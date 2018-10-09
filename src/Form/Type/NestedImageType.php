<?php

namespace App\Form\Type;

use App\Entity\ConsortiumLogo;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

use App\Events as AppEvents;
use App\Event\ImageUploadEvent;
use App\EventListener\ImageResizeSubscriber;

/**
 * Glue to use VichUploader with non-Doctrine objects.
 */
class NestedImageType extends BaseType
{
    private $storage;
    private $metaData;
    private $mappingFactory;
    private $uploadResizer;

    public function __construct(StorageInterface $storage, MetadataReader $reader, PropertyMappingFactory $factory, ImageResizeSubscriber $upload_resizer)
    {
        $this->storage = $storage;
        $this->metaData = $reader;
        $this->mappingFactory = $factory;
        $this->uploadResizer = $upload_resizer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        if (!$options['file_field']) {
            throw new RuntimeException("Have to define option 'file_field'");
        }

        $field = $options['file_field'];
        $field_config = $this->metaData->getUploadableFields($options['data_class'])[$field] ?? null;

        if (!$field_config) {
            throw new \InvalidArgumentError("Field '{$field}' does not accept uploads");
        }

        $builder
            ->add($field_config['fileNameProperty'], null, [
                'label' => 'Filename',
                'required' => false,
            ])
            ->add($field_config['propertyName'], FileType::class, [
                'required' => false,
            ])
            ;

        $builder->addEventSubscriber($this->uploadResizer);

        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use($field_config, $builder) {
            $entity = $event->getData();
            $mapping = $this->mappingFactory->fromObject($entity, null, $field_config['mapping'])[0];
            $field = $field_config['propertyName'];

            if ($entity->{$field}) {
                $this->storage->upload($entity, $mapping);

                /*
                 * Notifies Doctrine that the parent entity has changed and has to be written back
                 * to the database.
                 */
                $event->setData(clone $entity);

                $builder->getEventDispatcher()->dispatch(AppEvents::IMAGE_UPLOAD, new ImageUploadEvent($entity, $mapping));
            }
        });
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'file_field' => 'file',
        ]);
    }
}
