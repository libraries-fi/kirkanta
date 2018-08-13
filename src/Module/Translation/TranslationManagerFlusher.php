<?php

namespace App\Module\Translation;

class TranslationManagerFlusher
{
    private $logger;

    public function __construct(MissingTranslationLogger $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelTerminate($event)
    {
        $this->logger->flush();
    }
}
