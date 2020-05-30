<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TemplateRepository")
 */
class Template
{
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255, unique=true)
   */
  private $name;

  /**
   * @ORM\Column(type="json")
   */
  private $contentVariables = [];

  /**
   * @ORM\Column(type="string", length=255, unique=true)
   */
  private $templateFileName;

  public function getId(): ?int
  {
      return $this->id;
  }

  public function getName(): ?string
  {
      return $this->name;
  }

  public function setName(string $name): self
  {
      $this->name = $name;

      return $this;
  }

  public function getContentVariables(): ?array
  {
      return $this->contentVariables;
  }

  public function setContentVariables(array $contentVariables): self
  {
      $this->contentVariables = $contentVariables;

      return $this;
  }

  public function getTemplateFileName(): ?string
  {
      return $this->templateFileName;
  }

  public function setTemplateFileName(string $templateFileName): self
  {
      $this->templateFileName = $templateFileName;

      return $this;
  }
}
