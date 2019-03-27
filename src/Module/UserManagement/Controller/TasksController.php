<?php

namespace App\Module\UserManagement\Controller;

use App\Module\UserManagement\Entity\OneTimeToken;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Html2Text\Html2Text;
use Swift_Mailer as Mailer;
use Swift_Message as Email;

use UserAccountsBundle\UserInterface;

class TasksController extends Controller
{
    private $entities;
    private $storage;
    private $mailer;
    private $auth;

    public function __construct(EntityManagerInterface $entities, Mailer $mailer, AuthorizationCheckerInterface $auth, TranslatorInterface $translator)
    {
        $this->entities = $entities;
        $this->storage = $entities->getRepository(OneTimeToken::class);
        $this->mailer = $mailer;
        $this->auth = $auth;
        $this->translator = $translator;
    }

    /**
     * @Route("/reset-password", name="user_management.request_reset_password")
     * @Template("user_management/request-password.html.twig")
     */
    public function requestResetPassword(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Email address',
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->getForm();

        $requestUser = null;

        if ($this->auth->isGranted('IS_AUTHENTICATED_FULLY')) {
            /**
             * Autofill email only when an admin requests password on behalf of
             * other users. Attempt at preventing leaking of email addresses.
             */
            if ($uid = $request->query->get('user')) {
                $requestUser = $this->entities->getRepository('App:User')->findOneById($uid);

                if ($requestUser) {
                    $form->setData(['email' => $requestUser->getEmail()]);
                }
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->entities->getRepository('App:User')->findOneBy([
                'email' => $form->get('email')->getData(),
                'municipal_account' => false,
            ]);

            if ($user) {
                $token = new OneTimeToken('reset_password');
                $token->setUser($user);
                $this->sendEmail($user, $token->getNonce());

                $this->entities->persist($token);
                $this->entities->flush();

                if ($requestUser) {
                    $this->addFlash('success', 'Recovery link was sent to the user.');
                    return $this->redirectToRoute('entity.user.collection');
                } else {
                    $this->addFlash('success', 'If there was an account with this email address, you will be emailed with a recovery link.');
                    return $this->redirectToRoute('user_management.request_reset_password');
                }
            } else {
                $this->addFlash('danger', 'Given email address does not match any user.');
                $this->addFlash('danger', 'This tool cannot be used with municipal accounts.');
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/reset-password/{nonce}", name="user_management.reset_password")
     * @Template("user_management/change-password.html.twig")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwords, string $nonce)
    {
        $token = $this->storage->findToken('reset_password', (new OneTimeToken('', $nonce))->getToken());

        if (!$token) {
            throw new AccessDeniedHttpException;
        }

        $user = $token->getUser();

        $form = $this->createFormBuilder()
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
            $raw_password = $form->get('new_password')->getData();
            $password = $passwords->encodePassword($user, $raw_password);
            $user->setPassword($password);

            $this->storage->eraseToken($token);
            $this->entities->flush();

            $this->addFlash('success', 'Password was changed. You may now login.');
            return $this->redirectToRoute('front');
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    private function sendEmail(UserInterface $user, string $nonce) : void
    {
        $content = $this->renderView('email/reset-password.html.twig', [
            'user' => $user,
            'token' => $nonce,
        ]);

        $message = (new Email('Kirkanta: ' . $this->translator->trans('Password recovery')))
            ->setFrom('noreply@kirjastot.fi')
            ->setTo($user->getEmail())
            ->setBody($content, 'text/html')
            ->addPart((new Html2Text($content))->getText(), 'text/plain')
            ;

        $this->mailer->send($message);
    }
}
