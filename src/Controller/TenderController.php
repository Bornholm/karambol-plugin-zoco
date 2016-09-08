<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use KarambolZocoPlugin\Search as Search;
use KarambolZocoPlugin\Search\BoampEntry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenderController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/{tenderType}/{tenderId}', [$this, 'showSearchEntry'])->bind('plugins_zoco_tender');
  }

  public function showSearchEntry($tenderType, $tenderId) {

    $this->assertUrlAccessAuthorization();

    $tender = $this->get('zoco.elasticsearch')
      ->get('zoco', $tenderType, $tenderId)
    ;

    $user = $this->get('user');
    $hasPin = $user ? $this->get('zoco.tender_pin')->hasPin($user, $tender): false;

    return $this->get('twig')
      ->render('plugins/zoco/tender/tender.html.twig', [
        'tender' => $tender,
        'hasPin' => $hasPin
      ])
    ;

  }

}
