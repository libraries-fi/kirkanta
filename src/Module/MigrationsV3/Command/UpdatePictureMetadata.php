<?php

namespace App\Module\MigrationsV3\Command;

use App\Entity\ConsortiumLogo;
use App\Entity\LibraryPhoto;
Use Doctrine\ORM\EntityManagerInterface;
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
    private $em;
    private $mappings;

    const WEBROOT = 'public';

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
                    $entity->setSizes($class::DEFAULT_SIZES);

                    $basedir = sprintf('%s/%s', self::WEBROOT, $this->mappings->fromField($entity, 'file')->getUploadDestination());
                    $filepath = sprintf('%s/%s', $basedir, $entity->getFilename());

                    $file = new File($filepath);

                    $entity->setMimeType($file->getMimeType());
                    $entity->setFilesize($file->getSize());
                    $entity->setDimensions($this->getImageSize($filepath));

                    foreach ($entity->getSizes() as $size) {
                        try {
                            $file = new File("{$basedir}/{$size}/" . $entity->getFilename());

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
