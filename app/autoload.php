<?php

require_once __DIR__.'/../vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'           => __DIR__.'/../vendor',
    'Silex'             => __DIR__.'/../vendor/Silex/src', 
    'Employness'        => __DIR__.'/../src',
));
$loader->registerPrefixes(array(
    'Pimple'            => __DIR__.'/../vendor/Pimple/lib', 
    'Twig_Extensions_'  => __DIR__.'/../vendor/Twig-extensions/lib',
    'Twig_'             => __DIR__.'/../vendor/Twig/lib',
));
$loader->register();