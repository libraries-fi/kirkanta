<?php

namespace App\Module\ExportLibraryContactInfo\Controller;

use App\Entity\Address;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use App\Util\LibraryTypes;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ExportController extends Controller
{
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/admin/tools/export-contacts", name="export.library-contact-info")
     * @Template("admin/export/library.contact-info.html.twig")
     */
    public function form(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('types', ChoiceType::class, [
                'label' => 'Types',
                'choices' => new LibraryTypes(),
                'required' => true,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('group', CheckBoxType::class, [
                'required' => false,
                'label' => 'Group by municipality',
            ])
            ->add('coordinates', CheckboxType::class, [
                'required' => false,
                'label' => 'Include coordinates',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Download',
            ])
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $form->setData([
                'types' => ['municipal'],
                'group' => true,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->export((object)$form->getData());
        }

        return [
            'form' => $form->createView(),
        ];
    }

    public function export(\stdClass $values)
    {
        $builder = $this->getDoctrine()->getManager()
            ->getRepository('App:Library')
            ->createQueryBuilder('e')
                ->select('e')
                ->andWhere('e.state = 1')
                ->andWhere('e.type IN (:types)')
                ->setParameter('types', $values->types)
                ->join('e.translations', 'ed', 'WITH', 'ed.langcode = \'fi\'')
                ->orderBy('ed.name')
                ;

        if ($values->group) {
            $builder
                ->join('e.city', 'c')
                ->join('c.translations', 'cd', 'WITH', 'cd.langcode = \'fi\'')
                ->orderBy('cd.name')
                ->addOrderBy('ed.name');
        }

        $libraries = $builder->getQuery()->getResult()
                ;

        $export = [];
        $lastCity = null;
        $lastModified = null;

        $headers = [
            'Nimi',
            'Sähköposti',
            'Katuosoite',
            'Postiosoite',
            'Koordinaatit',
        ];

        foreach ($libraries as $library) {
            $email = $library->getEmail();
            $lmod = $library->getModified();
            $lastModified = max($lastModified, $lmod);

            if ($values->group) {
                if ($lastCity != $library->getCity()) {
                    if ($lastCity) {
                        $export[] = [];
                    }
                    $lastCity = $library->getCity();
                    $export[][$headers[0]] = mb_strtoupper($lastCity->getName());
                }
            }

            $export[] = array_combine($headers, [
                $library->getName(),
                $email ? $email->getContact() : null,
                $this->formatAddress($library->getAddress()),
                $this->formatAddress($library->getMailAddress(), true),
                $library->getCoordinates(),
            ]);

            if (!$values->coordinates) {
                array_pop($export[count($export) - 1]);
            }
        }

        if (!$values->coordinates) {
            array_pop($headers);
        }

        if (!$lastModified) {
            $lastModified = new \DateTime();
        }

        $date = date('Y-m-d');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"Kirjastojen yhteystiedot {$date}.csv\"",
            'Last-Modified' => $lastModified->format('D, d M Y H:i:s \G\M\T'),
        ];

        $content = $this->serializer->encode($export, 'csv', [
            'csv_delimiter' => ';'
        ]);

        // Ugly MS Office hack.
        $content = utf8_decode($content);

        return new Response($content, 200, $headers);
    }

    private function formatAddress(?Address $address, $is_mail_address = false) : ?string
    {
        if (!$address) {
            return null;
        }
        if ($is_mail_address) {
            $street = $address->getStreet();
            $zipcode = $address->getZipcode();
            $city = mb_strtoupper($address->getArea());
            $postBox = $address->getBoxNumber();

            $formatted = "{$zipcode} {$city}";

            if ($postBox) {
                $formatted = "PL {$postBox}, $formatted";
            }

            if ($street) {
                $formatted = "{$street}, {$formatted}";
            }

            return $formatted;
        } else {
            $street = $address->getStreet();
            $zipcode = $address->getZipcode();
            $city = $address->getCity();

            $formatted = "{$street}, {$zipcode} {$city}";

            if ($area = $address->getArea()) {
                $formatted .= " ({$area})";
            }

            return $formatted;
        }
    }
}
