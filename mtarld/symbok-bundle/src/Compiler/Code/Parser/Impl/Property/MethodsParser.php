<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parser\Impl\Property;

use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyMethods;
use Mtarld\SymbokBundle\Compiler\Code\Parsed\Property\PropertyTypes;
use Mtarld\SymbokBundle\Compiler\Helper\NodeFinder;
use Mtarld\SymbokBundle\Compiler\Helper\PropertyMethodNameBuilder;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class MethodsParser
{
    /** @var array */
    private $classMethods;

    public function __construct(NodeClass $class)
    {
        $this->classMethods = NodeFinder::findMethods(...$class->stmts);
    }

    public function parse($property, PropertyTypes $types): PropertyMethods
    {
        $getter = false;
        $setter = false;
        $adder = false;
        $remover = false;

        $propertyName = $property->name->name;
        foreach ($this->classMethods as $method) {
            $methodName = $method->name->name;
            if ($methodName == PropertyMethodNameBuilder::buildGetterMethodName($propertyName, $types->getBaseType())) {
                $getter = true;
            }
            if ($methodName == PropertyMethodNameBuilder::buildSetterMethodName($propertyName)) {
                $setter = true;
            }
            if ($methodName == PropertyMethodNameBuilder::buildAdderMethodName($propertyName)) {
                $adder = true;
            }
            if ($methodName == PropertyMethodNameBuilder::buildRemoverMethodName($propertyName)) {
                $remover = true;
            }
        }

        return new PropertyMethods($getter, $setter, $adder, $remover);
    }
}
