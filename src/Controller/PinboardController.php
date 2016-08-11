<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use Symfony\Component\HttpFoundation\JsonResponse;
use KarambolZocoPlugin\Entity\PinnedEntry;
use KarambolZocoPlugin\Search\BoampEntry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PinboardController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/pinboard', [$this, 'showPinboard'])->bind('plugins_zoco_pinboard');
    $app->post('/zoco/{entryType}/{entryId}/pin', [$this, 'pinEntry'])
      ->value('entryId', '/[a-z0-9\-]+/i')
      ->value('entryType', '/[a-z0-9\-]+/i')
      ->bind('plugins_zoco_pin_entry')
    ;
    $app->delete('/zoco/{entryType}/{entryId}/pin', [$this, 'unpinEntry'])
      ->value('entryId', '/[a-z0-9\-]+/i')
      ->value('entryType', '/[a-z0-9\-]+/i')
      ->bind('plugins_zoco_unpin_entry')
    ;
  }

  public function showPinboard() {

    $this->assertUrlAccessAuthorization();

    $user = $this->get('user');
    $twig = $this->get('twig');
    $searchService = $this->get('zoco.search');

    $result = $searchService->fetchPinnedEntries($user->getId());

    return $twig->render('plugins/zoco/pinboard/pinboard.html.twig', [
      'entries' => $result['entries']
    ]);

  }

  public function pinEntry($entryType, $entryId) {

    $this->assertUrlAccessAuthorization();

    $user = $this->get('user');
    $orm = $this->get('orm');

    $search = $this->get('zoco.search');
    $user = $this->get('user');

    $search->pin($user->getId(), $entryType, $entryId);

    return new JsonResponse(['result' => 'OK']);

  }

  public function unpinEntry($entryType, $entryId) {

    $this->assertUrlAccessAuthorization();

    $search = $this->get('zoco.search');
    $user = $this->get('user');

    $search->unpin($user->getId(), $entryType, $entryId);

    return new JsonResponse(['result' => 'OK']);

  }

}
