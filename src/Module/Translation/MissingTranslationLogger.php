<?php

namespace App\Module\Translation;

use Symfony\Component\Translation\TranslatorBagInterface;

class MissingTranslationLogger
{
    private $manager;
    private $bag;
    private $log;

    public function __construct(TranslationManager $manager, TranslatorBagInterface $bag)
    {
        $this->manager = $manager;
        $this->bag = $bag;
        $this->log = [];
    }

    public function log(string $id, ?string $locale, ?string $domain) : void
    {
        $this->log[] = [
            'locale' => $locale,
            'domain' => $domain,
            'source' => $id,
        ];
    }

    public function flush() : void
    {
        foreach ($this->log as $entry) {
            extract($entry);
            $catalogue = $this->bag->getCatalogue($locale);

            if (is_null($domain)) {
                $domain = 'messages';
            }

            if (!$catalogue->defines($source, $domain)) {
                $this->manager->addMessage($catalogue->getLocale(), $domain, $source);
            }
        }
        $this->manager->flush();
        $this->log = [];
    }
}
