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
    $esService = $this->get('zoco.elasticsearch');

    $params = [
      'body' => [
        'filter' => [
          'and' => [
            [ 'exists' => [ 'field' => 'main' ] ]
          ]
        ],
        'from' => $offset,
        'size' => $limit,
        'sort' => [
          ['main.GESTION.INDEXATION.DATE_PUBLICATION' => 'desc'],
          ['main.GESTION.INDEXATION.DATE_LIMITE_REPONSE' => 'asc']
        ]
      ]
    ];

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
      $params['body']['query'] = $query;
    }

    $results = $esService->query($params);

    $user = $this->get('user');
    $pins = $this->get('zoco.tender_pin')->havePins($user, $results->getDocuments());

    return $twig->render('plugins/zoco/search/results.html.twig', [
      'search' => $search,
      'results' => $results->getDocuments(),
      'total' => $results->getTotal(),
      'offset' => $offset,
      'limit' => $limit,
      'pins' => $pins
    ]);

  }

}
