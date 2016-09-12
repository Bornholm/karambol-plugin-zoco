<?php

namespace KarambolZocoPlugin\Entity;

use Karambol\Entity\UserExtension;
use Doctrine\ORM\Mapping as ORM;
use KarambolZocoPlugin\Entity\Workgroup;
/**
 * @ORM\Entity
 * @ORM\Table(name="zoco_users_extensions")
 */
class ZocoUserExtension extends UserExtension {

  /**
   * @ORM\ManyToMany(targetEntity="KarambolZocoPlugin\Entity\Workgroup", inversedBy="users")
   * @ORM\JoinTable(name="users__users_workgroups")
   */
  protected $workgroups;

  public function __construct() {
      $this->workgroups = new \Doctrine\Common\Collections\ArrayCollection();
  }

  public function getWorkgroups()
  {
    return $this->workgroups;
  }

  /**
   * @param Workgroup $workgroup
   */
  public function addWorkgroup(Workgroup $workgroup)
  {
      if ($this->workgroups->contains($workgroup)) {
          return;
      }
      $this->workgroups->add($workgroup);
  }
  /**
   * @param Workgroup $workgroup
   */
  public function removeWorkgroup(Workgroup $workgroup)
  {
      if (!$this->workgroups->contains($workgroup)) {
          return;
      }
      $this->workgroups->removeElement($workgroup);
  }
}
