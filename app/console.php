#!/usr/bin/env php
<?php

$_SERVER['ENV'] = false;

if (false !== $pos = array_search('-e', $argv) || false !== $pos = array_search('--env', $argv)) {
    $_SERVER['ENV'] = $argv[$pos];
}

$app = require __DIR__.'/bootstrap.php';
$application = new Employness\Console\Application($app);
$application->run();