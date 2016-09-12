<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Karambol\Account\UserInterface;

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
   * @ORM\ManyToOne(targetEntity="Karambol\Account\UserInterface")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
   */
  protected $userID;

  /**
   * @ORM\Column(type="string", length=64, nullable=false)
   */
  protected $name;

  /**
   * @ORM\Column(type="string", length=64, nullable=false)
   */
  protected $slug;

  /**
   * @ORM\ManyToMany(targetEntity="Karambol\Account\UserInterface", inversedBy="users")
   * @ORM\JoinTable(
   *  name="zoco_users_workgroups",
   *  joinColumns={
   *      @ORM\JoinColumn(name="user_id", referencedColumnName="id")
   *  },
   *  inverseJoinColumns={
   *      @ORM\JoinColumn(name="workgroup_id", referencedColumnName="id")
   *  }
   * )
   */
  protected $users;

  public function __construct()
  {
    $this->users = new \ArrayCollection();
  }

  public function getId() {
    return $this->id;
  }

  /**
   * @param UserGroup $userGroup
   */
  public function addUser(UserInterface $user)
  {
      if ($this->users->contains($user)) {
          return;
      }
      $this->users->add($user);
  }
  /**
   * @param UserGroup $userGroup
   */
  public function removeUser(UserInterface $user)
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
