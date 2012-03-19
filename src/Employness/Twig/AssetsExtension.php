<?php

namespace Employness\Twig;

class AssetsExtension extends \Twig_Extension
{
    private $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getFunctions()
    {
        return array(
            'asset' => new \Twig_Function_Method($this, 'getAssetUrl'),
            'round' => new \Twig_Function_Method($this, 'getRound'),
        );
    }

    public function getAssetUrl($url)
    {
        return $this->basePath.$url;
    }

    public function getRound($val, $precision)
    {
        return round($val, $precision);
    }

    public function getName()
    {
        return 'assets';
    }
}