<?php

namespace App\Menu\Matcher;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Matches the item with longest sub-URI match among its siblings.
 */
class PathTraceVoter implements VoterInterface
{
    private $requests;

    public function __construct(RequestStack $requests)
    {
        $this->requests = $requests;
    }

    public function matchItem(ItemInterface $item) : bool
    {
        $current_path = $this->requests->getCurrentRequest()->getPathInfo();

        $siblings = $item->getParent()->getChildren();
        $matching = array_filter($siblings, function ($item) use ($current_path) {
            // Appending item URI with a slash ensures only full names match.
            // Appending $current_path with a slash allows to match the exact URI, too.
            return strpos($current_path . '/', $item->getUri() . '/') === 0;
        });

        if (!empty($matching)) {
            usort($matching, function ($a, $b) {
                return strlen($b->getUri()) - strlen($a->getUri());
            });

            return $item == reset($matching);
        }

        return false;
    }
}
