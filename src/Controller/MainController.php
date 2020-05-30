<?php

namespace App\Controller;

use App\Repository\I18NRepository;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

  /**
   * @Route("/{path}", name="router", requirements={"path" = ".+"})
   * @param string $path
   * @param Request $request
   * @param PageRepository $pageRepository
   * @return JsonResponse
   */
  public function handle(string $path, Request $request, PageRepository $pageRepository, I18NRepository $i18nRepository)
  {
    $i18n = $i18nRepository->findAll();
    $replaceQuery = [];
    foreach ($i18n as $t) {
      $replaceQuery[] = $t->getLangCountryCode()."/";
    }

    $path = str_replace($replaceQuery, "", $path);
    $page = $pageRepository->findOneBy(['uri' => $path]);

    if ($page) {
      return $this->render('uploads/' . $page->getTemplate()->getTemplateFileName(), $page->getContent());
    }

    // TODO: return 404 page.
    return new JsonResponse(['error' => 'Page Not Found'], 404);
  }
}
