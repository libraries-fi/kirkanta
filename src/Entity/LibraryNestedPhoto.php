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
class LibraryNestedPhoto extends NestedImage
{
    const DEFAULT_SIZES = ['small', 'medium', 'large', 'huge'];

    /**
     * @Vich\UploadableField(mapping="library_photo", fileNameProperty="filename", size="filesize", mimeType="type", dimensions="dimensions", originalName="originalName")
     */
    public $file;
}
