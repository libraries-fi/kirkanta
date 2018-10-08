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

/**
 * Glue to use VichUploader with non-Doctrine objects.
 */
class NestedImageType extends BaseType
{
    private $storage;
    private $metaData;

    public function __construct(StorageInterface $storage, MetadataReader $reader, PropertyMappingFactory $factory)
    {
        $this->storage = $storage;
        $this->metaData = $reader;
        $this->factory = $factory;
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
                // 'allow_delete' => true,
                // 'download_uri' => 'upload_'
            ])
            ;

        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use($field_config) {
            $entity = $event->getData();
            $mapping = $this->factory->fromObject($entity, null, $field_config['mapping'])[0];

            if ($entity->file) {
                $this->storage->upload($entity, $mapping);

                /*
                 * Notifies Doctrine that the parent entity has changed and has to be written back
                 * to the database.
                 */
                $event->setData(clone $entity);
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
