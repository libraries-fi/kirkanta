<?php

namespace App\Module\Translation;

use Doctrine\DBAL\Connection;

class TranslationManager
{
    private $db;
    private $queue;

    public function __construct(Connection $connection)
    {
        $this->db = $connection;
        $this->queue = [];
    }

    public function addMessage(array $message) : void
    {
        if (empty($message['id'])) {
            return;
        }

        $defaults = [
            'locale' => null,
            'domain' => 'messages',
            'id' => null,
            'translation' => null,
        ];

        $entry = array_intersect_key($message, $defaults) + $defaults;

        $key = sprintf('%s::%s::%s', $entry['locale'], $entry['domain'], $entry['id']);
        $this->queue[$key] = $entry;
    }

    public function addMessages(iterable $messages) : void
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
    }

    public function findMessages(array $search, int $limit = 0, int $from = 0) : array
    {
        $builder = $this->db->createQueryBuilder()
            ->select('t.locale', 't.domain', 't.id', 't.translation')
            ->from('translations', 't')
            ->orderBy('t.id')
            ->addOrderBy('t.domain')
            ;

        if ($limit > 0) {
            $builder->setMaxResults($limit);
        }

        if (!empty($search['locale'])) {
            $builder->andWhere('t.locale = :locale');
            $builder->setParameter('locale', $search['locale']);
        }

        if (!empty($search['domain'])) {
            $builder->andWhere('t.domain = :domain');
            $builder->setParameter('domain', $search['domain']);
        }

        if (!empty($search['text'])) {
            $builder->andWhere('(t.id LIKE :text OR t.translation LIKE :text)');
            $builder->setParameter('text', '%' . $search['text'] . '%');
        }

        if (!empty($search['only_null'])) {
            $builder->andWhere('t.translation IS NULL');
        }

        return $builder->execute()->fetchAll();
    }

    public function loadMessages($locale, ?string $domain = null, int $limit = 0, int $from = 0) : array
    {
        return $this->findMessages(['locale' => $locale, 'domain' => $domain], $limit, $from);
    }

    public function countMessages(string $locale, ?string $domain = null) : int
    {
        $builder = $this->db->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('translations', 't')
            ->where('t.locale = :locale')
            ->setParameter('locale', $locale);

        if ($domain) {
            $builder->andWhere('t.domain = :domain');
            $builder->setParameter('domain', $domain);
        }

        return $builder->execute()->fetchColumn();
    }

    public function flush() : void
    {
        if ($this->queue) {
            $this->db->beginTransaction();

            $statement = $this->db->prepare('
                INSERT INTO translations AS t
                    (locale, domain, id, translation)
                VALUES
                    (:locale, :domain, :id, :translation)
                ON CONFLICT
                    (locale, domain, id)
                DO UPDATE
                    SET
                        translation = coalesce(EXCLUDED.translation, t.translation)
            ');

            foreach ($this->queue as $entry) {
                $ok = $statement->execute($entry);
            }

            $this->db->commit();
            $this->queue = [];
        }
    }
}
