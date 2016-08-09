<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use KarambolZocoPlugin\Search as Search;

class SearchController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/search', [$this, 'showSearchIndex'])->bind('plugins_zoco_search');
  }

  public function showSearchIndex() {

    $this->assertUrlAccessAuthorization();

    $request = $this->get('request');
    $twig = $this->get('twig');

    $search = $request->query->get('q');
    $page = $request->query->get('p', 0);
    $limit = $request->query->get('l', 50);

    return $this->handleSearch(empty($search) ? '' : $search, $page*$limit, $limit);

  }

  protected function handleSearch($search, $offset = 0, $limit = 50) {

    $twig = $this->get('twig');
    $esClient = $this->get('zoco_elasticsearch_client');

    $params = [
      'index' => 'zoco',
      'body' => [
        'query' => [
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
        ],
        'from' => $offset,
        'size' => $limit,
        'sort' => [
          ['main.GESTION.INDEXATION.DATE_PUBLICATION' => 'desc'],
          ['main.GESTION.INDEXATION.DATE_LIMITE_REPONSE' => 'asc']
        ],
        'highlight' => [
          'fields' => [
            '*' => new \stdClass()
          ]
        ]
      ]
    ];

    $results = $esClient->search($params);

    $total = $results['hits']['total'];
    $hits = $results['hits']['hits'];

    $entries = [];
    foreach($hits as $hit) {
      if($hit['_type'] === 'boamp') {
        $entries[] = new Search\BoampEntry($hit['_source']);
      }
    }


    $user = $this->get('user');
    $orm = $this->get('orm');
    $repo = $orm->getRepository('KarambolZocoPlugin\Entity\PinnedEntry');

    foreach($entries as $entry) {
      $qb = $repo->createQueryBuilder('p');
      $qb->select('count(p.id)')->where($qb->expr()->andX(
        $qb->expr()->eq('p.userId', $user->getId()),
        $qb->expr()->eq('p.entryId', $qb->expr()->literal($entry->getId())),
        $qb->expr()->eq('p.entryType', $qb->expr()->literal($entry->getType()))
      ));
      $count = $qb->getQuery()->getSingleScalarResult();
      if($count > 0) $pins[$entry->getId()] = true;
    }

    return $twig->render('plugins/zoco/search/results.html.twig', [
      'pins' => $pins,
      'search' => $search,
      'results' => $entries,
      'total' => $total,
      'offset' => $offset,
      'limit' => $limit
    ]);

  }

}
