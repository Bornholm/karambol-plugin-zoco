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
    $orm = $this->get('orm');

    $pins = $orm->getRepository('KarambolZocoPlugin\Entity\PinnedEntry')->findAll([
      'userId' => $user->getId()
    ]);

    $entries = count($pins) > 0 ? $this->getAssociatedEntries($pins) : [];

    return $twig->render('plugins/zoco/pinboard/pinboard.html.twig', [
      'entries' => $entries
    ]);

  }

  public function pinEntry($entryType, $entryId) {

    $this->assertUrlAccessAuthorization();

    $user = $this->get('user');
    $orm = $this->get('orm');

    $pin = $orm->getRepository('KarambolZocoPlugin\Entity\PinnedEntry')->findOneBy([
      'userId' => $user->getId(),
      'entryType' => $entryType,
      'entryId' => $entryId
    ]);

    if($pin) return new JsonResponse(['result' => 'OK']);

    $pin = new PinnedEntry();

    $pin->setUserId($user->getId());
    $pin->setEntryType($entryType);
    $pin->setEntryId($entryId);

    $orm->persist($pin);
    $orm->flush();

    return new JsonResponse(['result' => 'OK']);

  }

  public function unpinEntry($entryType, $entryId) {

    $this->assertUrlAccessAuthorization();

    $orm = $this->get('orm');
    $user = $this->get('user');

    $pin = $orm->getRepository('KarambolZocoPlugin\Entity\PinnedEntry')->findOneBy([
      'userId' => $user->getId(),
      'entryType' => $entryType,
      'entryId' => $entryId
    ]);

    if($pin) {
      $orm->remove($pin);
      $orm->flush();
    }

    return new JsonResponse(['result' => 'OK']);

  }


  protected function getAssociatedEntries(array $pins) {

    $esClient = $this->get('zoco_elasticsearch_client');

    $params = [
      'index' => 'zoco',
      'body' => [
        'docs' => []
      ]
    ];

    foreach($pins as $pin) {
      $params['body']['docs'][] = [
        '_type' => $pin->getEntryType(),
        '_id' => $pin->getEntryId()
      ];
    }

    $results = $esClient->mget($params);

    $entries = [];
    foreach($results['docs'] as $doc) {
      switch($doc['_type']) {
        case 'boamp':
          $entries[] = new BoampEntry($doc['_source']);
          break;
      }
    }

    return $entries;

  }

}
