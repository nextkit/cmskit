<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @ORM\Entity(repositoryClass="App\Repository\I18NRepository")
 * @ORM\Table("i18n")
 */
class I18N
{
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $language;

  /**
   * @ORM\Column(type="string", length=6)
   */
  private $langCountryCode;

  /**
   * @ORM\Column(type="boolean")
   */
  private $defaultLang = false;

  public function getId(): ?int
  {
      return $this->id;
  }

  public function getLanguage(): ?string
  {
      return $this->language;
  }

  public function setLanguage(string $language): self
  {
      $this->language = $language;

      return $this;
  }

  public function getLangCountryCode(): ?string
  {
      return $this->langCountryCode;
  }

  public function setLangCountryCode(string $langCountryCode): self
  {
      $this->langCountryCode = $langCountryCode;

      return $this;
  }

  public function getDefaultLang(): ?bool
  {
      return $this->defaultLang;
  }

  public function setDefaultLang(bool $defaultLang): self
  {
      $this->defaultLang = $defaultLang;

      return $this;
  }
}
