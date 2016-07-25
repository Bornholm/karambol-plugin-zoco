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

    $request = $this->get('request');
    $twig = $this->get('twig');

    $search = $request->query->get('q');
    $page = $request->query->get('p', 0);
    $limit = $request->query->get('l', 50);

    if(empty($search)) return $twig->render('plugins/zoco/search/index.html.twig');

    return $this->handleSearch($search, $page*$limit, $limit);

  }

  protected function handleSearch($search, $offset = 0, $limit = 50) {

    $twig = $this->get('twig');
    $esClient = $this->get('zoco_elasticsearch_client');

    $results = $esClient->search([
      'index' => 'zoco',
      'body' => [
        'query' => [
          'query_string' => [
            'query' => $search,
            'default_operator' => 'AND'
          ]
        ],
        'from' => $offset,
        'size' => $limit,
        'sort' => [
          ['main.GESTION.INDEXATION.DATE_PUBLICATION' => 'desc'],
          ['main.GESTION.INDEXATION.DATE_LIMITE_REPONSE' => 'asc']
        ]
      ]
    ]);

    $total = $results['hits']['total'];
    $hits = $results['hits']['hits'];

    $entries = [];
    foreach($hits as $hit) {
      if($hit['_type'] === 'boamp') {
        $entries[] = new Search\BoampEntry($hit['_source']);
      }
    }

    return $twig->render('plugins/zoco/search/results.html.twig', [
      'search' => $search,
      'results' => $entries,
      'total' => $total,
      'offset' => $offset,
      'limit' => $limit
    ]);

  }

}
