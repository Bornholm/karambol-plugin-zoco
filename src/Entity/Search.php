<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Karambol\Account\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoco_searches")
 */
class Search {

  /**
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @var string
   */
  protected $search;

  /**
   * @ORM\ManyToOne(targetEntity="Karambol\Account\UserInterface")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", orphanRemoval=true, nullable=false)
   */
  protected $user;

  public function getId() {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getSearch()
  {
    return $this->search;
  }

  /**
   * @param string $search
   *
   * @return static
   */
  public function setSearch($search)
  {
    $this->search = $search;
    return $this;
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

  public function getElasticsearchQuery() {

    $body = [];

    $body['filter'] = [
      'and' => [
        [ 'exists' => [ 'field' => 'main' ] ]
      ]
    ];

    $search = $this->getSearch();

    if(!empty($search)) {
      $query = [
        'multi_match' => [
          'fields' => [
            '*.GESTION.REFERENCE.IDWEB',
            '*.GESTION.INDEXATION.RESUME_OBJET',
            '*.DONNEES.IDENTITE.*',
            '*.DONNEES.OBJET.TITRE_MARCHE',
            '*.DONNEES.OBJET.OBJET_COMPLET',
            '*.DONNEES.OBJET.LOTS.LOT.INTITULE',
            '*.DONNEES.OBJET.LOTS.LOT.DESCRIPTION',
            '*.DONNEES.OBJET.LOTS.DESCRIPTION',
            '*.DONNEES.OBJET.LOTS.INTITULE'
          ],
          'query' => $search,
          'operator' => 'AND',
          'type' => 'cross_fields'
        ]
      ];
      $body['query'] = $query;
    }

    return ['body' => $body];

  }


}
