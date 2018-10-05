<?php

namespace App\Module\UserManagement\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Module\UserManagement\ChangePassword;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AccountController extends Controller
{
    /**
     * @Route("/profile", name="account.home")
     * @Template("user/profile.html.twig")
     */
    public function userProfileAction(UserInterface $user)
    {
        return [
            'user' => $user
        ];
    }

    /**
     * @Route("/profile/change-password", name="account.change_password")
     * @IsGranted("CHANGE_PASSWORD")
     * @Template("account/change-password.html.twig")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $passwords, UserInterface $user)
    {
        $token = new ChangePassword($user->getPassword());

        $form = $this->createFormBuilder($token)
            ->add('old_password', PasswordType::class)
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'New password'
                ],
                'second_options' => [
                    'label' => 'Verify password',
                ]
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // var_dump($token->getNewPassword());
            $password = $passwords->encodePassword($user, $token->getNewPassword());
            $user->setPassword($password);
            $this->getDoctrine()->getManager()->flush($user);

            $this->addFlash('success', 'Your password was changed.');

            return $this->redirectToRoute('account.home');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/profile/change-email", name="account.change_email")
     * @IsGranted("CHANGE_EMAIL")
     * @Template("account/change-email.html.twig")
     */
    public function changeEmail(Request $request, UserInterface $user) {
        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class, [
                'help' => 'Give a valid email address.'
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush($user);
            $this->addFlash('success', 'Your email address was changed.');
            return $this->redirectToRoute('account.home');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
