<?php

namespace App\Controller\API\Admin;

use App\Entity\I18N;
use App\Repository\I18NRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/api/admin/i18n", name="i18n")
 */
class I18NController extends AbstractController
{
  /**
   * @Route("", methods={"GET"})
   * @param I18NRepository $i18NRepository
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function findAll(I18NRepository $i18NRepository, SerializerInterface $serializer)
  {
    $docs = $i18NRepository->findBy([], ['language' => 'ASC']);
    return JsonResponse::fromJsonString($serializer->serialize($docs, 'json'));
  }

  /**
   * @Route("/{id}", methods={"GET", "OPTIONS"})
   * @param string $id
   * @param I18NRepository $i18NRepository
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function find(string $id, I18NRepository $i18NRepository, SerializerInterface $serializer)
  {
    $doc = $i18NRepository->find($id);
    return JsonResponse::fromJsonString($serializer->serialize($doc, 'json'));
  }

  /**
   * @Route("", methods={"POST"})
   * @param Request $request
   * @param SluggerInterface $slugger
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function create(Request $request, SluggerInterface $slugger, SerializerInterface $serializer, I18NRepository $i18NRepository)
  {
    $data = json_decode($request->getContent(), true);

    // Check request.
    if (is_array($data) == false) {
      throw new HttpException(400, 'Missing request parameters.');
    }

    $setDefaultLang = false;
    if ($i18NRepository->count([]) === 0) {
      $setDefaultLang = true;
    }

    $entityManager = $this->getDoctrine()->getManager();

    foreach ($data as $dataset) {
      $i18n = new I18N();
      $i18n->setLanguage($dataset['language']);
      $i18n->setLangCountryCode($dataset['langCountryCode']);
      if ($setDefaultLang) {
        $i18n->setDefaultLang(true);
        $setDefaultLang = false;
      }
      $entityManager->persist($i18n);
    }

    try {
      $entityManager->flush();
    } catch (UniqueConstraintViolationException $e) {
      throw new HttpException(500, "Failed to save file.");
    }

    return new JsonResponse([]);
  }

  /**
   * @Route("/{id}", methods={"DELETE"})
   * @param string $id
   * @param I18NRepository $i18NRepository
   * @return JsonResponse
   */
  public function delete(string $id, I18NRepository $i18NRepository)
  {
    $doc = $i18NRepository->find($id);
    if ($doc == null) {
      throw new HttpException(400, 'Document with the id "' . $id . '" not found');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($doc);
    $entityManager->flush();

    return new JsonResponse();
  }

  /**
   * @Route("/{id}", methods={"POST"})
   * @param string $id
   * @return JsonResponse
   */
  public function setDefaultLang(string $id, SerializerInterface $serializer, I18NRepository $i18NRepository)
  {
    $doc = $i18NRepository->findOneByDefault();
    $entityManager = $this->getDoctrine()->getManager();

    if ($doc != null) {
      $doc->setDefaultLang(false);
    }

    $newDefaultDoc = $i18NRepository->findOneBy(['id' => $id]);
    $newDefaultDoc->setDefaultLang(true);
    $entityManager->flush();

    return JsonResponse::fromJsonString($serializer->serialize($newDefaultDoc, 'json'));
  }
}
