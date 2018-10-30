<?php

namespace App\Module\Translation;

use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;

class TranslationManagerFlusher
{
    private $manager;
    private $collector;

    public function __construct(DataCollectorTranslator $collector, TranslationManager $manager)
    {
        $this->manager = $manager;
        $this->collector = $collector;
    }

    public function onKernelResponse($event) : void
    {
        foreach ($this->collector->getCollectedMessages() as $entry) {
            if ($entry['state'] != DataCollectorTranslator::MESSAGE_MISSING) {
                continue;
            }

            // Clear fallback message.
            $entry['translation'] = null;

            $this->manager->addMessage($entry);
        }

        $this->manager->flush();
    }

    public function onKernelTerminate($event) : void
    {
        // header('X-Samu: OK');
//
        // exit('stop');
        // $this->logger->flush();
    }
}
