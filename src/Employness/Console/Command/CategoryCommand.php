<?php

namespace Employness\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('category:create')
            ->setDescription('create category')
            ->setHelp('You can create a new category with: <info>category:create -n name</info>')
            ->addOption('name', 'm', InputOption::VALUE_REQUIRED, 'category name')
            ->addOption('environment', '-env', InputOption::VALUE_OPTIONAL, 'environment')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requireOption = function($name) use($input) {
            if (null === $input->getOption($name)) {
                throw new \InvalidArgumentException(sprintf('You must provide the "%s" option', $name));
            }
        };

        $requireOption('name');

        $app = $this->getApp();
        $app['category.repository']->insert(array(
            'name'      => $input->getOption('name'),
        ));
        return;
    }
}