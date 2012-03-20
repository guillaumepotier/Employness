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
            'twig_round' => new \Twig_Function_Method($this, 'getRound'),
        );
    }

    public function getAssetUrl($url)
    {
        return $this->basePath.$url;
    }

    public function getRound($val1, $val2, $precision)
    {
        return is_numeric($val1) && is_numeric($val2) && $val2 != 0 ? round($val1/$val2, $precision) : 0;
    }

    public function getName()
    {
        return 'assets';
    }
}