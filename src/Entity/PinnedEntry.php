<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoco_pinned_entries")
 */
class PinnedEntry {

  /**
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @ORM\Column(name="entryId", type="text", length=64, nullable=false)
   */
  protected $entryId;

  /**
   * @ORM\Column(name="entryType", type="text", length=64, nullable=false)
   */
  protected $entryType;

  /**
   * @ORM\Column(name="userId", type="integer", nullable=false)
   */
  protected $userId;

  /**
   * @return
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return
   */
  public function getEntryId()
  {
    return $this->entryId;
  }

  /**
   * @param  $entryId
   *
   * @return static
   */
  public function setEntryId($entryId)
  {
    $this->entryId = $entryId;
    return $this;
  }

  /**
   * @return
   */
  public function getEntryType()
  {
    return $this->entryType;
  }

  /**
   * @param  $entryType
   *
   * @return static
   */
  public function setEntryType($entryType)
  {
    $this->entryType = $entryType;
    return $this;
  }

  /**
   * @return
   */
  public function getUserId()
  {
    return $this->userId;
  }

  /**
   * @param  $user
   *
   * @return static
   */
  public function setUserId($userId)
  {
    $this->userId = $userId;
    return $this;
  }

}
