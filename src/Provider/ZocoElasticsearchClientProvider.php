<?php

namespace KarambolZocoPlugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Elasticsearch\ClientBuilder;

class ZocoElasticsearchClientProvider implements ServiceProviderInterface
{

  protected $clientConfig;

  public function __construct(array $clientConfig) {
    $this->clientConfig = $clientConfig;
  }

  public function register(Application $app) {
    $config = $this->clientConfig;
    $app['zoco_elasticsearch_client'] = ClientBuilder::create()
      ->setHosts($config['hosts'])
      ->setLogger($app['logger'])
      ->build()
    ;
  }

  public function boot(Application $app) {}

}
