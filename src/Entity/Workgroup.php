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
   * @ORM\ManyToMany(targetEntity="\KarambolZocoPlugin\Entity\ZocoUserExtension", inversedBy="workgroups")
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

  public function getUsers()
  {
    return $this->users;
  }

  /**
   * @param UserGroup $userGroup
   */
  public function addUser(\KarambolZocoPlugin\Entity\ZocoUserExtension $user)
  {
      if ($this->users->contains($user)) {
          return;
      }
      $this->users->add($user);
  }
  /**
   * @param UserGroup $userGroup
   */
  public function removeUser(\KarambolZocoPlugin\Entity\ZocoUserExtension $user)
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
    $this->slug = $this->sluggable($slug);
  }

  public function getslug()
  {
    return $this->slug;
  }

  protected function sluggable($string)
  {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
  }

}
