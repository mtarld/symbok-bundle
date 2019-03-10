<?php

namespace Mtarld\SymbokBundle\Factory\Symbok;

use Mtarld\SymbokBundle\Helper\NodesFinder;
use Mtarld\SymbokBundle\Helper\PropertyMethodNameBuilder;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyMethods;
use Mtarld\SymbokBundle\Model\Symbok\SymbokPropertyTypes;
use PhpParser\Node\Stmt\Class_ as NodeClass;

class SymbokPropertyMethodsFactory
{
    public function create(NodeClass $class, $property, SymbokPropertyTypes $types): SymbokPropertyMethods
    {
        $getter = false;
        $setter = false;
        $adder = false;
        $remover = false;

        $propertyName = $property->name->name;

        $classMethods = NodesFinder::findMethods(...$class->stmts);
        foreach ($classMethods as $method) {
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

        return new SymbokPropertyMethods($getter, $setter, $adder, $remover);
    }
}
