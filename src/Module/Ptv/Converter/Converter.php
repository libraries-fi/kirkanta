<?php

namespace App\Module\Ptv\Converter;

interface Converter
{
    public function supports($entity) : bool;
    public function convert($entity) : array;
}
