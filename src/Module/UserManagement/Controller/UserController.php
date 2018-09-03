<?php

namespace App\Module\UserManagement\Controller;

use App\EntityTypeManager;
use App\Entity\ListBuilder\UserListBuilder;
use App\Entity\UserGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserAccountsBundle\UserInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints;

use App\Module\UserManagement\Validator\GroupManagerCount;

class UserController extends Controller
{
    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    /**
     * @Route("/user_management", name="user_management.own_group")
     * @Template("entity/list.html.twig")
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
     * @Template("user_management/create-user.html.twig")
     */
    public function createUser(Request $request, UserPasswordEncoderInterface $passwords)
    {
        $builder = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('username', null, [
                'label' => 'Personal name',
                'help' => 'Real name of the employee, not a nickname.'
            ])
            ->add('group', EntityType::class, [
                'label' => 'User group',
                'data' => $this->getUser()->getGroup(),
                'class' => UserGroup::class,
                'choice_label' => 'name',
                'attr' => [
                    'readonly' => true,
                    'class' => 'form-control-plaintext'
                ]
            ])
            ->add('group_manager', CheckboxType::class, [
                'label' => 'Make this account a group administrator',
                'required' => false,
                'constraints' => [new GroupManagerCount([
                    'payload' => $this->getUser()->getGroup()
                ])]
            ])
            ->add('actions', FormType::class, [
                'mapped' => false,
                'required' => false,
            ]);

        if ($this->isGranted('ROLE_ROOT')) {
            $builder->add('group', EntityType::class, [
                'label' => 'User group',
                'class' => UserGroup::class,
                'choice_label' => 'name',
                'placeholder' => '-- Select --',
            ]);
        }

        if ($this->getUser()->isMunicipalAccount()) {
            $builder->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'property_path' => '[raw_password]',
                'required' => false,
                'invalid_message' => 'Passwords did not match',
                'first_options' => [
                    'label' => 'Password'
                ],
                'second_options' => [
                    'label' => 'Password again'
                ],
                'constraints' => [
                    new Constraints\Length(['min' => 8, 'max' => 100])
                ]
            ]);
        }

        $builder->get('actions')->add('submit', SubmitType::class);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account = $this->types->getRepository('user')->create($form->getData());
            // $account->setGroup($this->getUser()->getGroup());

            if ($account->getRawPassword()) {
                $password = $passwords->encodePassword($account, $account->getRawPassword());
                $account->setPassword($password);
            }

            $em = $this->types->getEntityManager();
            $em->persist($account);
            $em->flush();

            $this->addFlash('success', 'Account was created.');
            return $this->redirectToRoute('user_management.own_group');
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * @Route("/user_management/{user}", name="user_management.manage_user", defaults={"entity_type": "user"})
     * @ParamConverter("user", converter="entity_from_type_and_id")
     * @Template("user_management/manage-user.html.twig")
     */
    public function manageUser(Request $request, UserInterface $user)
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
            $this->types->getEntityManager()->flush();
            $this->addFlash('success', 'Changes were saved.');

            return $this->redirectToRoute('user_management.own_group');
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
