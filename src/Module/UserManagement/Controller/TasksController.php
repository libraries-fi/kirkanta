<?php

namespace App\Module\UserManagement\Controller;

use App\Module\UserManagement\Entity\OneTimeToken;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

use Swift_Mailer as Mailer;
use Swift_Message as Email;

use UserAccountsBundle\UserInterface;

class TasksController extends Controller
{
    private $entities;
    private $storage;

    public function __construct(EntityManagerInterface $entities, Mailer $mailer)
    {
        $this->entities = $entities;
        $this->storage = $entities->getRepository(OneTimeToken::class);
        $this->mailer = $mailer;
    }

    /**
     * @Route("/reset-password", name="user_management.request_reset_password")
     * @Template("user_management/request-password.html.twig")
     */
    public function requestResetPassword(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Email address'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->entities->getRepository('App:User')->findOneBy([
                'email' => $form->get('email')->getData(),
                'municipal_account' => false,
            ]);

            if ($user) {
                $nonce = bin2hex(random_bytes(20));
                $token = $this->storage->createToken($nonce, 'reset_password');
                $token->setUser($user);

                $this->sendEmail($user, $nonce);

                $this->entities->persist($token);
                $this->entities->flush();
            }

            $this->addFlash('success', 'If there was an account with this email address, you will be emailed with a recovery link.');

            return $this->redirectToRoute('user_management.request_reset_password');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/reset-password/{token}", name="user_management.reset_password")
     */
    public function resetPassword(string $token)
    {

    }

    private function sendEmail(UserInterface $user, string $nonce) : void
    {
        $content = $this->renderView('email/reset-password.html.twig', [
            'user' => $user,
            'token' => $nonce,
        ]);

        $message = (new Email('Password recovery'))
            ->setFrom('noreply@kirjastot.fi')
            ->setTo($user->getEmail());

        $this->mailer->send($message);
    }
}