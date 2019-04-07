<?php

namespace App\Module\Ptv\Form\Extension;

use App\EntityTypeManager;
use App\Entity\Library;
use App\Entity\Feature\GroupOwnership;
use App\Form\LibraryForm;
use App\Module\Ptv\Entity\PtvData;
use App\Module\Ptv\Form\PtvDataType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;

/**
 * Handles processing of PTV configuration for Library entities.
 */
class LibraryFormExtension extends AbstractTypeExtension
{
    const REQUIRED_ROLE = 'ROLE_PTV';

    private $types;
    private $auth;

    public function __construct(EntityTypeManager $types, Security $auth) {
        $this->types = $types;
        $this->auth = $auth;
    }

    public function getExtendedType() : string
    {
        return LibraryForm::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) use($builder) {
            $library = $event->getData();
            $form = $event->getForm();

            if (($library instanceof Library) && $this->isPtvAllowed($library)) {
                $data = $this->types->getRepository('ptv_data')->findOneBy([
                    'entity_type' => 'library',
                    'entity_id' => $library->getId(),
                ]);

                if (!$data) {
                    $data = new PtvData('library', $library->getId());
                }

                $form->add('ptv', PtvDataType::class, [
                    'required' => false,
                    'mapped' => false,
                    'label' => 'PTV Info',
                    'data' => $data,
                ]);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $form = $event->getForm();

            if ($form->isValid() && $form->has('ptv')) {
                $data = $event->getForm()->get('ptv')->getData();

                if ($data->isEnabled() || $data->getPtvIdentifier()) {
                    $this->types->getEntityManager()->persist($data);
                }
            }
        });
    }

    protected function isPtvAllowed(GroupOwnership $entity)
    {
        $roles = $entity->isNew()
            ? $this->auth->getUser()->getGroup()->getRoles()
            : $entity->getOwner()->getRoles()
            ;
        return in_array(self::REQUIRED_ROLE, $roles, true);
    }
}
