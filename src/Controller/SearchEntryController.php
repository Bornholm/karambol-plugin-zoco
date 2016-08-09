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
    $esClient = $this->get('zoco_elasticsearch_client');

    $params = [
      'index' => 'zoco',
      'type' => $entryType,
      'id' => $entryId
    ];

    $result = $esClient->get($params);

    // dump($result);

    switch($entryType) {
      case 'boamp':
        $entry = new BoampEntry($result['_source']);
        break;
      default:
        throw new NotFoundHttpException();
    }

    $user = $this->get('user');
    $orm = $this->get('orm');
    $repo = $orm->getRepository('KarambolZocoPlugin\Entity\PinnedEntry');

    $qb = $repo->createQueryBuilder('p');
    $qb->select('count(p.id)')->where($qb->expr()->andX(
      $qb->expr()->eq('p.userId', $user->getId()),
      $qb->expr()->eq('p.entryId', $qb->expr()->literal($entry->getId())),
      $qb->expr()->eq('p.entryType', $qb->expr()->literal($entry->getType()))
    ));
    $isPinned = $qb->getQuery()->getSingleScalarResult() > 0;

    return $twig->render('plugins/zoco/search/entry.html.twig', [
      'entry' => $entry,
      'isPinned' => $isPinned
    ]);

  }

}
