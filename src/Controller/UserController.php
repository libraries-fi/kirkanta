<?php

namespace App\Controller;

use App\EntityTypeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends Controller
{
    const USER_ENTITY_TYPE = 'user';

    /**
     * @Route("/user/add")
     * @Method("POST")
     */
    public function createAccountAction(Request $request, EntityTypeManager $types)
    {
        /*
         * NOTE: Basically we can delete this code and just call EntityController::addAction.
         * BUT we need to send an email to the user for account activation. And possibly create a log entry regarding the new account.
         */

        $form = $types->getForm(self::USER_ENTITY_TYPE);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $types->getRepository(self::USER_ENTITY_TYPE)
                ->create($form->getData()->getValues());

            $em = $types->getEntityManager();
            $em->persist($user);
            $em->flush($user);

            $this->addFlash('success', 'User created.');

            return $this->redirectToRoute('entity.user.edit', [
                'user' => $user->getId(),
            ]);
        } else {
            return $this->redirectToRoute('entity.user.add');
        }
    }
}
