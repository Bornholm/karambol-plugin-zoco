<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

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
   * @var \DateTime
   */
  protected $after;

  /**
   * @var \DateTime
   */
  protected $before;

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
   * @return \DateTime
   */
  public function getBefore()
  {
    return $this->before;
  }

  /**
   * @param \DateTime $before
   *
   * @return static
   */
  public function setBefore($before)
  {
    $this->before = $before;
    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getAfter()
  {
    return $this->after;
  }

  /**
   * @param \DateTime $before
   *
   * @return static
   */
  public function setAfter($after)
  {
    $this->after = $after;
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

    $body = [
      'query' => [
        'filtered' => [
          'filter' => [
            'and' => [
              [ 'exists' => [ 'field' => 'main' ] ]
            ]
          ],
          'query' => [
            'bool' => [
              'must' => []
            ]
          ]
        ]
      ]
    ];
    $filterAnd = &$body['query']['filtered']['filter']['and'];
    $queryMust = &$body['query']['filtered']['query']['bool']['must'];

    $search = $this->getSearch();

    if(!empty($search)) {
      $queryMust[] = [ 'multi_match' => [
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
      ]];
    }

    $before = $this->getBefore();
    if($before !== null) {
      $filterAnd[] = [
        'range' => [
          'main.GESTION.INDEXATION.DATE_PUBLICATION'  => [
            'lt' => $before->format('Y-m-d')
          ]
        ]
      ];
    }

    $after = $this->getAfter();
    if($after !== null) {
      $filterAnd[] = [
        'range' => [
          'main.GESTION.INDEXATION.DATE_PUBLICATION'  => [
            'gt' => $after->format('Y-m-d')
          ]
        ]
      ];
    }

    return ['body' => $body];

  }


}
