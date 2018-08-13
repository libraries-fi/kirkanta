<?php

namespace App\Module\Ptv;

use App\EntityTypeManager;
use App\Module\Ptv\Converter\Converter as ConverterInterface;
use App\Module\Ptv\Converter\LibraryConverter;
use App\Module\Ptv\Exception\AuthenticationException;
use App\Module\Schedules\ScheduleManager;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Client
{
    private $types;
    private $cache;
    private $http;
    private $converters = [];

    private $username;
    private $password;
    private $authUrl;

    public function __construct(EntityTypeManager $types, ScheduleManager $schedules, ParameterBagInterface $parameters)
    {
        $this->types = $types;

        // Auth tokens are valid for 24 hours so expire them a bit earlier.
        $this->cache = new ApcuCache('kirkanta.ptv', 3600 * 23);
        $this->http = new HttpClient(['base_uri' => $parameters->get('ptv.api')]);
        $this->converters[] = new Converter\LibraryConverter($schedules);

        $this->username = $parameters->get('ptv.username');
        $this->password = $parameters->get('ptv.password');
        $this->authUrl = $parameters->get('ptv.auth_url');
    }

    public function isSupported($entity) : bool
    {
        try {
            $this->getConverter();
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    public function store($entity) : void
    {
        $type_id = $this->types->getTypeId(get_class($entity));

        $meta = $this->types->getRepository('ptv_data')->findOneBy([
            'entity_type' => 'library',
            'entity_id' => $entity->getId(),
        ]);

        if ($meta && $meta->isEnabled()) {
            $converter = $this->getConverter($entity);
            $document = $converter->convert($entity);
            $type = $converter->getDocumentType($entity);
            $this->push($type, $document);
        }
    }


    public function push(string $type, array $document) : void
    {
        $token = $this->authenticate();

        header('Content-Type: text/plain; charset=utf-8');
        print json_encode($document, JSON_PRETTY_PRINT);
        exit('send data to PTV');
    }

    private function authenticate() : string
    {
        if ($token = $this->cache->get('access_token')) {
            return $token;
        } else {
            if (!$this->username || !$this->password) {
                throw new RuntimeException('You must configure username and password in order to use the PTV API');
            }

            $data = [
                'form_params' => [
                    'grant_type' => 'password',
                    'scope' => 'dataEventRecords openid',
                    'client_id' => 'ptv_api_client',
                    'client_secret' => 'openapi',
                    'username' => $this->username,
                    'password' => $this->password
                ]
            ];

            try {
                $response = $this->http->request('POST', $this->authUrl, $data);

                if ($response->getStatusCode() == 200) {
                    $result = json_decode($response->getBody());
                    $token = $result->access_token;
                    $this->cache->set('access_token', $token);

                    return $token;
                }
            } catch (RequestException $e) {
                throw new AuthenticationException($e);
            }

            throw new AuthenticationException;
        }
    }

    private function getConverter($entity) : ConverterInterface
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($entity)) {
                return $converter;
            }
        }

        $class_name = get_class($entity);
        throw new InvalidArgumentException("No converter accepts '{$class_name}'");
    }
}
