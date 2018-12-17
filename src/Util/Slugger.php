<?php

namespace App\Util;

use InvalidArgumentException;
use App\Entity\Feature\Sluggable;
use Doctrine\Common\Persistence\ObjectRepository;

class Slugger
{
    private $storage;

    public static function slugify(string $name) : string
    {
        $name = mb_strtolower($name);
        $name = str_replace(['å', 'ä', 'ö'], ['a', 'a', 'o'], $name);
        $name = preg_replace('/[^a-z0-9]+/', '-', $name);
        $name = trim($name, '-');
        return $name;
    }

    public function __construct(ObjectRepository $storage)
    {
        $this->storage = $storage;
    }

    public function makeSlug($name, string $langcode, int $max_length = 40) : string
    {
        if (is_array($name)) {
            $method = [__CLASS__, 'slugify'];
            $name = implode('-', array_map($method, $name));
        } else {
            $name = self::slugify($name);
        }

        $slug = $name;
        $i = 2;

        while ($this->storage->findBy(['slug' => $slug, 'langcode' => $langcode])) {
            $slug = substr($name, 0, $max_length - strlen($i)) . "-{$i}";
            $i++;
        }

        return $slug;
    }
}
