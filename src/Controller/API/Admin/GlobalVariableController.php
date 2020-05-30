<?php

namespace App\Controller\API\Admin;

use App\Entity\GlobalVariable;
use App\Repository\GlobalVariableRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/api/admin/global-variables", name="global-variables")
 */
class GlobalVariableController extends AbstractController
{
  /**
   * @Route("", methods={"GET"})
   * @param GlobalVariableRepository $globalVariablesRepo
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function findAll(GlobalVariableRepository $globalVariablesRepo, SerializerInterface $serializer)
  {
    $docs = $globalVariablesRepo->findAll();
    return JsonResponse::fromJsonString($serializer->serialize($docs, 'json'));
  }

  /**
   * @Route("/{id}", methods={"GET", "OPTIONS"})
   * @param string $id
   * @param GlobalVariableRepository $globalVariablesRepo
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function find(string $id, GlobalVariableRepository $globalVariablesRepo, SerializerInterface $serializer)
  {
    $doc = $globalVariablesRepo->find($id);
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
    $data = json_decode($request->getContent(), true);

    // Check request.
    if (isset($data['name']) == false || isset($data['key']) == false || isset($data['value']) == false) {
      throw new HttpException(400, 'Missing request parameters.');
    }

    // Save the template to the database.
    $entityManager = $this->getDoctrine()->getManager();
    $globalVariable = new GlobalVariable();
    $globalVariable->setName($data['name']);
    $globalVariable->setKey($data['key']);
    $globalVariable->setValue($data['value']);

    try {
      $entityManager->persist($globalVariable);
      $entityManager->flush();
    } catch (UniqueConstraintViolationException $e) {
      throw new HttpException(400, 'Unique error: field "name" or "key" with the value "' . $data['name'] . '" and "' . $data['key'] . '" already exits');
    }

    return JsonResponse::fromJsonString($serializer->serialize($globalVariable, 'json'));
  }

  /**
   * @Route("/{id}", methods={"DELETE"})
   * @param string $id
   * @param GlobalVariableRepository $globalVariablesRepo
   * @return JsonResponse
   */
  public function delete(string $id, GlobalVariableRepository $globalVariablesRepo)
  {
    $doc = $globalVariablesRepo->find($id);

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
   * @param GlobalVariableRepository $globalVariablesRepo
   * @param SerializerInterface $serializer
   * @return JsonResponse
   */
  public function update(string $id, Request $request, GlobalVariableRepository $globalVariablesRepo, SerializerInterface $serializer)
  {
    $data = json_decode($request->getContent(), true);

    // Check request.
    if (isset($data['value']) == false) {
      throw new HttpException(400, 'Missing request parameters.');
    }

    $globalVariable = $globalVariablesRepo->find($id);

    if ($globalVariable == null) {
      throw new HttpException(400, 'Document with the id "' . $id . '" not found');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $globalVariable->setValue($data['value']);
    $entityManager->flush();

    return JsonResponse::fromJsonString($serializer->serialize($globalVariable, 'json'));
  }
}
