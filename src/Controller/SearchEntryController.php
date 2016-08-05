<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use KarambolZocoPlugin\Search as Search;
use KarambolZocoPlugin\Search\BoampEntry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchEntryController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/search/{entryType}/{entryId}', [$this, 'showSearchEntry'])->bind('plugins_zoco_search_entry');
  }

  public function showSearchEntry($entryType, $entryId) {

    $twig = $this->get('twig');
    $esClient = $this->get('zoco_elasticsearch_client');

    $params = [
      'index' => 'zoco',
      'type' => $entryType,
      'id' => $entryId
    ];

    $result = $esClient->get($params);

    switch($entryType) {
      case 'boamp':
        $entry = new BoampEntry($result['_source']);
        break;
      default:
        throw new NotFoundHttpException();
    }

    return $twig->render('plugins/zoco/search/entry.html.twig', [
      'entry' => $entry
    ]);

  }

}
