<?php

namespace App\EventListener;

use App\Util\SystemLanguages;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SessionLanguageSubscriber implements EventSubscriberInterface
{
    const SESSION_KEY = 'uilang';

    private $languages;

    public function __construct(SystemLanguages $languages)
    {
        $this->languages = $languages;
        $this->session = new Session();
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
            SecurityEvents::INTERACTIVE_LOGIN => [['onSecurityInteractiveLogin']]
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) : void
    {
        $langcode = $event->getRequest()->request->get('lang');
        $this->setLanguage($langcode);
    }

    public function onKernelRequest(GetResponseEvent $event) : void
    {
        $event->getRequest()->setLocale($this->getLanguage());
    }

    private function setLanguage(string $langcode) : void
    {
        if ($this->languages->search($langcode) === false) {
            $langcode = SystemLanguages::DEFAULT_LANGCODE;
        }
        $this->session->set(self::SESSION_KEY, $langcode);
    }

    private function getLanguage() : string
    {
        return $this->session->get(self::SESSION_KEY) ?? SystemLanguages::DEFAULT_LANGCODE;
    }
}
