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

    // public $sizes = [];

    /**
     * @Vich\UploadableField(mapping="consortium_logo", fileNameProperty="filename", size="filesize", mimeType="type", dimensions="dimensions", originalName="originalName")
     */
    public $file;
}
