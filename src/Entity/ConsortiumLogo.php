<?php

namespace App\Entity;

use JsonSerializable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * POPO class containing information about a logo attached to a Consortium.
 *
 * @Vich\Uploadable
 */
class ConsortiumLogo extends NestedImage
{
    const DEFAULT_SIZES = ['small', 'medium'];
    
    /**
     * @Vich\UploadableField(mapping="consortium_logo", fileNameProperty="filename", size="filesize", mimeType="type", dimensions="dimensions", originalName="originalName")
     */
    public $file;
}
