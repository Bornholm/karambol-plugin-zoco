<?php

namespace KarambolZocoPlugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use KarambolZocoPlugin\Search\BoampEntry;
use KarambolZocoPlugin\Search\SearchService;

class SearchProvider implements ServiceProviderInterface
{

  protected $clientConfig;

  public function __construct(array $clientConfig) {
    $this->clientConfig = $clientConfig;
  }

  public function register(Application $app) {

    $searchService = new SearchService($app);
    $searchService->setIndex($this->clientConfig['index']['name']);
    $searchService->registerEntryType('boamp', BoampEntry::class);

    $app['zoco.search'] = $searchService;

  }

  public function boot(Application $app) {}

}
