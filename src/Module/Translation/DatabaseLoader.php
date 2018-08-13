<?php

namespace App\Module\Translation;

use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseLoader implements LoaderInterface
{
    private $db;

    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    public function load($resource, $locale, $domain = 'messages') : MessageCatalogue
    {
        $statement = $this->db->prepare('SELECT source, message FROM translations WHERE locale = :locale AND domain = :domain');
        $statement->execute(['locale' => $locale, 'domain' => $domain]);
        $messages = [];

        foreach ($statement as $row) {
            $messages[$row['source']] = $row['message'];
        }

        $catalogue = new MessageCatalogue($locale);
        $catalogue->add($messages, $domain);
        return $catalogue;
    }
}
