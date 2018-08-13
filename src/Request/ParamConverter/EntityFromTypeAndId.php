<?php

namespace App\Request\ParamConverter;

use App\EntityTypeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityFromTypeAndId implements ParamConverterInterface
{
    const ID = 'entity_from_type_and_id';

    private $types;

    public function __construct(EntityTypeManager $types)
    {
        $this->types = $types;
    }

    public function supports(ParamConverter $configuration) : bool
    {
        // Accept only if this converted was explicitly defined.
        return $configuration->getConverter() == self::ID;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $type = $request->attributes->get('entity_type');
        $id = $request->attributes->get($type);
        $entity = $this->types->getRepository($type)->findOneById($id);

        if ($entity) {
            $request->attributes->set($configuration->getName(), $entity);
        } else {
            throw new NotFoundHttpException(sprintf('Object not found for parameters type=\'%s\' and id=\'%s\'.', $type, $id));
        }
    }
}
