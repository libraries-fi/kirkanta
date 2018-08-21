<?php

namespace App\Controller;

use App\EntityTypeManager;
use App\Entity\Notification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SystemController extends Controller
{
    use Feature\ProvideEntityTypeManager;

    /**
     * @Route("/", name="front")
     */
    public function frontAction(Request $request, AuthorizationCheckerInterface $auth, EntityTypeManager $entities)
    {
        if (!$auth->isGranted('MANAGE_ALL_ENTITIES')) {
            $organisations = $entities->getListBuilder('organisation')->load();
            $consortiums = $entities->getListBuilder('consortium')->load();
            $finna_organisations = $entities->getListBuilder('finna_organisation')->load();
        }

        if ($user = $this->getUser()) {
            $storage = $this->getEntityTypeManager()->getRepository('notification');
            $notifications = $storage->findUnreadByUser($user);
        } else {
            $notifications = [];
        }

        return $this->render('index.html.twig', [
            'notifications' => $notifications,
            'organisations' => $organisations ?? null,
            'consortiums' => $consortiums ?? null,
            'finna_organisations' => $finna_organisations ?? null,
        ]);
    }

    /**
     * @Route("/user/notifications", name="user.notifications")
     * @Template("user/notifications.html.twig")
     */
    public function userNotificationsAction()
    {
        $manager = $this->getEntityTypeManager();
        $list_builder = $manager->getListBuilder('notification');
        $result = $list_builder->load();
        $unread = $manager->getRepository('notification')->findUnreadByUser($this->getUser());

        $table = $list_builder->build($result)
            ->transform('subject', function($n) use($unread) {
                $template = '<a href="{{ path("user.show_notification", {"type": "notification", "notification": row.id}) }}">{{ row.subject }}</a>';

                if (in_array($n, $unread)) {
                    $template = '<b>' . $template . '</b>';
                }

                return $template;
            });

        return [
            'table' => $table,
        ];
    }

    /**
     * @Route("/user/notifications/{notification}", name="user.show_notification")
     * @ParamConverter("notification", class="App:Notification")
     * @Template("user/show_notification.html.twig")
     */
    public function showUserNotification(Notification $notification)
    {
        $this->getUser()->getReadNotifications()->add($notification);
        $this->getEntityManager()->flush();

        return [
            'notification' => $notification
        ];
    }

    /**
     * @Route("/profile", name="user.profile")
     * @Template("user/profile.html.twig")
     */
    public function userProfileAction()
    {
        return [];
    }

    /**
     * @Route("/help", name="system.help")
     * @Template("help.html.twig")
     */
    public function helpAction()
    {

    }
}
