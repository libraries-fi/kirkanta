<?php

namespace App\Module\Schedules\Controller;

use App\Entity\Library;
use App\Module\Schedules\ScheduleBuilder;
use App\Module\Schedules\ScheduleManager;
use DateTime;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SchedulesController extends Controller
{
    const BATCH_SIZE = 200;

    /**
     * @Route("/system/schedules/build")
     * @QueryParam(name="batch", key="batch", requirements="\d+", default="1")
     */
    public function buildAction(ScheduleManager $manager, int $batch)
    {
        $result = $this->get('doctrine.orm.entity_manager')
            ->getRepository(Library::class)
            ->createQueryBuilder('o')
            ->orderBy('o.id')
            ->setMaxResults(self::BATCH_SIZE)
            ->setFirstResult(self::BATCH_SIZE * ($batch - 1))
            ->getQuery()
            ->getResult();

        $database = $this->get('doctrine.orm.entity_manager')->getConnection();

        foreach ($result as $library) {
            $manager->updateSchedules($library, new DateTime('Monday this week'), new DateTime('Sunday +12 months'));
        }

        if (!empty($result)) {
            return $this->redirect(sprintf('?batch=%d', $batch + 1));
        }

        exit('finished');
    }

    /**
     * @Route("/system/schedules/update-realtime")
     */
    function updateRealtimeStatus()
    {
        /*
         * NOTE: It is important to set EVERY time row of CURRENT_DATE to non-null value, because
         * it allows us to fetch all rows for a particular date even when filtering by status.
         */

        $database = $this->get('doctrine.orm.entity_manager')->getConnection();
        $database->beginTransaction();

        $database->query('
            UPDATE schedules
            SET status =
                CASE
                    WHEN opens <= NOW() AND closes > NOW() AND staff = true
                        THEN 1
                    WHEN opens <= NOW() AND closes > NOW() AND staff = false
                        THEN 2
                    ELSE
                        0
                END
            WHERE date(opens) = CURRENT_DATE;
        ');

        $database->query('
            UPDATE schedules
            SET status = NULL
            WHERE status IS NOT NULL AND DATE(opens) != CURRENT_DATE;
        ');

        $database->commit();

        return new Response('finished');
    }
}
