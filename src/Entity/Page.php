<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 * @ORM\Table(name="page", indexes={@ORM\Index(name="page_uri_idx", columns={"uri"})})
 */
class Page
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
  private $title;

  /**
   * @ORM\Column(type="string", length=512, unique=true)
   */
  private $uri;

  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\Template")
   * @ORM\JoinColumn(nullable=false)
   */
  private $template;

  /**
   * @ORM\Column(type="json")
   */
  private $content = [];

  public function getId(): ?int
  {
      return $this->id;
  }

  public function getTitle(): ?string
  {
      return $this->title;
  }

  public function setTitle(string $title): self
  {
      $this->title = $title;

      return $this;
  }

  public function getUri(): ?string
  {
      return $this->uri;
  }

  public function setUri(string $uri): self
  {
      $this->uri = $uri;

      return $this;
  }

  public function getTemplate(): ?Template
  {
      return $this->template;
  }

  public function setTemplate(?Template $template): self
  {
      $this->template = $template;

      return $this;
  }

  public function getContent(): ?array
  {
      return $this->content;
  }

  public function setContent(array $content): self
  {
      $this->content = $content;

      return $this;
  }
}
