<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;

class SearchController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/search', [$this, 'showSearchIndex'])->bind('plugins_zoco_search');
  }

  public function showSearchIndex() {

    $request = $this->get('request');
    $twig = $this->get('twig');

    $search = $request->query->get('q');

    if(empty($search)) return $twig->render('plugins/zoco/search/index.html.twig');

    return $this->handleSearch($search);

  }

  protected function handleSearch($search) {

    $twig = $this->get('twig');
    $esClient = $this->get('zoco_elasticsearch_client');

    $results = $esClient->search([
      'index' => 'zoco',
      'body' => [
        'query' => [
          'query_string' => [
            'query' => $search
          ]
        ],
        'size' => 100
      ]
    ]);

    dump($results);

    return $twig->render('plugins/zoco/search/results.html.twig', [
      'search' => (string)$search,
      'results' => $results
    ]);

  }

}
