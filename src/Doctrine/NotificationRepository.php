<?php

namespace App\Doctrine;

use DateTime;
use DateInterval;
use App\Entity\Notification;
use App\Entity\User;

class NotificationRepository extends EntityRepository
{
    public function findUnreadByUser(User $user)
    {
        $limit = (new DateTime)->sub(new DateInterval('P14D'));
        $dql = '
            SELECT n
            FROM App\Entity\Notification n
            WHERE n.created >= :date AND n.id NOT IN (
                SELECT m.id
                FROM App\Entity\User u
                JOIN u.read_notifications m
                WHERE u.id = :user_id
            )
            ORDER BY n.created DESC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user_id', $user->getId());
        $query->setParameter('date', $limit);
        return $query->getResult();
    }

    public function findByUser(User $user)
    {
        $dql = '
            SELECT n
            FROM App\Entity\Notification n
            ORDER BY n.created DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user_id', $user->getId());
        return $query->getResult();
    }

    public function readNotification(User $user, Notification $notification)
    {
        $user->addReadNotification($notification);
        $this->getEntityManager()->flush($user);
    }
}
