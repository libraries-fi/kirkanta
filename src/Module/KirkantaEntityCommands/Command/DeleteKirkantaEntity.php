<?php

namespace App\Module\KirkantaEntityCommands\Command;

use App\Entity\Library;
use App\EntityTypeManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteKirkantaEntity extends Command
{

    private $entityTypeManager;

    public function __construct(EntityTypeManager $entityTypeManager)
    {
        parent::__construct();
        
        $this->entityTypeManager = $entityTypeManager;
    }

    protected function configure() : void
    {
        $this
            ->setName('kirkanta-entity:delete')
            ->setDescription('Delete entity with type and id')
            ->addArgument('entity_type', InputArgument::REQUIRED, 'Entity type')
            ->addArgument('entity_id', InputArgument::REQUIRED, 'Entity ID')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        
        $entity_type = $input->getArgument('entity_type');
        $entity_id = $input->getArgument('entity_id');

        switch ($entity_type) {
            case 'library':
                $em = $this->entityTypeManager->getEntityManager();
                $library = $em->getRepository(Library::class)
                    ->find($entity_id);
                if($library) {
                    $em->remove($library);
                    $em->flush($library);
                } else {
                    throw new \Exception('No library with id: ' . $entity_id);
                }
                break;

            default:
                throw new \Exception('Invalid entity type');
        }
    }
}
