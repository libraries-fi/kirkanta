<?php

namespace App\Entity;

use App\Entity\Feature\CreatedAwareness;
use App\Entity\Feature\Weight;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @ORM\Table(name="pictures")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="attached_to", type="string")
 * @ORM\DiscriminatorMap({
 *      "organisation" = "LibraryPhoto",
 *      "service" = "ServicePhoto",
 *      "consortium_logo" = "ConsortiumLogo"
 * })
 */
abstract class Picture extends EntityBase implements CreatedAwareness, Weight
{
    use Feature\CreatedAwarenessTrait;
    use Feature\WeightTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $filename;

    /**
     * @ORM\Column(type="string")
     */
    private $original_name;

    /**
     * @ORM\Column(type="string")
     */
    private $mime_type;

    /**
     * @ORM\Column(type="integer")
     */
    private $filesize;

    /**
     * Value pair consisting of original image file dimensions.
     *
     * FIXME: Type int_array does not like NULLs.
     *
     * @ORM\Column(type="int_array")
     */
    private $dimensions = [];

    /**
     * Array of size names (small, medium, etc.)
     *
     * @ORM\Column(type="text_array")
     */
    private $sizes = [];

    /**
     * @ORM\Column(type="json_array")
     */
    private $meta;

    // Annotate in subclasses with @Vich\UploadableField
    // protected $file;

    // Every subclass should define this.
    // static public $default_sizes = [];

    public function __construct()
    {
        $this->sizes = static::DEFAULT_SIZES;
        $this->created = new \DateTime;
    }

    public function getFilename() : ?string
    {
        return $this->filename;
    }

    /**
     * NOTE: $filename has to be nullable because VichUploader sets a NULL filename when entity's
     * being deleted.
     */
    public function setFilename(?string $filename) : void
    {
        $this->filename = $filename;
    }

    public function getOriginalName() : string
    {
        return $this->original_name;
    }

    /**
     * NOTE: $filename has to be nullable because VichUploader sets a NULL filename when entity's
     * being deleted.
     */
    public function setOriginalName(?string $name) : void
    {
        $this->original_name = $name;
    }

    public function getPixelCount() : int
    {
        return array_product($this->dimensions ?? []);
    }

    public function getDimensions() : array
    {
        return $this->dimensions;
    }

    public function getMimeType() : string
    {
        return $this->mime_type;
    }

    /**
     * NOTE: $filename has to be nullable because VichUploader sets a NULL filename when entity's
     * being deleted.
     */
    public function setMimeType(?string $type) : void
    {
        $this->mime_type = $type;
    }

    public function getFilesize() : int
    {
        return $this->filesize;
    }

    /**
     * NOTE: $filename has to be nullable because VichUploader sets a NULL filename when entity's
     * being deleted.
     */
    public function setFilesize(?int $bytes) : void
    {
        $this->filesize = $bytes;
    }

    /**
     * NOTE: $filename has to be nullable because VichUploader sets a NULL filename when entity's
     * being deleted.
     */
    public function setDimensions(?array $dimensions) : void
    {
        $this->dimensions = $dimensions;
    }

    public function getSizes() : array
    {
        return $this->sizes;
    }

    public function setSizes(array $sizes) : void
    {
        $this->sizes = $sizes;
    }

    public function getFile() : ?File
    {
        return $this->file;
    }

    public function setFile(File $file) : void
    {
        $this->file = $file;
    }

    public function getMeta() : ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta) : void
    {
        $this->meta = $meta;
    }

    public function addMeta(array $values) : void
    {
        $this->meta = $values + ($this->meta ?? []);
    }
}
