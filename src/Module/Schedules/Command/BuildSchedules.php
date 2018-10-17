<?php

namespace App\Module\Schedules\Command;

use App\Entity\Library;
use App\Module\Schedules\Exception\LegacyPeriodException;
use App\Module\Schedules\ScheduleBuilder;
use App\Module\Schedules\ScheduleManager;
use DateInterval;
use DatePeriod;
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

            /**
             * Iterate range in slices to avoid issues with potential LEGACY PERIODS.
             * If processed range contains a legacy period, schedules cannot be generated.
             */
            foreach ($result as $library) {
                $begin = new DateTime('Monday this week');
                $end = new DateTime('Sunday +12 months');
                $interval = new DateInterval('P1M');
                $iterator = new DatePeriod($begin, $interval, $end);

                foreach ($iterator as $end) {
                    try {
                        $this->schedules->updateSchedules($library, $begin, $end);
                        $begin = $end;
                    } catch (LegacyPeriodException $e) {
                        // pass
                    }
                }
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
