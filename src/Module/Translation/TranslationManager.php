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

    public function addMessage(string $locale, string $domain, string $id, string $translation = null)
    {
        $this->queue[] = [
            'locale' => $locale,
            'domain' => $domain,
            'source' => $id,
            'message' => $translation,
        ];
    }

    public function addMessages(iterable $messages) : void
    {
        foreach ($messages as $entry) {
            extract($entry);

            if (!isset($locale)) {
                var_dump($entry);
                exit;
            }

            $this->addMessage($locale, $domain, $source, $message);
        }
    }

    public function findMessages(array $search, int $limit = 0, int $from = 0) : array
    {
        $builder = $this->db->createQueryBuilder()
            ->select('t.locale', 't.domain', 't.source', 't.message')
            ->from('translations', 't')
            ->orderBy('t.source')
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
            $builder->andWhere('(t.source LIKE :text OR t.message LIKE :text)');
            $builder->setParameter('text', '%' . $search['text'] . '%');
        }

        if (!empty($search['only_null'])) {
            $builder->andWhere('t.message IS NULL');
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

    public function flush()
    {
        if ($this->queue) {
            $this->db->beginTransaction();

            $statement = $this->db->prepare('
                INSERT INTO translations AS t
                    (locale, domain, source, message)
                VALUES
                    (:locale, :domain, :source, :message)
                ON CONFLICT
                    (locale, domain, source)
                DO UPDATE
                    SET
                        message = coalesce(EXCLUDED.message, t.message)
            ');

            foreach ($this->queue as $entry) {
                if (empty($entry['source'])) {
                    continue;
                }

                if (ctype_digit($entry['source'])) {
                    continue;
                }

                $statement->execute($entry);
            }

            $this->db->commit();
            $this->queue = [];
        }
    }
}
