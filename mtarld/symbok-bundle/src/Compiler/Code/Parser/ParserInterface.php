<?php

namespace Mtarld\SymbokBundle\Compiler\Code\Parser;

use phpDocumentor\Reflection\Types\Context;

interface ParserInterface
{
    public function parse($subject, Context $context);
}
