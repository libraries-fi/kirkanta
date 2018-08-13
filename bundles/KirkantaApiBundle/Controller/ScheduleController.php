<?php

namespace KirjastotFi\KirkantaApiBundle\Controller;

use App\Entity\Period;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use KirjastotFi\KirkantaApiBundle\Form\ScheduleForm;
use KirjastotFi\KirkantaApiBundle\Validator\Constraints\LanguageAllowed;
use Symfony\Component\HttpFoundation\Request;


class ScheduleController extends FOSRestController
{
    /**
     * @GET("/schedules.{_format}", defaults={"_format": "json" })
     * @QueryParam(name="page_number", key="page", requirements="\d+", default="1")
     * @QueryParam(name="page_size", key="limit", requirements="\d+", default="50")
     * @QueryParam(name="langcode", key="lang", requirements=@LanguageAllowed, nullable=true, strict=true)
     */
    public function search(Request $request, int $page_number, int $page_size, string $langcode = null, Connection $connection)
    {
        $form = $this->createForm(ScheduleForm::class, null, [
            'method' => 'GET',
            'allow_extra_fields' => true,
        ]);
        $form->submit($request->query->all(), true);

        if (!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                var_dump($error->getCause()->getCause());
            }
        }

        $values = $form->getData();
        $values = array_filter($values);
        $builder = $connection->createQueryBuilder()
            ->select('s.*')
            ->from('schedules', 's')
            ;

        foreach ($values as $key => $value) {
            switch ($key) {
                case 'sort':
                    foreach ($value as $field) {
                        if (in_array($field, ['organisation', 'department'])) {
                            $field .= '_id';
                        }
                        $builder->addOrderBy($field);
                    }
                    break;
                case 'start':
                    $builder->andWhere('opens::date >= :start');
                    $builder->setParameter('start', $value);
                    break;
                case 'end':
                    $builder->andWhere('opens::date <= :end');
                    $builder->setParameter('end', $value);
                    break;
                case 'organisation':
                    if (!empty($value['slug'])) {
                        $builder->innerJoin('o', 'organisations_data', 'od', 'o.id = od.entity_id');
                        $builder->andWhere('od.slug IN (:o_slug)');
                        $builder->setParameter('o_slug', $value['slug'], Connection::PARAM_STR_ARRAY);
                    }

                    if (!empty($value['id'])) {
                        $builder->andWhere('s.department_id IN (:o_id)');
                        $builder->setParameter('o_id', $value['id'], Connection::PARAM_INT_ARRAY);
                    }

                    $builder->innerJoin('s', 'organisations', 'o', 's.organisation_id = o.id AND o.state = 1');
                    break;
            }
        }

        if (empty($values['organisation'])) {
            $builder->andWhere('s.department_id = s.organisation_id');
            $builder->andWhere('s.status IS NOT NULL');
        }

        $result = $builder->execute();
        $result->setFetchMode(\PDO::FETCH_LAZY);

        $cache = [];
        $schedules = [];
        $period_ids = [];

        foreach ($result as $row) {
            list($date, $opens) = explode(' ', $row->opens);
            list($_, $closes) = explode(' ', $row->closes . ' ');

            $period_ids[] = $row->period_id;
            $opens = substr($opens, 0, 5) ?: null;
            $closes = substr($closes, 0, 5) ?: null;

            if (!isset($cache[$row->department_id][$date])) {
                $entry = [
                    'date' => $date,
                    'opens' => $closes ? $opens : null,
                    'closes' => $closes,
                    'status' => $row->status,
                    'organisation' => $row->organisation_id,
                    'department' => $row->organisation_id == $row->department_id ? null : $row->department_id,
                    'period' => $row->period_id,
                    'info' => json_decode($row->info),

                    // Using an object here to fix XML serialization.
                    'times' => new ArrayCollection,
                ];
                $cache[$row->department_id][$date] = &$entry;
                $schedules[] = &$entry;
            } else {
                $entry = &$cache[$row->department_id][$date];
            }

            if ($closes) {
                $entry['times'][] = [
                    'opens' => $opens,
                    'closes' => $closes,
                    'staff' => $row->staff,
                ];
                $entry['closes'] = $closes;
            }

            if (is_int($row->status)) {
                $entry['status'] = max($entry['status'], $row['status']);

                // Using empty() as there are no times if the library is closed through the day.
                if (!empty($entry['times'])) {
                    end($entry['times'])['active'] = $row->status > 0;
                }
            }

            unset($entry);
        }

        $result = [
            'result' => new ArrayCollection($schedules),
        ];


        if (in_array('period', $values['refs'] ?? [])) {
            $builder = $this->getDoctrine()->getEntityManager()
                ->createQueryBuilder()
                ->select('p')
                ->from(Period::class, 'p', 'p.id')
                ->orderBy('p.id')
                ->where('p.id IN (:pids)')
                ->setParameter('pids', $period_ids);
            $periods = $builder->getQuery()->getResult();

            $result['refs']['period'] = $periods;
            $result['refs']['period'] = new ArrayCollection($periods);
        }

        $context = (new Context)->setGroups(['default']);

        return $this->view($result)->setContext($context);
    }
}
