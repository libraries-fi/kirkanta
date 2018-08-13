<?php

namespace App\Component\Twig;

use App\Component\Element\Table;
use Twig_Extension as Extension;
use Twig_SimpleFunction as SimpleFunction;
use Twig_Environment;

class FontAwesomeExtension extends Extension
{
    private $renderer;
    private $template = 'kirkanta/component/table.html.twig';

    public function __construct(Twig_Environment $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getFunctions() : array
    {
        return [
            new SimpleFunction('icon', [$this, 'icon'], ['is_safe' => ['html']]),
        ];
    }

    public function icon(string $name, array $options = []) : string
    {
        return sprintf('<i class="fa fa-%s" aria-hidden="true"></i>', $name);
    }
}
