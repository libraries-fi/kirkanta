<?php

namespace App\Module\Ptv\EventListener;

use App\Entity\Address;
use App\Entity\EntityDataBase;
use App\Entity\Library;
use App\Module\Ptv\Client;
use App\Module\Ptv\Exception\AuthenticationException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ExportListener
{
    private $client;
    private $flashes;

    public function __construct(Client $client, SessionInterface $session)
    {
        $this->client = $client;
        $this->flashes = $session->getFlashBag();
    }

    // public function preUpdate($event) : void
    // {
    //     $this->postUpdate($event);
    // }

    /**
     * NOTE: We don't need to handle postPersist as only existing entities can be configured for
     * PTV exports.
     */
    public function postUpdate(LifecycleEventArgs $event) : void
    {
        try {
            $entity = $this->getMainEntity($event->getEntity());
            $this->client->store($entity);
        } catch (InvalidArgumentException $e) {
            /*
             * This exception could be thrown from this class or the PTV client.
             *
             * It means that the entity should not be pushed to PTV so we can drop it.
             */
        } catch (AuthenticationException $e) {
            $this->flashes->add('danger', $e->getMessage());

            if ($previous = $e->getPrevious()) {
                $response = json_decode($previous->getResponse()->getBody())->message;
                $this->flashes->add('danger', $response);
            }
        }
    }

    private function getMainEntity($entity)
    {
        if ($entity instanceof Library) {
            return $entity;
        } elseif ($entity instanceof EntityDataBase) {
            /*
             * Resolve translation entity to its parent.
             *
             * NOTE: Could be a translation of any entity, not just of Library.
             */
            return $this->getMainEntity($entity->getEntity());
        } elseif (method_exists($entity, 'getLibrary')) {
            // Resources bound to Library entities have this method.
            return $this->getMainEntity($entity->getLibrary());
        }

        throw new InvalidArgumentException('Failed to resolve main entity');
    }
}
