<?php

namespace App\Module\MigrationsV3\Command;

use App\Entity\ConsortiumLogo;
use App\Entity\LibraryPhoto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * Writes file metadata into the database. It did not exist at all in the previous version.
 */
class UpdatePictureMetadata extends Command
{
    const WEBROOT = 'public';

    private $em;
    private $mappings;

    public function __construct(EntityManagerInterface $entities, PropertyMappingFactory $mappings)
    {
        parent::__construct();
        $this->em = $entities;
        $this->mappings = $mappings;
    }

    protected function configure() : void
    {
        $this
            ->setName('migrations:update-picture-metadata')
            ->setDescription('Update database with picture metadata')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $classes = [LibraryPhoto::class, ConsortiumLogo::class];
        $BATCH_SIZE = 100;

        gc_enable();

        foreach ($classes as $class) {
            $ROUND = 0;
            do {
                $result = $this->em->getRepository($class)->findBy([], ['id' => 'asc'], $BATCH_SIZE, $BATCH_SIZE * $ROUND++);

                foreach ($result as $entity) {
                    try {
                        $basedir = sprintf('%s/%s', self::WEBROOT, $this->mappings->fromField($entity, 'file')->getUploadDestination());
                        $filepath = sprintf('%s/%s', $basedir, $entity->getFilename());

                        $file = new File($filepath);

                        /**
                         * No trace of original name so fallback.
                         */
                        $entity->setOriginalName($entity->getFilename());
                        
                        $entity->setMimeType($file->getMimeType());
                        $entity->setFilesize($file->getSize());
                        $entity->setDimensions($this->getImageSize($filepath));
                        $entity->setSizes($class::DEFAULT_SIZES);

                        foreach ($entity->getSizes() as $size) {
                            try {
                                $sizepath = sprintf('%s/%s/%s', dirname($basedir), $size, $entity->getFilename());
                                $file = new File($sizepath);

                                $entity->addMeta([
                                    $size => [
                                        'dimensions' => $this->getImageSize($file->getRealPath()),
                                        'filesize' => $file->getSize(),
                                    ]
                                ]);
                            } catch (FileNotFoundException $e) {
                                // pass
                            }
                        }
                    } catch (FileNotFoundException $e) {
                        // pass
                    }
                }

                $this->em->flush();
                $this->em->clear();

                gc_collect_cycles();
            } while (!empty($result));
        }
    }

    private function getImageSize(string $filename) : ?array
    {
        if ($data = getimagesize($filename)) {
            return array_slice($data, 0, 2);
        }

        return null;
    }
}
