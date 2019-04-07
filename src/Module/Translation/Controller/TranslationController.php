<?php

namespace App\Module\Translation\Controller;

use App\Module\Translation\Form\SearchForm;
use App\Module\Translation\Form\TranslationForm;
use App\Module\Translation\TranslationManager;
use App\Util\FormData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

class TranslationController extends Controller
{
    /**
     * @Route("/admin/translation", name="translation.manage")
     * @Template("translation.html.twig")
     */
    public function editAction(Request $request, TranslationManager $manager)
    {
        $method = $request->getMethod();
        $request->setMethod('GET');
        $search = $this->createForm(SearchForm::class, ['locale' => 'fi']);
        $search->handleRequest($request);

        $data = $manager->findMessages($search->getData(), $request->query->getInt('from', 0));

        $request->setMethod($method);
        $form = $this->createForm(TranslationForm::class, ['translations' => $data, 'search' => $search->getData()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messages = $form->get('translations')->getData();
            $manager->addMessages($messages);
            $manager->flush();

            $this->reloadCache();
            $this->addFlash('success', 'Translations saved.');
            return $this->redirectToRoute('translation.manage', $request->query->all());
        }

        return [
            'form' => $form->createView(),
            'search_form' => $search->createView(),
        ];
    }

    private function findMessages(array $search, int $from, int $limit = 25) : array
    {
        $manager = $this->get('translation_manager');
        return $manager->findMessages($search, $limit, $from);
    }

    private function reloadCache()
    {
        $root = $this->getParameter('kernel.cache_dir');
        $dirs = glob("{$root}/../*/translations/*");
        array_map('unlink', $dirs);
    }
}
