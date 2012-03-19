<?php

namespace Employness\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    public function getApp()
    {
        return $this->getApplication()->getApp();
    }
}