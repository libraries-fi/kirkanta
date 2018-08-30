<?php

namespace App\Module\Schedules\Command;

use App\Entity\Library;
use App\Module\Schedules\ScheduleBuilder;
use App\Module\Schedules\ScheduleManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildSchedules extends Command
{
    private $entities;
    private $schedules;

    public function __construct(EntityManagerInterface $entities, ScheduleManager $schedules)
    {
        parent::__construct();
        $this->entities = $entities;
        $this->schedules = $schedules;
    }

    protected function configure() : void
    {
        $this
            ->setName('schedules:build')
            ->setDescription('Build schedules for libraries')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        // Logging consumes memory a lot and doesn't clear up when flushing the EM.
        $this->entities->getConnection()->getConfiguration()->setSQLLogger(null);

        $ROUND = 0;
        $BATCH_SIZE = 100;

        gc_enable();

        do {
            $result = $this->entities
                ->getRepository(Library::class)
                ->createQueryBuilder('o')
                ->orderBy('o.id')
                ->setMaxResults($BATCH_SIZE)
                ->setFirstResult($BATCH_SIZE * $ROUND++)
                ->getQuery()
                ->getResult();

            foreach ($result as $library) {
                $this->schedules->updateSchedules($library, new DateTime('Monday this week'), new DateTime('Sunday +12 months'));
            }

            $output->writeln('Progress: ' . ($BATCH_SIZE * $ROUND));
            $this->entities->clear();

            // Even if we're not gonna run out of memory, it is better to save it for processes
            // that actually need it.
            gc_collect_cycles();
        } while (!empty($result));

        $output->writeln('done');
    }
}
