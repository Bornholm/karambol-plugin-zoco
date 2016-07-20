<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="boamp")
 */
class BoampEntry {

  /**
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @ORM\Column(type="string", length=20, unique=true)
   */
  public $webId;

  /**
   * @ORM\Column(type="text")
   */
  public $xmlFile;

  public function getId() {
    return $this->id;
  }

}
