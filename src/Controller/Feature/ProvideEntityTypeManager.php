<?php

namespace App\Controller\Feature;

use Doctrine\ORM\EntityManagerInterface;
use App\EntityTypeManager;

trait ProvideEntityTypeManager
{
    public function getEntityTypeManager() : EntityTypeManager
    {
        return $this->container->get('entity_type_manager');
    }

    /**
     * @deprecated
     */
    public function getEntityManager() : EntityManagerInterface
    {
        return $this->getDoctrine()->getManager();
    }
}
