<?php

namespace App\Component\Twig;

use App\Component\Element\Table;
use Twig_Extension as Extension;
use Twig_SimpleFunction as SimpleFunction;
use Twig_Environment;

class TableExtension extends Extension
{
    private $renderer;
    private $template = 'component/table.html.twig';

    public function __construct(Twig_Environment $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getFunctions() : array
    {
        return [
            new SimpleFunction('table_render', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    public function render($table, array $options = []) : string
    {
        $options += [
            'striped' => false,
            'hovered' => false,
            'drag' => false,
        ];

        $attr = $options['attr'] ?? [];
        unset($options['attr']);

        if (is_array($table)) {
            $table = Table::createFromArray($table);
        }

        return $this->renderer->render($this->template, ['table' => $table, 'options' => $options, 'attr' => $attr]);
    }
}
