<?php

namespace App\EventListener;

use App\Events;
use App\Event\ImageUploadEvent;
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use App\Entity\Picture;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Vich\UploaderBundle\Event\Event as VichEvent;
use Vich\UploaderBundle\Event\Events as VichEvents;

/**
 * Handles scaling uploaded images to given sizes.
 */
class ImageResizeSubscriber implements EventSubscriberInterface
{
    private $imagine;
    private $sizes;

    public static function getSubscribedEvents()
    {
        return [
            VichEvents::POST_UPLOAD => 'postUpload',
            Events::IMAGE_UPLOAD => 'onImageUpload',
        ];
    }

    public function __construct(ImagineInterface $imagine, array $size_mappings)
    {
        $this->imagine = $imagine;
        $this->sizes = $size_mappings;
    }

    public function postUpload(VichEvent $event) : void
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
                    throw new \DomainException(sprintf('Invalid size \'%s\' passed.', $size));
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

    public function onImageUpload(ImageUploadEvent $event) : void
    {
        $image = $event->getImage();
        $file = $image->file;
        $image_data = $this->imagine->open(implode(DIRECTORY_SEPARATOR, [$event->getMapping()->getUploadDestination(), $image->filename]));

        foreach (array_reverse($image->sizes) as $size) {
            if (!isset($this->sizes[$size])) {
                throw new \DomainException(sprintf('Invalid size \'%s\' passed.', $size));
            }

            list($width, $height) = explode('x', $this->sizes[$size]);
            $filepath = implode(DIRECTORY_SEPARATOR, [$event->getMapping()->getUploadDestination(), $size, $image->filename]);

            $size_data = $this->scaleSizeForImage($image_data, new Box($width, $height));
            $image_data->resize($size_data);
            $image_data->save($filepath);
        }
    }

    private function scaleSizeForImage(ImageInterface $image, BoxInterface $max)
    {
        $orig = $image->getSize();
        $r0 = $orig->getWidth() / $orig->getHeight();
        $r1 = $max->getWidth() / $max->getHeight();

        if ($orig->getWidth() < $max->getWidth() && $orig->getHeight() < $max->getHeight()) {
            return new Box($orig->getWidth(), $orig->getHeight());
        }

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
