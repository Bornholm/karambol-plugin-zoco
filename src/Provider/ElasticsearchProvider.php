<?php

namespace KarambolZocoPlugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use KarambolZocoPlugin\Elasticsearch\Tender\BoampTender;
use KarambolZocoPlugin\Elasticsearch\ElasticsearchService;

class ElasticsearchProvider implements ServiceProviderInterface
{

  protected $clientConfig;
  protected $logger;

  public function __construct(array $clientConfig, $logger = null) {
    $this->clientConfig = $clientConfig;
    $this->logger = $logger;
  }

  public function register(Application $app) {
    $clientConfig = $this->clientConfig;
    $service = new ElasticsearchService($clientConfig, $this->logger);
    $service->registerDocumentMapping($clientConfig['index']['name'], 'boamp', BoampTender::class);
    $app['zoco.elasticsearch'] = $service;
  }

  public function boot(Application $app) {}

}
