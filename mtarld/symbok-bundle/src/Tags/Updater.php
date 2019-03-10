<?php

namespace Mtarld\SymbokBundle\Tags;

use Mtarld\SymbokBundle\Compiler\Helper\ContextBuilder;
use Mtarld\SymbokBundle\Compiler\Helper\NodeFinder;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as NodeClass;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class Updater
{
    /** @var Parser */
    private $phpParser;

    public function __construct()
    {
        $this->phpParser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
    }

    public function applyNodes(string $filename, Node ...$generatedNodes)
    {
        $fp = fopen($filename, 'rw+');
        flock($fp, LOCK_EX);

        $content = '';
        $line = 1;
        $lines = [];
        while (false == feof($fp)) {
            $content .= fgets($fp);
            $lines[$line++] = ftell($fp);
        }

        $nodes = $this->phpParser->parse($content);

        fseek($fp, 0);
        $content = '';

        $originalNamespace = NodeFinder::findNamespace(...$nodes);
        $originalUses = NodeFinder::findUses(...$originalNamespace->stmts);
        $originalClass = NodeFinder::findClass(...$originalNamespace->stmts);

        $generatedNamespace = NodeFinder::findNamespace(...$generatedNodes);
        $generatedClass = NodeFinder::findClass(...$generatedNamespace->stmts);

        $context = ContextBuilder::build((string)$originalNamespace->name, $originalUses);

        $comment = $this->createComment($originalClass, $generatedClass, $context);

        /** @var Doc $doc */
        if ($doc = $originalClass->getDocComment()) {
            $content .= fread($fp, $doc->getFilePos());
            $content .= $comment;
            fseek($fp, strlen($doc->getText()), SEEK_CUR);
        } else {
            $line = $originalClass->getLine();
            $content .= fread($fp, $lines[$line - 1]);
            $content .= $comment . PHP_EOL;
        }

        // Read tailed content
        $content .= fread($fp, filesize($filename) - ftell($fp));

        // Rewind, truncate and write new content
        fseek($fp, 0);
        ftruncate($fp, 0);
        fwrite($fp, $content);

        // Release and close
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    private function createComment(NodeClass $originalClass, NodeClass $generatedClass, Context $context): string
    {
        $summary = "Class {$originalClass->name->name}";
        $description = null;
        $tags = [];

        if ($docComment = $originalClass->getDocComment()) {
            $docBlock = DocBlockFactory::createInstance()->create((string)$docComment, $context);
            $summary = $docBlock->getSummary() ?? $summary;
            $description = $docBlock->getDescription();
            $tags = $docBlock->getTags();
        }

        foreach ($tags as $index => $tag) {
            if ($tag instanceof Method) {
                unset($tags[$index]);
            }
        }

        $originalMethods = array_map(function (ClassMethod $method) {
            return $method->name->name;
        }, $originalClass->getMethods());

        foreach ($generatedClass->getMethods() as $method) {
            if (in_array($method->name->name, $originalMethods)) {
                continue;
            }
            $tags[] = $this->createMethodTag($method, $context);
        }

        return self::removeSpaceFromClassTags(
            str_replace(
                "/**\n * \n *\n",
                "/**\n",
                (new DocBlock\Serializer())->getDocComment(new DocBlock($summary, $description, $tags, $context))
            )
        );
    }

    private function createMethodTag(ClassMethod $method, Context $context): Method
    {
        $docBlock = DocBlockFactory::createInstance()->create((string)$method->getDocComment(), $context);
        $arguments = array_map(function (Param $param, int $key) use ($method) {
            $compound = null;
            $default = $method->getParams()[$key]->jsonSerialize()['default'];
            if (!empty($default)
                && !($param->getType() instanceof \phpDocumentor\Reflection\Types\Compound)
                && $default->jsonSerialize()['name']->toString() === 'null'
            ) {
                $compound = new \phpDocumentor\Reflection\Types\Compound([
                    $param->getType(),
                    new \phpDocumentor\Reflection\Types\Null_()
                ]);
            }

            return [
                'name' => $param->getVariableName(),
                'type' => $compound ?? $param->getType(),
            ];
        }, $docBlock->getTagsByName('param'), array_keys($docBlock->getTagsByName('param')));

        if ($docBlock->hasTag('return')) {
            /** @var Return_ $returnTag */
            $returnTag = $docBlock->getTagsByName('return')[0];
            $returnType = $returnTag->getType();
        } else {
            $returnType = new Void_();
        }

        return new Method($method->name->name, $arguments, $returnType, $method->isStatic());
    }

    public static function removeSpaceFromClassTags(string $classDoc): string
    {
        return preg_replace('/(@\S*)(\s*)(\(.*\)|\()/m', '$1$3', $classDoc);
    }
}
