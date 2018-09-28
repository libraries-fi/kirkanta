<?php

namespace App\Module\ApiCache\Command;

use App\Entity\Feature\StateAwareness;
use App\Module\ApiCache\DocumentManager;
use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexEntities extends Command
{
    private $cache;

    public function __construct(DocumentManager $cache)
    {
        parent::__construct();
        $this->cache = $cache;
        $this->types = $this->cache->getEntityTypeManager();
    }

    protected function configure() : void
    {
        $this
            ->setName('api-cache:index')
            ->setDescription('Cache entities for Kirkanta API')
            ->addArgument('entity_type', InputArgument::REQUIRED, 'Entity type')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $entity_type = $input->getArgument('entity_type');;
        $entity_class = $this->types->getEntityClass($entity_type);

        if (!is_a($entity_class, ApiCacheable::class, true)) {
            throw new InvalidArgumentException('This entity type cannot be indexed');
        }

        $ROUND = 0;
        $BATCH_SIZE = 100;

        $em = $this->types->getEntityManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        gc_enable();

        do {
            $parameters = is_a($entity_class, StateAwareness::class, true)
            ? ['state' => StateAwareness::PUBLISHED]
            : [];

            $result = $this->types
                ->getRepository($entity_type)
                ->findBy($parameters, ['id' => 'asc'], $BATCH_SIZE, $BATCH_SIZE * $ROUND++);

            foreach ($result as $entity) {
                $this->cache->write($entity);
            }

            $output->writeln('Progress: ' . ($BATCH_SIZE * $ROUND));

            $em->flush();
            $em->clear();

            gc_collect_cycles();
        } while (!empty($result));

        $output->writeln('done');
    }
}
