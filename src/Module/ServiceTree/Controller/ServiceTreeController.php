<?php

namespace App\Module\ServiceTree\Controller;

use App\Module\ServiceTree\Entity\ServiceCategory;
use App\Module\ServiceTree\Entity\ServiceItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ServiceTreeController extends Controller
{
    /**
     * @Route("/service_category/{id}/add-item", name="entity.service_category.add_item", requirements={"id": "\d+"})
     * @ParamConverter("category", class="ServiceTree:ServiceCategory")
     */
    public function addTreeItemAction(Request $request, ServiceCategory $category)
    {
        exit('add item');
    }

    /**
     * @Route("/service_category/{id}/remove-item/{item_id}", name="entity.service_category.remove_item", requirements={"id": "\d+"})
     * @ParamConverter("category", class="ServiceTree:ServiceCategory")
     * @ParamConverter("item", class="ServiceTree:ServiceItem", options={"id" = "item_id"})
     */
    public function removeTreeItemAction(Request $request, ServiceCategory $category, ServiceItem $item)
    {
        if ($category->getItems()->contains($item)) {
            $category->getItems()->removeElement($item);
        }
        exit('remove item');
    }
}
