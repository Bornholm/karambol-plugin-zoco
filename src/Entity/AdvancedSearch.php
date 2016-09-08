<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Karambol\Account\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoco_tender_pins")
 */
class TenderPin {

  /**
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="Karambol\Account\UserInterface")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", orphanRemoval=true, nullable=false)
   */
  protected $user;

  public function getId() {
    return $this->id;
  }

  /**
   * @return Karambol\Account\UserInterface
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @param  $user
   *
   * @return static
   */
  public function setUser(UserInterface $user)
  {
    $this->user = $user;
    return $this;
  }

  

}
