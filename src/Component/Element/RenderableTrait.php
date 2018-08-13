<?php

namespace App\Component\Element;

/**
 * Provides ability to store key-value pairs during rendering for helper functions etc.
 */
trait RenderableTrait
{
    private $renderContext = [];

    public function setRenderContextValue($key, $value) : void
    {
        $this->renderContext[$key] = $value;
    }

    public function getRenderContextValue($key)
    {
        return $this->renderContext[$key] ?? null;
    }
}
