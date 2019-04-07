<?php

namespace App\Module\UserManagement\Controller;

use App\EntityTypeManager;
use App\Entity\ListBuilder\UserListBuilder;
use App\Entity\UserGroup;
use App\Module\Email\Mailer;
use App\Module\UserManagement\AccountCreatedEmail;
use App\Module\UserManagement\Entity\OneTimeToken;
use App\Module\UserManagement\Validator\GroupManagerCount;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints;
use UserAccountsBundle\UserInterface;

class UserController extends Controller
{
    private $types;

    public function __construct(EntityTypeManager $types, UserPasswordEncoderInterface $passwords)
    {
        $this->types = $types;
        $this->passwords = $passwords;
    }

    /**
     * @Route("/user_management", name="user_management.own_group")
     * @Template("entity/collection.html.twig")
     */
    public function usersFromGroup()
    {
        $group = $this->getUser()->getGroup();
        $builder = $this->types->getListBuilder('user');
        $builder->getQueryBuilder()->andWhere('e.group = :group')->setParameter('group', $group);

        $table = $builder
            ->build($builder->load())
            ->removeColumn('group')
            ->useAsTemplate('roles')
            ->setLabel('roles', 'Role')
            ->transform('name', function() {
                return '<a href="{{ path("user_management.manage_user", {user: row.id}) }}">{{ row.username }}</a>';
            })
            ->transform('roles', function($user) {
                $label = in_array(GroupManagerCount::GROUP_MANAGER_ROLE, $user->getRoles(true)) ? 'Group manager' : 'User';
                return "{% trans %}{$label}{% endtrans %}";
            });

        $actions = [
            'add' => [
                'title' => 'Create new',
                'route' => 'user_management.create_user',
                'icon' => 'fas fa-plus-circle'
            ]
        ];

        return [
            'entity_type' => 'user',
            'type_label' => $this->types->getTypeLabel('user', true),
            'group' => $group,
            'table' => $table,
            'actions' => $actions,
        ];
    }

    /**
     * @Route("/user_management/add", name="user_management.create_user", defaults={"entity_type": "user"})
     * @Route("/admin/user/add", name="entity.user.add", defaults={"entity_type": "user"})
     * @Template("user_management/create-user.html.twig")
     */
    public function createUser(Request $request, Mailer $mailer)
    {
        $account = new \App\Entity\User;
        $form = $this->types->getForm('user', 'add', $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($account->getRawPassword()) {
                $password = $this->passwords->encodePassword($account, $account->getRawPassword());
                $account->setPassword($password);
            }

            // $token = new OneTimeToken('activate_account');
            // $token->setUser($account);
            //
            // $message = new AccountCreatedEmail($token);
            // $mailer->send($message);

            $em = $this->types->getEntityManager();
            $em->persist($account);
            // $em->persist($token);
            $em->flush();

            $this->addFlash('success', 'Account was created.');
            // $this->addFlash('success', 'Activation email sent.');
            // $this->addFlash('success', $token->getNonce());
            return $this->redirectToRoute('user_management.own_group');
        }

        return [
            'form' => $form->createView(),
            'user' => $account,
        ];
    }

    /**
     * @Route("/user_management/{user}", name="user_management.manage_user", defaults={"entity_type": "user"})
     * @Route("/admin/user/{user}", name="entity.user.edit", defaults={"entity_type": "user", "is_admin": true})
     * @ParamConverter("user", converter="entity_from_type_and_id")
     * @Template("user_management/manage-user.html.twig")
     */
    public function manageUser(Request $request, UserInterface $user, bool $is_admin = false)
    {
        $form = $this->types
            ->getForm('user', 'edit', $user)
            ->remove('group')
            ->remove('roles')
            ->add('group_manager', CheckboxType::class, [
                'label' => 'Make this account a group administrator',
                'required' => false,
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getRawPassword()) {
                $password = $this->passwords->encodePassword($user, $user->getRawPassword());
                $user->setPassword($password);

                $this->addFlash('success', 'Password was changed.');
            }
            $this->types->getEntityManager()->flush();
            $this->addFlash('success', 'Changes were saved.');

            return $this->redirectToRoute($is_admin ? 'entity.user.collection' : 'user_management.own_group');
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user_management/group-admins", name="user_management.manage_group_admins", defaults={"entity_type": "user"})
     */
    public function manageAdmins()
    {
        exit('manage admins');
    }
}
