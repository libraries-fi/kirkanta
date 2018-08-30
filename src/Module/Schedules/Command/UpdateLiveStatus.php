<?php

namespace App\Module\Schedules\Command;

use App\Entity\Library;
use DateTime;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateLiveStatus extends Command
{
    private $db;

    public function __construct(Connection $db)
    {
        parent::__construct();
        $this->db = $db;
    }

    protected function configure() : void
    {
        $this
            ->setName('schedules:update-status')
            ->setDescription('Update live status of libraries')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->db->beginTransaction();

        /*
         * NOTE: It is REQUIRED that every row for the current date is set to a non-NULL value,
         * because then we can filter by status even when no row actually matches current time.
         *
         * Just use max(live_status) with queries.
         */
        $this->db->query('
            UPDATE schedules
            SET live_status =
                CASE
                    WHEN opens <= NOW() AND closes > NOW() AND staff = true
                        THEN 1
                    WHEN opens <= NOW() AND closes > NOW() AND staff = false
                        THEN 2
                    ELSE 0
                END
            WHERE date(opens) = CURRENT_DATE
        ');

        $this->db->query('
            UPDATE schedules
            SET live_status = NULL
            WHERE date(opens) != CURRENT_DATE AND live_status IS NOT NULL
        ');

        $this->db->commit();
        $output->writeln('done');
    }
}
