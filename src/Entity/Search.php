<?php

namespace KarambolZocoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Karambol\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="zoco_searches")
 */
class Search {

  const STATUS_NONE = 0;
  const STATUS_OPENED = 1;
  const STATUS_CLOSED = 2;
  const STATUS_BOTH = 3;

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
   * @ORM\Column(type="date")
   */
  protected $publishedAfter;

  /**
   * @var \DateTime
   * @ORM\Column(type="date")
   */
  protected $publishedBefore;

  /**
   * @var int
   * @ORM\Column(type="integer")
   */
  protected $status = self::STATUS_OPENED;

  /**
   * @ORM\ManyToOne(targetEntity="Karambol\Entity\User")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
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
  public function getPublishedBefore()
  {
    return $this->publishedBefore;
  }

  /**
   * @param \DateTime $publishedBefore
   *
   * @return static
   */
  public function setPublishedBefore($publishedBefore)
  {
    $this->publishedBefore = $publishedBefore;
    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getPublishedAfter()
  {
    return $this->publishedAfter;
  }

  /**
   * @param \DateTime $before
   *
   * @return static
   */
  public function setPublishedAfter($publishedAfter)
  {
    $this->publishedAfter = $publishedAfter;
    return $this;
  }

  /**
   * @return array
   */
  public function getStatus()
  {
    switch($this->status) {
      case self::STATUS_OPENED:
        return [self::STATUS_OPENED];
      case self::STATUS_CLOSED:
        return [self::STATUS_CLOSED];
      case self::STATUS_BOTH:
        return [self::STATUS_CLOSED, self::STATUS_OPENED];
      default:
        return [];
    }
  }

  /**
   * @param \DateTime $before
   *
   * @return static
   */
  public function setStatus(array $status)
  {
    $this->status = array_reduce($status, function($result, $item) {
      return $result|$item;
    }, 0);
    return $this;
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
  public function setUser(User $user)
  {
    $this->user = $user;
    return $this;
  }

  public function hasAdvancedParameters() {
    return $this->getPublishedBefore() ||
      $this->getPublishedAfter() ||
      $this->status !== self::STATUS_OPENED
    ;
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
      $queryMust[] = [ 'simple_query_string' => [
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
        'default_operator' => 'AND'
      ]];
    }

    $publishedBefore = $this->getPublishedBefore();
    if($publishedBefore !== null) {
      $filterAnd[] = [
        'range' => [
          'main.GESTION.INDEXATION.DATE_PUBLICATION'  => [
            'lt' => $publishedBefore->format('Y-m-d')
          ]
        ]
      ];
    }

    $publishedAfter = $this->getPublishedAfter();
    if($publishedAfter !== null) {
      $filterAnd[] = [
        'range' => [
          'main.GESTION.INDEXATION.DATE_PUBLICATION'  => [
            'gt' => $publishedAfter->format('Y-m-d')
          ]
        ]
      ];
    }

    $status = $this->status;
    if($status === self::STATUS_OPENED || $status === self::STATUS_CLOSED) {
      $now = new \DateTime('now');
      $comp = $status === self::STATUS_OPENED ? 'gt' : 'lt';
      $filterAnd[] = [
        'or' => [
          [
            'range' => [
              'main.GESTION.INDEXATION.DATE_LIMITE_REPONSE'  => [
                $comp => $now->format('Y-m-d H:i:s'),
                'format' => 'yyyy-MM-dd HH:mm:ss'
              ]
            ]
          ],
          [
            'range' => [
              'main.DONNEES.CONDITION_DELAI.RECEPT_OFFRES'  => [
                $comp => $now->format('Y-m-d H:i:s'),
                'format' => 'yyyy-MM-dd HH:mm:ss'
              ]
            ]
          ]
        ]
      ];
    }

    return ['body' => $body];

  }


}
