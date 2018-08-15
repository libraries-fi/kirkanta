<?php

namespace App\Module\Ptv;

use App\EntityTypeManager;
use App\Module\Ptv\Converter\Converter as ConverterInterface;
use App\Module\Ptv\Converter\LibraryConverter;
use App\Module\Ptv\Exception\AuthenticationException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
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

    /**
     * FIXME: Lazily passing LibraryConverter as an argument and letting Symfony do the initialization.
     */
    public function __construct(EntityTypeManager $types, LibraryConverter $library_converter, ParameterBagInterface $parameters)
    {
        $this->types = $types;

        // Auth tokens are valid for 24 hours so expire them a bit earlier.
        $this->cache = new ApcuCache('kirkanta.ptv', 3600 * 23);
        $this->http = new HttpClient(['base_uri' => $parameters->get('ptv.api')]);
        $this->converters[] = $library_converter;

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
            $ptv_id = $this->push($type, $document, $meta->getPtvIdentifier());

            if (!$meta->getPtvIdentifier()) {
                $meta->setPtvIdentifier($ptv_id);
            }
        }
    }


    public function push(string $type, array $document, ?string $id = null) : string
    {
        $token = $this->authenticate();

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ];

        try {
            if ($id) {
                $response = $this->http->request('PUT', "{$type}/{$id}", [
                    'headers' => $headers,
                    'json' => $document,
                ]);

                return json_decode((string)$response->getBody())->id;
            } else {
                $response = $this->http->request('POST', $type, [
                    'headers' => $headers,
                    'json' => $document,
                ]);

                $result = json_decode((string)$response->getBody());

                var_dump($result);
                exit;
            }
        } catch (ClientException $e) {
            header('Content-Type: application/json');
            $source = (string)$e->getResponse()->getBody();
            print(json_encode(json_decode($source), JSON_PRETTY_PRINT));
            exit;
        }
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
