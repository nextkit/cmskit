<?php

namespace App\Controller\API\Admin;

use App\Entity\Page;
use App\Repository\PageRepository;
use App\Repository\TemplateRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/admin/pages", name="pages")
 */
class PageController extends AbstractController
{
  /**
   * @Route("", methods={"GET"})
   * @param PageRepository $pageRepository
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function findAll(PageRepository $pageRepository, SerializerInterface $serializer)
  {
    $docs = $pageRepository->findAll();
    return JsonResponse::fromJsonString($serializer->serialize($docs, 'json'));
  }

  /**
   * @Route("/{id}", methods={"GET", "OPTIONS"})
   * @param string $id
   * @param PageRepository $pageRepository
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function find(string $id, PageRepository $pageRepository, SerializerInterface $serializer)
  {
    $doc = $pageRepository->find($id);
    return JsonResponse::fromJsonString($serializer->serialize($doc, 'json'));
  }

  /**
   * @Route("", methods={"POST"})
   * @param Request $request
   * @param TemplateRepository $templateRepository
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function create(Request $request, TemplateRepository $templateRepository, SerializerInterface $serializer)
  {
    $data = json_decode($request->getContent(), true);

    // Check request.
    if (isset($data['title']) == false || isset($data['uri']) == false || isset($data['template']) == false) {
      throw new HttpException(400, 'Missing request parameters.');
    }

    $template = $templateRepository->find($data['template']);

    if ($template == null) {
      throw new HttpException(400, 'No template found with the id "' . $data['templateId'] . '"');
    }

    $content = [];
    $uri = strtolower($data['uri']);

    // Remove slash at front.
    if (substr($uri, 0, 1) == '/') {
      $uri = substr($uri, 1, strlen($uri) - 1);
    }
    // Remove slash at the back.
    if (substr($uri, strlen($uri) - 1, 1) == '/') {
      $uri = substr($uri, 0, strlen($uri) - 1);
    }

    if (isset($data['content'])) {
      // Create content from template vars.
      foreach ($data['content'] as $varKey => $value) {
        foreach ($template->getContentVariables() as $contentVarKey => $contentValue) {
          if ($varKey == $contentVarKey && gettype($value) == $contentValue['type']) {
            $content[$contentVarKey] = $value;
            break;
          }
        }
      }
    } else {
      // Else set everything empty.
      foreach ($template->getContentVariables() as $contentVarKey => $contentValue) {
        switch ($contentValue['type']) {
          case 'array':
            $content[$contentVarKey] = [];
            break;
          case 'string':
          default:
            $content[$contentVarKey] = '';
            break;
        }
      }
    }

    if (count($template->getContentVariables()) != count($content)) {
      throw new HttpException(400, 'Not all variables defined needed in the template: "' . $serializer->serialize($content, 'json') . '" -> "' . $serializer->serialize($template->getContentVariables(), 'json') . '"');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $page = new Page();
    $page->setTitle($data['title']);
    $page->setUri($uri);
    $page->setContent($content);
    $page->setTemplate($template);

    try {
      $entityManager->persist($page);
      $entityManager->flush();
    } catch (UniqueConstraintViolationException $e) {
      throw new HttpException(400, 'Unique error: field "uri" with the value "' . $uri . '" already exits');
    }

    return JsonResponse::fromJsonString($serializer->serialize($page, 'json'));
  }

  /**
   * @Route("/{id}", methods={"DELETE"})
   * @param string $id
   * @param PageRepository $pageRepository
   * @return JsonResponse
   */
  public function delete(string $id, PageRepository $pageRepository)
  {
    $doc = $pageRepository->find($id);

    if ($doc == null) {
      throw new HttpException(400, 'Document with the id "' . $id . '" not found');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($doc);
    $entityManager->flush();

    return new JsonResponse();
  }

  /**
   * @Route("/{id}", methods={"PUT"})
   * @param string $id
   * @param Request $request
   * @param PageRepository $pageRepository
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function update(string $id, Request $request, PageRepository $pageRepository, SerializerInterface $serializer)
  {
    $data = json_decode($request->getContent(), true);

    // Check request.
    if (isset($data['content']) == false) {
      throw new HttpException(400, 'Missing request parameters.');
    }

    $page = $pageRepository->find($id);

    if ($page == null) {
      throw new HttpException(400, 'Document with the id "' . $id . '" not found');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $page->setContent($data['content']);
    $entityManager->flush();

    return JsonResponse::fromJsonString($serializer->serialize($page, 'json'));
  }
}
