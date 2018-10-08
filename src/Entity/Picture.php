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
 * })
 * @Vich\Uploadable
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
     * @ORM\Column(type="text_array")
     */
    private $sizes = [];

    /**
     * @ORM\Column(type="json_array")
     */
    private $meta;

    /**
     * @Vich\UploadableField(mapping="image", fileNameProperty="filename")
     */
    private $file;

    // Every subclass should define this.
    // static public $default_sizes = [];

    public function getFilename() : ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename) : void
    {
        $this->filename = $filename;
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
