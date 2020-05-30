<?php

namespace App\Controller\API\Admin;

use App\Entity\Template;
use App\Repository\TemplateRepository;
use App\Util\TemplateVariableExtraction;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/api/admin/templates", name="templates")
 */
class TemplateController extends AbstractController
{
  /**
   * @Route("", methods={"GET"})
   * @param TemplateRepository $templateRepository
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function findAll(TemplateRepository $templateRepository, SerializerInterface $serializer)
  {
    $docs = $templateRepository->findAll();
    return JsonResponse::fromJsonString($serializer->serialize($docs, 'json'));
  }

  /**
   * @Route("/{id}", methods={"GET", "OPTIONS"})
   * @param string $id
   * @param TemplateRepository $templateRepository
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function find(string $id, TemplateRepository $templateRepository, SerializerInterface $serializer)
  {
    $doc = $templateRepository->find($id);
    return JsonResponse::fromJsonString($serializer->serialize($doc, 'json'));
  }


  /**
   * @Route("", methods={"POST"})
   * @param Request $request
   * @param SluggerInterface $slugger
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function create(Request $request, SluggerInterface $slugger, SerializerInterface $serializer)
  {
    // Check request.
    if ($request->request->has('name') == false || $request->files->has("templateFile") == false) {
      throw new HttpException(400, 'Missing request parameters.');
    }

    // Get File extension.
    $extension = $request->files->get('templateFile')->guessClientExtension();

    // Check if the file type is valid.
    if ($extension != 'html' && $extension != 'htm') {
      throw new HttpException(400, 'Invalid file type. Must be a .html or .htm file.');
    }

    // Extract vars.
    $name = $request->request->get('name');
    $templateFile = $request->files->get('templateFile');

    //This is needed to safely include the file name as part of the URL
    $safeFilename = $slugger->slug(pathinfo($templateFile->getClientOriginalName(), PATHINFO_FILENAME));
    $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

    // Move the file to the directory where brochures are stored
    try {
      $templateFile->move(
        $this->getParameter('templates_directory'),
        $newFilename
      );
    } catch (FileException $e) {
      throw new HttpException(500, "Failed to save file.");
    }

    $contentVars = TemplateVariableExtraction::Extract($this->getParameter('templates_directory') . '/' . $newFilename);

    // Save the template to the database.
    $entityManager = $this->getDoctrine()->getManager();
    $template = new Template();
    $template->setName($name);
    $template->setTemplateFileName($newFilename);
    $template->setContentVariables($contentVars);

    try {
      $entityManager->persist($template);
      $entityManager->flush();
    } catch (UniqueConstraintViolationException $e) {
      throw new HttpException(400, 'Unique error: field "name" with the value "' . $name . '" already exits');
    }

    return JsonResponse::fromJsonString($serializer->serialize($template, 'json'));
  }

  /**
   * @Route("/{id}", methods={"DELETE"})
   * @param string $id
   * @param TemplateRepository $templateRepository
   * @return JsonResponse
   */
  public function delete(string $id, TemplateRepository $templateRepository)
  {
    $doc = $templateRepository->find($id);

    if ($doc == null) {
      throw new HttpException(400, 'Document with the id "' . $id . '" not found');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($doc);
    $entityManager->flush();

    return new JsonResponse();
  }
}
