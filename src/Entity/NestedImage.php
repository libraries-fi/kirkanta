<?php

namespace App\Entity;

use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * POPO class containing information about a logo attached to a Consortium.
 *
 * @Vich\Uploadable
 */
class NestedImage
{
    public $filename;
    public $filesize;
    public $dimensions;
    public $originalName;
    public $type;
    public $sizes = [];

    const DEFAULT_SIZES = ['small', 'medium', 'large', 'huge'];

    public function __construct()
    {
        // Defined in parent class.
        $this->sizes = static::DEFAULT_SIZES;
    }
}
