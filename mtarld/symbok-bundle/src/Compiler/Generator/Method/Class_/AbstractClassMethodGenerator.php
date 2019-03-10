<?php

namespace Mtarld\SymbokBundle\Compiler\Generator\Method\Class_;

use Mtarld\SymbokBundle\Compiler\Code\Parsed\ParsedClass;
use Mtarld\SymbokBundle\Compiler\Generator\Method\AbstractMethodGenerator;
use Mtarld\SymbokBundle\Compiler\Rules\Impl\ClassRules;
use phpDocumentor\Reflection\Types\Context;

abstract class AbstractClassMethodGenerator extends AbstractMethodGenerator
{
    /** @var ParsedClass */
    protected $class;

    /** @var ClassRules */
    protected $classRules;

    public function __construct(
        ParsedClass $class,
        ClassRules $classRules,
        Context $context
    ) {
        $this->class = $class;
        $this->classRules = $classRules;

        parent::__construct($context);
    }
}
