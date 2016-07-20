<?php

namespace DashboardPlugin\Entity;

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

  public function getId() {
    return $this->id;
  }

  public function getBoampId() {
    return $this->id;
  }

}
