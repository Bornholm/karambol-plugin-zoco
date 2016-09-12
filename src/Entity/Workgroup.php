<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Karambol\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoco_workgroups")
 */
class Workgroup {

  /**
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="KarambolZocoPlugin\Entity\ZocoUserExtension")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE", nullable=false)
   */
  protected $user;

  /**
   * @ORM\Column(type="string", length=64, nullable=false)
   */
  protected $name;

  /**
   * @ORM\Column(type="string", length=64, nullable=false)
   */
  protected $slug;

  /**
   * @ORM\ManyToMany(targetEntity="Karambol\Entity\User", inversedBy="workgroups")
   */
  protected $users;

  public function __construct()
  {
    $this->users = new \Doctrine\Common\Collections\ArrayCollection();
  }

  public function getId() {
    return $this->id;
  }

  public function getUser() {
    return $this->user;
  }

  public function setUser(\KarambolZocoPlugin\Entity\ZocoUserExtension $user) {
    $this->user = $user;
  }

  /**
   * @param UserGroup $userGroup
   */
  public function addUser(Karambol\Entity\User $user)
  {
      if ($this->users->contains($user)) {
          return;
      }
      $this->users->add($user);
  }
  /**
   * @param UserGroup $userGroup
   */
  public function removeUser(Karambol\Entity\User $user)
  {
      if (!$this->users->contains($user)) {
          return;
      }
      $this->users->removeElement($user);
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setSlug($slug)
  {
    $this->slug = $slug;
  }

  public function getslug()
  {
    return $this->slug;
  }

}
