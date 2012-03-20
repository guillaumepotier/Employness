<?php

namespace Employness\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('create admin user')
            ->setHelp('You can create a new user with: <info>user:create -e user@mail.com -p 1234</info> or an admin with <info>user:create -e user@mail.com -p 1234 -admin</info>')
            ->addOption('email', 'e', InputOption::VALUE_REQUIRED, 'user email')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'user password')
            ->addOption('admin', 'a', InputOption::VALUE_OPTIONAL, 'isAdmin')
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

        $requireOption('password');
        $requireOption('email');

        if ($input->getOption('admin')) {
            $admin_condition = 1;
        } else {
            $admin_condition = 0;
        }

        $app = $this->getApp();
        $app['user.repository']->insert(array(
            'email'     => $input->getOption('email'),
            'password'  => sha1($input->getOption('password')),
            'token'     => md5(uniqid('_tok')),
            'admin'     => $admin_condition,
        ));
        return;
    }
}