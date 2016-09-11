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
    $app->post('/zoco/{tenderType}/{tenderId}/pin', [$this, 'pinEntry'])
      ->value('tenderId', '/[a-z0-9\-]+/i')
      ->value('tenderType', '/[a-z0-9\-]+/i')
      ->bind('plugins_zoco_pin_tender')
    ;
    $app->delete('/zoco/{tenderType}/{tenderId}/pin', [$this, 'unpinEntry'])
      ->value('tenderId', '/[a-z0-9\-]+/i')
      ->value('tenderType', '/[a-z0-9\-]+/i')
      ->bind('plugins_zoco_unpin_tender')
    ;
  }

  public function showPinboard() {

    $user = $this->get('user');
    $twig = $this->get('twig');
    $es = $this->get('zoco.elasticsearch');

    $tenders = [];
    $pins = $this->get('zoco.tender_pin')->getUserPins($user);

    if(count($pins) > 0) {

      $esClient = $es->getClient();
      $params = [ 'body' => [ 'docs' => [] ] ];

      foreach($pins as $pin) {
        $params['body']['docs'][] = [
          '_index' => 'zoco',
          '_type' => $pin->getTenderType(),
          '_id' => $pin->getTenderId()
        ];
      }

      $results = $esClient->mget($params);

      foreach($results['docs'] as $hit) {
        $tenders[] = $es->createDocumentFromHit($hit);
      }

    }

    return $twig->render('plugins/zoco/pinboard/pinboard.html.twig', [
      'tenders' => $tenders
    ]);

  }

  public function pinEntry($tenderType, $tenderId) {

    $user = $this->get('user');
    $orm = $this->get('orm');

    $search = $this->get('zoco.tender_pin');
    $user = $this->get('user');

    $this->get('zoco.tender_pin')
      ->pin($user, ['type' => $tenderType, 'id' => $tenderId])
    ;

    return new JsonResponse(['result' => 'OK']);

  }

  public function unpinEntry($tenderType, $tenderId) {

    $user = $this->get('user');

    $this->get('zoco.tender_pin')
      ->unpin($user, ['type' => $tenderType, 'id' => $tenderId])
    ;

    return new JsonResponse(['result' => 'OK']);

  }

}
