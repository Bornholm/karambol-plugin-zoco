<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use KarambolZocoPlugin\Entity\ZocoUserExtension;
use KarambolZocoPlugin\Entity\Workgroup;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoco_tender_workgroups")
 */
class TenderWorkgroup {

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
   * @ORM\ManyToOne(targetEntity="\KarambolZocoPlugin\Entity\ZocoUserExtension")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE", nullable=false)
   */
  protected $user;

  /**
   * @ORM\ManyToOne(targetEntity="KarambolZocoPlugin\Entity\Workgroup")
   * @ORM\JoinColumn(name="workgroup", referencedColumnName="id", onDelete="CASCADE", nullable=false)
   */
  protected $workgroup;


  public function getId() {
    return $this->id;
  }

  /**
   * @return Karambol\Entity\User
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
  public function setUser(ZocoUserExtension $user)
  {
    $this->user = $user;
    return $this;
  }

  public function getWorkgroup()
  {
    return $this->workgroup;
  }

  public function setWorkgroup(Workgroup $workgroup)
  {
    $this->workgroup = $workgroup;
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
