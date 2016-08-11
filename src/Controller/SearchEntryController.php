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

    $this->assertUrlAccessAuthorization();

    $twig = $this->get('twig');
    $searchService = $this->get('zoco.search');
    $user = $this->get('user');

    $result = $searchService->fetchEntry($entryType, $entryId);

    return $twig->render('plugins/zoco/search/entry.html.twig', [
      'entry' => $result['entry']
    ]);

  }

}
