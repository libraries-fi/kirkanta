<?php

namespace App\Module\Ptv\Converter;

use DateTime;
use App\Entity\Library;
use App\Module\Ptv\Util\Address;
use App\Module\Ptv\Util\Html;
use App\Module\Ptv\Util\Language;
use App\Module\Ptv\Util\Municipalities;
use App\Module\Ptv\Util\OpeningTimes;
use App\Module\Ptv\Util\PhoneNumber;
use App\Module\Ptv\Util\Text;
use App\Module\Schedules\ScheduleManager;

class LibraryConverter implements Converter
{
    private $schedules;
    private $municipalities;

    public function __construct(ScheduleManager $schedules, Municipalities $municipalities)
    {
        $this->schedules = $schedules;
        $this->municipalities = $municipalities;
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
        $city_name = $library->getCity()->getTranslation('fi')->getName();

        $doc = [
            // FIXME: Replace with a configurable ID.
            // VAASA == 7fdd7f84-e52a-4c17-a59a-d7c2a3095ed5
            'organizationId' => '7fdd7f84-e52a-4c17-a59a-d7c2a3095ed5',

            'organizationType' => 'Municipality',
            'publishingStatus' => 'Published',
            'sourceId' => 'kirkanta--' . $library->getId(),
            'municipality' => $this->municipalities->nameToId($city_name),
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

            if ($translation->getDescription()) {
                $doc['serviceChannelDescriptions'][] = [
                    'type' => 'Description',
                    'language' => $langcode,
                    'value' => Html::toPlainText($translation->getDescription(), 4000),
                ];
            }

            if ($translation->getSlogan()) {
                $doc['serviceChannelDescriptions'][] = [
                    'type' => 'Summary',
                    'language' => $langcode,
                    'value' => Text::truncate($translation->getSlogan(), 150)
                ];
            }
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
                'type' => 'Location',
                'subtype' => 'Single',
                'streetAddress' => [
                    'postalCode' => $address->getZipCode(),
                ]
            ];

            foreach ($address->getTranslations() as $langcode => $translation) {
                if (!Language::isAllowed($langcode)) {
                    continue;
                }

                list($street, $number) = Address::parseStreetAndNumber($translation->getStreet());

                $doc['addresses'][0]['streetAddress']['street'][] = [
                    'language' => $langcode,
                    'value' => $street,
                ];

                if ($number) {
                    $doc['addresses'][0]['streetAddress']['streetNumber'] = $number;
                }
            }

            if ($coords = $address->getCoordinates()) {
                list($lat, $lon) = explode(',', $coords);
                $doc['addresses'][0]['streetAddress'] += [
                    'latitude' => (float)$lat,
                    'longitude' => (float)$lon,
                ];
            }
        }

        if ($address = $library->getMailAddress()) {
            $doc['addresses'][1] = [
                'type' => 'Postal',
                'subtype' => 'Street',
            ];

            if ($pbox = $address->getBoxNumber()) {
                $doc['addresses'][1]['subtype'] = 'PostOfficeBox';
                $doc['addresses'][1]['postOfficeBoxAddress']['postalCode'] = $address->getZipcode();
                $doc['addresses'][1]['postOfficeBoxAddress']['postOfficeBox'] = [
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
            } else {
                $doc['addresses'][1]['streetAddress']['postalCode'] = $address->getZipcode();
                foreach ($address->getTranslations() as $langcode => $translation) {
                    if (!Language::isAllowed($langcode)) {
                        continue;
                    }

                    if ($translation->getStreet()) {
                        list($street, $number) = Address::parseStreetAndNumber($translation->getStreet());
                        $doc['addresses'][1]['streetAddress']['street'][] = [
                            'language' => $langcode,
                            'value' => $street,
                        ];

                        if ($number) {
                            $doc['addresses'][1]['streetAddress']['streetNumber'] = $number;
                        }
                    } else {
                        $doc['addresses'][1]['streetAddress']['street'][] = [
                            'language' => $langcode,
                            'value' => $translation->getArea(),
                        ];
                    }
                }
            }
        }

        // header('Content-Type: text/plain');
        // print_r($doc['addresses']);
        // exit;

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
                'serviceHourtype' => $period->isContinuous() ? 'DaysOfTheWeek' : 'Exceptional',
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
