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
   * @ORM\Column(type="string", length=64, nullable=false)
   */
  protected $tenderId;

  /**
   * @ORM\Column(type="string", length=64, nullable=false)
   */
  protected $tenderType;

  /**
   * @ORM\ManyToOne(targetEntity="Karambol\Account\UserInterface")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE", nullable=false)
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

  /**
   * @return
   */
  public function getTenderId()
  {
    return $this->tenderId;
  }

  /**
   * @param  $tenderId
   *
   * @return static
   */
  public function setTenderId($tenderId)
  {
    $this->tenderId = $tenderId;
    return $this;
  }

  /**
   * @return
   */
  public function getTenderType()
  {
    return $this->tenderType;
  }

  /**
   * @param  $tenderType
   *
   * @return static
   */
  public function setTenderType($tenderType)
  {
    $this->tenderType = $tenderType;
    return $this;
  }

}
