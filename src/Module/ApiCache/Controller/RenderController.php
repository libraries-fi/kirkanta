<?php

namespace App\Module\ApiCache\Controller;

use App\Entity\Feature\StateAwareness;
use App\Module\ApiCache\DocumentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RenderController extends Controller
{
    const BATCH_SIZE = 100;

    public function __construct(DocumentManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Route("/system/api-cache/{entity_type}", name="apicache.indexer")
     */
    public function renderAction(Request $request, string $entity_type)
    {
        $batch = $request->query->getInt('i', 1);
        $types = $this->cache->getEntityTypeManager();
        $entity_class = $types->getEntityClass($entity_type);

        $params = [];

        if (is_a($entity_class, StateAwareness::class, true)) {
            $params['state'] = StateAwareness::PUBLISHED;
        }

        $entities = $types->getRepository($entity_type)->findBy($params, ['id' => 'asc'], self::BATCH_SIZE, ($batch - 1) * self::BATCH_SIZE);

        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $this->cache->write($entity);
            }
            return $this->redirectToRoute('apicache.indexer', [
                'entity_type' => $entity_type,
                'i' => $batch + 1
            ]);
        }

        exit('finished :)');
    }
}
