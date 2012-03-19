<?php

namespace Employness\Console;

use Employness\Application as App;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    protected $app;

    public function __construct(App $app)
    {
        parent::__construct();

        $this->app = $app;

        foreach (new \DirectoryIterator(__DIR__.'/Command') as $file) {
            $class = 'Employness\\Console\\Command\\'.$file->getBasename('.php');
            if (class_exists($class)) {
                $refl = new \ReflectionClass($class);
                if (!$refl->isAbstract()) {
                    $this->add($refl->newInstance());
                }
            }
        }
    }

    public function getApp()
    {
        return $this->app;
    }
}