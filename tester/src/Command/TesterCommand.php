<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Product;

class TesterCommand extends Command
{
    protected static $defaultName = 'symbok:test';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $p = new Product();
        $p->setId(1);
        $p->setName('salut');
        print($p);
    }
}
