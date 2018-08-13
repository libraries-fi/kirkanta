<?php

namespace App\Module\Translation;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\Translator as BaseTranslator;

class Translator implements TranslatorBagInterface, TranslatorInterface
{
    private $translator;
    private $logger;
    private $locales = ['fi', 'sv'];

    public function __construct(TranslatorInterface $translator, MissingTranslationLogger $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $message = $this->translator->trans($id, $parameters, $domain, $locale);

        if ($message == $id) {
            $this->log($id, $domain, $locale);
        }

        return $message;
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $message = $this->translator->transChoice($id, $number, $parameters, $domain, $locale);

        if ($message == $id) {
            $this->log($id, $domain, $locale);
        }

        return $message;
    }

    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale()
    {
        return $this->translator->getLocale();
    }

    public function getCatalogue($locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }

    private function log(string $id, ?string $domain, ?string $locale)
    {
        // $this->logger->log($id, $locale, $domain);

        // Log for every locale so that they can be found in the translate tool.
        foreach ($this->locales as $locale) {
            $this->logger->log($id, $locale, $domain);
        }
    }
}
