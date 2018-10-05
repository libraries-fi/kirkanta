<?php

namespace UserAccountsBundle\Controller;

use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use UserAccountsBundle\Form\UserLogin;
use UserAccountsBundle\Form\UserRegistration;

class AccountController extends Controller
{
    /**
     * @Route("/register", name="user.register")
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(UserRegistration::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity_class = $this->userEntityClass();
            $user = new $entity_class;
            $password = $this->hashPassword($user, $form->get('pass')->getData());

            $user->setEmail($form->get('email')->getData());
            $user->setUsername($form->get('user')->getData());
            $user->setPassword($password);

            $this->entityManager()->persist($user);
            $this->entityManager()->flush();

            $this->addFlash('notice', 'User account created');
            return $this->redirectToRoute('front');
        }

        $template = $this->getParameter('user_accounts.template.register');

        return $this->render($template, [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/login", name="user.login")
     */
    public function loginAction()
    {
        $auth = $this->get('security.authentication_utils');
        $error = $auth->getLastAuthenticationError();
        $username = $auth->getLastUsername();
        $template = $this->getParameter('user_accounts.template.login');

        return $this->render($template, [
            'error' => $error,
            'last_username' => $username,
        ]);
    }

    /**
     * @Route("/logout", name="user.logout")
     */
    public function logoutAction()
    {

    }

    protected function entityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    protected function hashPassword(UserInterface $user, $password)
    {
        $hash = $this->get('security.password_encoder')->encodePassword($user, $password);
        return $hash;
    }

    protected function userEntityClass()
    {
        return $this->container->getParameter('user_accounts.user_entity');
    }
}
