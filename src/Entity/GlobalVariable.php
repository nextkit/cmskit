<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GlobalVariableRepository")
 */
class GlobalVariable
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
   * @ORM\Column(name="variable_key", type="string", length=255, unique=true)
   */
  private $key;

  /**
   * @ORM\Column(type="string", length=1024)
   */
  private $value;

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

  public function getKey(): ?string
  {
      return $this->key;
  }

  public function setKey(string $key): self
  {
      $this->key = $key;

      return $this;
  }

  public function getValue(): ?string
  {
      return $this->value;
  }

  public function setValue(string $value): self
  {
      $this->value = $value;

      return $this;
  }
}
