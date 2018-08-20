<?php

namespace App\Module\Finna\Controller;

use App\EntityTypeManager;
use App\Entity\Consortium;
use App\Module\Finna\Entity\FinnaAdditions;
use App\Util\FormData;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FinnaController extends Controller
{
    const FINNA_ENTITY_TYPE = 'finna_organisation';

    public function __construct(EntityTypeManager $types)
    {
        $this->entityTypeManager = $types;
    }

    /**
     * @Route("/consortium/{consortium}/add-finna", name="entity.consortium.add_finna", defaults={"entity_type": "consortium"})
     * @ParamConverter("consortium", class="App:Consortium")
     * @Template("entity/FinnaAdditions/edit.html.twig")
     */
    public function addFinnaAdditionsAction(Request $request, Consortium $consortium)
    {
        $entity = new FinnaAdditions;
        $entity->setConsortium($consortium);

        $form = $this->entityTypeManager->getForm(self::FINNA_ENTITY_TYPE, 'edit', new FormData([
            'consortium' => $consortium
        ]));
        
        $form->remove('exclusive');

        // $form->get('consortium')->setData($consortium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityTypeManager->getEntityManager();

            $entity = $em->getRepository(FinnaAdditions::class)->create($form->getData()->getValues());

            $em->persist($entity);
            $em->flush();

            $this->addFlash('form.success', 'Record was created');

            return $this->redirectToRoute('entity.finna_organisation.edit', [
                self::FINNA_ENTITY_TYPE => $entity->getId(),
            ]);
        }

        return [
            'form' => $form,
            'type_label' => $this->entityTypeManager->getTypeLabel(self::FINNA_ENTITY_TYPE),
            'entity_type' => 'consortium',
            'consortium' => $consortium
        ];
    }

    /**
     * Handles form submission when creating FinnaAdditions AND Consortium entities at the same time.
     * This is because the Consortium has to be persisted first in order to have access to its ID for
     * the FinnaAdditions entity to use.
     *
     * @Route("/finna_organisation/add")
     * @Method("POST")
     */
    public function createFinnaOrganisation(Request $request)
    {
        $form = $this->entityTypeManager->getForm(self::FINNA_ENTITY_TYPE);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $values = $form->getData()->getValues();
            $finna_data = $this->entityTypeManager->getRepository(self::FINNA_ENTITY_TYPE)->create($values);
            $consortium = $finna_data->getConsortium();

            $consortium->setFinnaData(null);

            $em = $this->entityTypeManager->getEntityManager();
            $em->persist($consortium);
            $em->flush($consortium);

            // Treat all consortiums created through finna_organisation proxy as exclusives.
            $finna_data->setExclusive(true);

            $finna_data->setConsortium($consortium);

            $em->persist($finna_data);
            $em->flush();

            $this->addFlash('form.success', 'Record was created.');

            return $this->redirectToRoute('entity.finna_organisation.edit', [
                self::FINNA_ENTITY_TYPE => $finna_data->getId(),
            ]);
        } else {
            return $this->redirectToRoute('entity.finna_organisation.add');
        }
    }


    /**
     * Handles form submission when creating FinnaAdditions AND Consortium entities at the same time.
     * This is because the Consortium has to be persisted first in order to have access to its ID for
     * the FinnaAdditions entity to use.
     *
     * @Route("/finna_organisation/{finna_organisation}/delete")
     * @Method("POST")
     */
    public function deleteFinnaOrganisation(Request $request, FinnaAdditions $finna_organisation)
    {
        try {
            $em = $this->getDoctrine()->getManager();

            if ($finna_organisation->isExclusive()) {
                $em->remove($finna_organisation->getConsortium());
            }

            $em->flush();
            $this->addFlash('success', 'Record was deleted.');

            return $this->redirectToRoute('entity.finna_organisation.collection');
        } catch (ForeignKeyConstraintViolationException $exception) {
            $this->addFlash('form.danger', 'Cannot delete record as it has dependencies.');

            return $this->redirectToRoute("entity.finna_organisation.edit", [
                'finna_organisation' => $finna_organisation->getId(),
            ]);
        }
    }
}
