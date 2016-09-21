<?php

namespace KarambolZocoPlugin\Controller;

use Karambol\Controller\Controller;
use Karambol\KarambolApp;
use KarambolZocoPlugin\Entity\Search;
use KarambolZocoPlugin\Form\Type\SearchType;

class SearchController extends Controller {

  public function mount(KarambolApp $app) {
    $app->get('/zoco/search', [$this, 'showSearchIndex'])->bind('plugins_zoco_search');
  }

  public function showSearchIndex() {

    $request = $this->get('request');
    $twig = $this->get('twig');

    $form = $this->getSearchForm();

    $form->handleRequest($request);

    if(!$form->isValid() && count($form->getErrors()) > 0) {
      return $this->render('plugins/zoco/search/search.html.twig', [
        'searchForm' => $form->createView()
      ]);
    }

    $page = $request->query->get('p', 0);
    $limit = $request->query->get('l', 20);

    return $this->handleSearch($form->getData(), $page*$limit, $limit);

  }

  protected function handleSearch(Search $search, $offset, $limit) {

    $twig = $this->get('twig');
    $esService = $this->get('zoco.elasticsearch');
    $query = $search->getElasticsearchQuery();

    $query['body']['from'] = $offset;
    $query['body']['size'] = $limit;
    $query['body']['sort'] = [
      ['main.GESTION.INDEXATION.DATE_PUBLICATION' => 'desc'],
      ['main.GESTION.INDEXATION.DATE_LIMITE_REPONSE' => 'asc']
    ];

    $results = $esService->query($query);

    $user = $this->get('user');
    $pins = $user !== null ? $this->get('zoco.tender_pin')->havePins($user, $results->getDocuments()) : [];

    return $twig->render('plugins/zoco/search/search.html.twig', [
      'searchForm' => $this->getSearchForm($search)->createView(),
      'results' => $results->getDocuments(),
      'total' => $results->getTotal(),
      'offset' => $offset,
      'limit' => $limit,
      'pins' => $pins
    ]);

  }

  protected function getSearchForm(Search $search = null) {

    $formFactory = $this->get('form.factory');
    $urlGen = $this->get('url_generator');

    if($search === null) $search = new Search();

    $formBuilder = $formFactory->createBuilder(SearchType::class, $search);
    $action = $urlGen->generate('plugins_zoco_search');

    return $formBuilder->setAction($action)
      ->setMethod('GET')
      ->getForm()
    ;

  }

}
