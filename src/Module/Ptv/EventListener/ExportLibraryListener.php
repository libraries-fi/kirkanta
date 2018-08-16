<?php

namespace App\Module\Ptv\EventListener;

use App\Entity\Library;
use App\Module\Ptv\Client;
use App\Module\Ptv\Exception\AuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ExportLibraryListener
{
    private $entities;
    private $flashes;
    private $ptv;

    public function __construct(EntityManagerInterface $entities, Client $ptv, SessionInterface $session)
    {
        $this->entities = $entities;
        $this->flashes = $session->getFlashBag();
        $this->ptv = $ptv;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $route_name = $event->getRequest()->attributes->get('_route');

        if ($route_name == 'entity.library.edit' && $request->isMethod('POST') && $response->getStatusCode() < 400) {
            $id = $request->attributes->get('library');
            $library = $this->entities->getRepository(Library::class)->findOneById($id);

            try {
                $ptv_data = $this->ptv->store($library);

                if ($ptv_data->isEnabled()) {
                    $this->entities->flush($ptv_data);
                    $this->flashes->add('form.success', 'Library synced with PTV');
                }
            } catch (AuthenticationException $e) {
                $this->flashes->add('form.danger', $e->getMessage());

                if ($previous = $e->getPrevious()) {
                    $response = json_decode($previous->getResponse()->getBody())->message;
                    $this->flashes->add('danger', $response);
                }
            } catch (InvalidArgumentException $e) {
                /*
                 * This exception means that the entity should not be pushed to PTV so we can drop it.
                 */
            }
        }
    }
}
