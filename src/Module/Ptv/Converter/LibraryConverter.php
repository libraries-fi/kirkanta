<?php

namespace App\Module\Ptv\Converter;

use DateTime;
use App\Entity\Library;
use App\Module\Ptv\Util\Address;
use App\Module\Ptv\Util\Html;
use App\Module\Ptv\Util\Language;
use App\Module\Ptv\Util\OpeningTimes;
use App\Module\Ptv\Util\PhoneNumber;
use App\Module\Ptv\Util\Text;
use App\Module\Schedules\ScheduleManager;

class LibraryConverter implements Converter
{
    private $schedules;

    public function __construct(ScheduleManager $schedules)
    {
        $this->schedules = $schedules;
    }

    public function supports($entity) : bool
    {
        return $entity instanceof Library;
    }

    public function getDocumentType($library) : string
    {
        return 'ServiceChannel/Servicelocation';
    }

    public function convert($library) : array
    {
        $doc = [
            'organizationId' => null,
            'sourceId' => 'kirkanta:' . $library->getId(),
            'publishingStatus' => 'Published',
            'organizationType' => 'Municipality',
            'municipality' => 'RESOLVED_ID_HERE',
        ];

        $languages = [];

        foreach ($library->getTranslations() as $langcode => $translation) {
            if (!Language::isAllowed($langcode)) {
                continue;
            }

            $languages[$langcode] = true;
            $doc['serviceChannelNames'][] = [
                'language' => $langcode,
                'value' => Text::truncate($translation->getName(), 100),
            ];

            $doc['supportEmails'][] = [
                'language' => $langcode,
                'value' => $translation->getEmail(),
            ];

            $doc['serviceChannelDescriptions'][] = [
                'type' => 'Description',
                'language' => $langcode,
                'value' => Html::toPlainText($translation->getDescription(), 4000),
            ];

            $doc['serviceChannelDescriptions'][] = [
                'type' => 'ShortDescription',
                'language' => $langcode,
                'value' => Text::truncate($translation->getSlogan(), 150)
            ];
        }

        foreach ($library->getPhoneNumbers() as $number) {
            foreach ($number->getTranslations() as $langcode => $translation) {
                if (!Language::isAllowed($langcode)) {
                    continue;
                }
                $doc['supportPhones'][] = [
                    'language' => $langcode,
                    'additionalInformation' => $translation->getName(),
                    'number' => PhoneNumber::normalize($number->getNumber()),
                    'isFinnishServiceNumber' => true,
                    'serviceChargeType' => 'Free',
                ];
            }
        }

        if ($address = $library->getAddress()) {
            $doc['addresses'][0] = [
                'type' => 'Visiting',
                'postalCode' => $address->getZipCode(),
            ];

            foreach ($address->getTranslations() as $langcode => $translation) {
                if (!Language::isAllowed($langcode)) {
                    continue;
                }

                list($street, $number) = Address::parseStreetAndNumber($translation->getStreet());

                $doc['addresses'][0]['streetAddress'][] = [
                    'language' => $langcode,
                    'value' => $street,
                ];

                if ($number) {
                    $doc['addresses'][0]['streetNumber'] = $number;
                }
            }

            if ($coords = $address->getCoordinates()) {
                list($lat, $lon) = explode(',', $coords);
                $doc['addresses'][0] += [
                    'latitude' => (float)$lat,
                    'longitude' => (float)$lon,
                ];
            }
        }

        if ($address = $library->getMailAddress()) {
            $doc['addresses'][1] = [
                'type' => 'Postal',
                'postalCode' => $address->getZipcode(),
            ];

            if ($pbox = $address->getBoxNumber()) {
                $doc['addresses'][1]['postOfficeBox'] = [
                    [
                        'language' => 'fi',
                        'value' => "PL {$pbox}"
                    ],
                    [
                        'language' => 'en',
                        'value' => "P.O. Box {$pbox}",
                    ],
                    [
                        'language' => 'sv',
                        'value' => "PB {$pbox}",
                    ]
                ];
            }

            foreach ($address->getTranslations() as $langcode => $translation) {
                if (!Language::isAllowed($langcode)) {
                    continue;
                }

                if ($translation->getStreet()) {
                    list($street, $number) = Address::parseStreetAndNumber($translation->getStreet());
                    $doc['addresses'][1]['streetAddress'][] = [
                        'language' => $langcode,
                        'value' => $street,
                    ];

                    if ($number) {
                        $doc['addresses'][1]['streetNumber'] = $number;
                    }
                } else {
                    $doc['addresses'][1]['streetAddress'][] = [
                        'language' => $langcode,
                        'value' => $translation->getArea(),
                    ];
                }
            }
        }

        $last_period_begins = null;

        foreach ($library->getPeriods() as $period) {
            /*
             * NOTE: Periods are ordered by validity from neweest to oldest.
             */
            if (!OpeningTimes::acceptPeriod($period)) {
                continue;
            }

            if ($last_period_begins && $last_period_begins < new DateTime) {
                continue;
            }

            $schedules = [
                'publishingStatus' => 'Published',
                'serviceHourtype' => $period->isContinuous() ? 'Standard' : 'Exception',
                'validFrom' => $period->getValidFrom()->format(DateTime::RFC3339)
            ];

            if ($period->getValidUntil()) {
                $schedules['validTo'] = $period->getValidUntil()->format(DateTime::RFC3339);
            } elseif ($last_period_begins) {
                $schedules['validTo'] = $last_period_begins->format(DateTime::RFC3339);
            }

            foreach (OpeningTimes::periodDefinitions($period) as $day) {
                $merge = true;
                $last_open = 0;

                foreach ($day['times'] as $i => $tuple) {
                    if ($i == 0) {
                        continue;
                    }

                    if (!isset($tuple['closed']) && $merge) {
                        $day['times'][$last_open]['closes'] = $tuple['closes'];

                        // NOTE: Using 'closed' status to filter rows in the next loop.
                        $tuple['closed'] = true;
                        continue;
                    } elseif (!$tuple['closed']) {
                        $last_open = $i;
                    }

                    $merge = !$tuple['closed'];
                }

                foreach ($day['times'] as $tuple) {
                    if (!isset($tuple['closed'])) {
                        $schedules['openingHour'][] = [
                            'dayFrom' => $day['date']->format('l'),
                            'from' => $tuple['opens'],
                            'to' => $tuple['closes'],
                        ];
                    }
                }
            }

            $doc['serviceHours'][] = $schedules;
            $last_period_begins = $period->getValidFrom();
        }

        $doc['languages'] = array_keys($languages);

        return $doc;
    }
}
