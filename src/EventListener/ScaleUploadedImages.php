<?php

namespace App\EventListener;

use DomainException;
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use App\Entity\Picture;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

/**
 * Handles scaling uploaded images to given sizes.
 */
class ScaleUploadedImages implements EventSubscriberInterface
{
    private $imagine;
    private $sizes;

    public static function getSubscribedEvents()
    {
        return [
            Events::POST_UPLOAD => 'postUpload'
        ];
    }

    public function __construct(ImagineInterface $imagine, array $size_mappings)
    {
        $this->imagine = $imagine;
        $this->sizes = $size_mappings;
    }

    public function postUpload(Event $event) : void
    {
        $object = $event->getObject();

        if ($object instanceof Picture) {
            $class = get_class($object);
            $sizes = $class::$defaultSizes;
            $basedir = $object->getFile()->getPath();
            $filename = $object->getFile()->getFilename();
            $realpath = realpath(sprintf('%s/%s', $basedir, $filename));
            $image = $this->imagine->open($realpath);

            foreach (array_reverse($sizes) as $size) {
                if (!isset($this->sizes[$size])) {
                    throw new DomainException(sprintf('Invalid size \'%s\' passed.', $size));
                }

                list($width, $height) = explode('x', $this->sizes[$size]);
                $path = sprintf('%s/%s/%s', $basedir, $size, $filename);

                $resize = $this->scaleSizeForImage($image, new Box($width, $height));
                $image->resize($resize);
                $image->save($path);
            }

            $object->setSizes($sizes);
        }
    }

    private function scaleSizeForImage(ImageInterface $image, BoxInterface $max)
    {
        $orig = $image->getSize();
        $r0 = $orig->getWidth() / $orig->getHeight();
        $r1 = $max->getWidth() / $max->getHeight();

        if ($r0 > $r1) {
            $height = $orig->getHeight() * ($max->getWidth() / $orig->getWidth());
            $new_size = new Box($max->getWidth(), $height);
        } else {
            $width = $orig->getWidth() * ($max->getHeight() / $orig->getHeight());
            $new_size = new Box($width, $max->getHeight());
        }

        return $new_size;
    }
}
