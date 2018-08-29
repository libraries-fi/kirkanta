<?php

namespace KirjastotFi\KirkantaApiBundle\Serializer\Normalizer;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use App\Entity\Address;
use App\Entity\City;
use App\Entity\Facility;
use App\Entity\Library;
use App\Entity\Period;
use App\Module\Finna\Entity\FinnaAdditions;
use KirjastotFi\KirkantaApiBundle\Serializer\NameConverter\SnakesToCamelsConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class EntityNormalizer extends ObjectNormalizer
{
    private $inner;
    private $overrides = [];

    public static function create(ClassMetadataFactoryInterface $metadata, PropertyAccessorInterface $accessor)
    {
        $normalizer = new static($metadata, new SnakesToCamelsConverter, $accessor, null);

        $normalizer->setOverride(Address::class, 'city', [$normalizer, 'cityName']);
        $normalizer->setOverride(Address::class, 'coordinates', [__CLASS__, 'splitCoordinates']);

        $normalizer->setOverride(City::class, 'consortium', [__CLASS__, 'entityId']);
        $normalizer->setOverride(City::class, 'region', [__CLASS__, 'entityId']);

        $normalizer->setOverride(Facility::class, 'consortium', [__CLASS__, 'entityId']);
        $normalizer->setOverride(Facility::class, 'city', [__CLASS__, 'entityId']);

        $normalizer->setOverride(FinnaAdditions::class, 'service_point', [__CLASS__, 'entityId']);
        
        $normalizer->setOverride(Library::class, 'parent', [__CLASS__, 'entityId']);

        $normalizer->setOverride(Period::class, 'valid_from', [$normalizer, 'formatDate']);
        $normalizer->setOverride(Period::class, 'valid_until', [$normalizer, 'formatDate']);

        $normalizer->setOverride(Translatable::class, 'translations', [$normalizer, 'extractTranslations']);

        return $normalizer;
    }

    public function extractTranslations(iterable $translations, array $context) : array
    {
        $processed = [];
        $langcodes = [];
        $format = $context['format'];

        if (empty($context['langcode'])) {
            foreach ($translations as $langcode => $entity) {
                $langcodes[$langcode] = true;
                $data = $this->normalize($entity, $context['format'], $context);
                foreach ($data as $key => $value) {
                    if ($format == 'xml') {
                        $processed[$key]['@type'] = 'tr';
                        $processed[$key]['value'][] = [
                            '@lang' => $langcode,
                            '#' => $value,
                        ];
                    } else {
                        $processed[$key][$langcode] = $value;
                    }
                }
            }
        } else {
            $langcode = $context['langcode'];
            $fallback = $context['entity']->getDefaultLanguage();
            $entity = $translations[$langcode] ?? $translations[$fallback];
            $data = $this->normalize($entity, $context['format'], $context);

            foreach ($data as $key => $value) {
                if ($format == 'xml') {
                    $processed[$key] = [
                        '@type' => 'tr',
                        '@lang' => $entity->getLangcode(),
                        '#' => $value,
                    ];
                } else {
                    $processed[$key] = $value;
                }
            }
        }

        /*
         * Intent is good but this will also clutter nested entities and cause confusion when
         * some processed nested values like library.city lack this property altogether.
         */
        // $processed['available_languages'] = array_keys($langcodes);

        return $processed;
    }

    public static function entityId($entity) : ?int
    {
        if ($entity) {
            // NOTE: Calling isPublished() will trigger additional entity loads if necessary.
            if (!($entity instanceof StateAwareness) || $entity->isPublished()) {
                return $entity->getId();
            }
        }
        return null;
    }

    public function cityName(?City $city, array $context)
    {
        if (!$city) {
            return null;
        }

        if (empty($context['langcode'])) {
            $values = [];

            foreach ($city->getTranslations() as $lang => $data) {
                if ($context['format'] == 'xml') {
                    $values['value'] = ['#' => $data->getName(), '@lang' => $lang];
                } else {
                    $values[$lang] = $data->getName();
                }
            }
            return $values;
        } else {
            $data = $city->getTranslations()->get($context['langcode']);
            return $data ? $data->getName() : null;
        }
    }

    public function splitCoordinates(?string $coordinates) : ?array
    {
        if ($coordinates) {
            list($lat, $lon) = explode(',', $coordinates);
            return [
                'lat' => (float)trim($lat),
                'lon' => (float)trim($lon),
            ];
        }
        return null;
    }

    public function formatDate(?DateTimeInterface $date) : ?string
    {
        if ($date) {
            return $date->format('Y-m-d');
        } else {
            return null;
        }
    }

    public function setOverrides(string $class_name, array $overrides) : void
    {
        foreach ($overrides as $property => $callable) {
            $this->setOverride($class_name, $property, $callable);
        }
    }

    public function setOverride(string $class_name, string $property, callable $callback) : void
    {
        $this->overrides[$class_name][$property] = $callback;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $values = parent::normalize($object, $format, $context);

        if ($object instanceof Translatable && isset($values['translations'])) {
            $translations = $values['translations'];
            $values = $values['translations'] + $values;
            unset($values['translations']);
        }

        return $values;
    }

    public function getAttributeValue($object, $property, $format = null, array $context = [])
    {
        $value = parent::getAttributevalue($object, $property, $format, $context);

        if ($callback = $this->getOverride($object, $property)) {
            $context['entity'] = $object;
            $context['format'] = $format;
            $value = $callback($value, $context);
        }

        return $value;
    }

    public function getOverride($object, $property) : ?callable
    {
        foreach ($this->overrides as $class => $overrides) {
            if ($object instanceof $class && isset($overrides[$property])) {
                return $overrides[$property];
            }
        }
        return null;
    }
}
