<?php

namespace App\Module\MigrationsV3\Command;

use App\Entity\Consortium;
use App\Entity\ConsortiumLogo;
use App\Entity\LibraryPhoto;
use Doctrine\Common\Collections\Criteria;
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
class CreateConsortiumLogoEntities extends Command
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
            ->setName('migrations:create-consortium-logos')
            ->setDescription('Creates consortium logo entities from filenames')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        /**
         * In old Kirkanta the files were stored with all uploads.
         */
        $old_upload_dir = sprintf('%s/%s', self::WEBROOT, $this->mappings->fromField(new LibraryPhoto(), 'file')->getUploadDestination());

        $new_upload_dir = sprintf('%s/%s', self::WEBROOT, $this->mappings->fromField(new ConsortiumLogo(), 'file')->getUploadDestination());

        // $criteria = Criteria::create()->where(Criteria::expr()->neq('old_logo_filename', null));
        // $consortiums = $this->em->getRepository(Consortium::class)->matching($criteria);

        $consortiums = $this->em->getRepository(Consortium::class)->createQueryBuilder('c')
            ->andWhere('c.old_logo_filename IS NOT NULL')
            ->andWhere('c.logo IS NULL')
            ->getQuery()
            ->getResult();

        foreach ($consortiums as $consortium) {
            /**
             * NOTE: No originals preserved before so we only copy small and medium.
             */

            // usleep(10);

            $new_filebasename = str_replace('.', '', uniqid('', true));

            foreach (['small', 'medium'] as $size) {
                try {
                    $old_filepath = sprintf('%s/%s/%s', dirname($old_upload_dir), $size, $consortium->getOldLogoFilename());
                    $new_filename = sprintf('%s.%s', $new_filebasename, substr(strrchr($old_filepath, '.'), 1));
                    $new_dirname = sprintf('%s/%s', dirname($new_upload_dir), $size);

                    $file = new File($old_filepath);
                    $file->move($new_dirname, $new_filename);

                    $output->writeln("MOVE: {$old_filepath} -> {$new_dirname}/{$new_filename}");

                    /**
                     * No originals were preserved so copy the medium size as original.
                     * Intended to make life easier later on should we need to re-process the logos.
                     */
                    if ($size == 'medium') {
                        $original_filepath = sprintf('%s/%s/%s', dirname($new_dirname), 'original', $new_filename);
                        copy("{$new_dirname}/{$new_filename}", $original_filepath);

                        $output->writeln("COPY: {$new_dirname}/{$new_filename} -> {$original_filepath}");

                        $logo = new ConsortiumLogo();
                        $logo->setFilename($new_filename);
                        $logo->setOriginalName(basename($old_filepath));

                        $consortium->setLogo($logo);

                        $this->em->persist($logo);
                    }
                } catch (FileNotFoundException $e) {
                    // pass
                    $output->writeln('MISSING: ' . $old_filepath);
                }
            }
        }

        $this->em->flush();
        $output->writeln('NOTE: Run migrations:update-picture-metadata to insert metadata.');
    }
}
