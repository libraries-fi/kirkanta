<?php

namespace KirjastotFi\KirkantaApiBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use KirjastotFi\KirkantaApiBundle\Validator\Constraints\LanguageAllowed;
use OutOfBoundsException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Organisation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use KirjastotFi\KirkantaApiBundle\Serializer;
use KirjastotFi\KirkantaApiBundle\StandardSerializerFactory;

class TestController extends FOSRestController
{
    /**
     * @Route("/foo/serialize")
     */
    public function serializeAction()
    {
        $entity = $this->get('doctrine.orm.entity_manager')
            ->getRepository(Organisation::class)
            ->findOneById(84924);

        // $serializer = $this->get('kirkanta.serializer');
        // $serializer = StandardSerializerFactory::create();

        $serializer = $this->get('serializer');

        $json = $serializer->serialize($entity, 'json', [
            'groups' => ['default'],
            // 'langcode' => 'fi',
        ]);

        return new Response($json, 200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ]);
    }
}
