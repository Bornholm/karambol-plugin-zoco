<?php

namespace KarambolZocoPlugin;

use Karambol\KarambolApp;
use Karambol\Plugin\PluginInterface;
use KarambolZocoPlugin\Command as Command;
use KarambolZocoPlugin\Provider\ZocoElasticsearchClientProvider;
use KarambolZocoPlugin\Controller as Controller;

class ZocoPlugin implements PluginInterface {

  public function boot(KarambolApp $app, array $options) {
    $this->addViews($app);
    $this->addEntities($app);
    $this->addCommands($app, $options);
    $this->addServices($app, $options);
    $this->addControllers($app);
  }

  protected function addCommands(KarambolApp $app, array $options) {
    $boampOptions = $options['boamp'];
    $app['console']->add(new Command\FetchBoampCommand($app, $boampOptions));
    $app['console']->add(new Command\ExtractBoampCommand($app, $boampOptions));
    $app['console']->add(new Command\ParseBoampCommand($app, $boampOptions));
    $app['console']->add(new Command\CreateIndexCommand($app, $options['elasticsearch']['index']));
    $app['console']->add(new Command\SearchIndexCommand($app, $options['elasticsearch']['index']));
  }

  protected function addEntities(KarambolApp $app) {
    $annotationDriver = $app['orm']->getConfiguration()->getMetadataDriverImpl();
    $annotationDriver->addPaths([__DIR__.'/Entity']);
  }

  protected function addServices(KarambolApp $app, array $options) {
    $elasticsearchOptions = $options['elasticsearch'];
    $app->register(new ZocoElasticsearchClientProvider($elasticsearchOptions));
  }

  protected function addControllers(KarambolApp $app) {
    $ctrl = new Controller\SearchController($app);
    $ctrl->bindTo($app);
  }

  public function addViews($app) {
    $twigPaths = $app['twig.path'];
    array_unshift($twigPaths, __DIR__.'/Views');
    $app['twig.path'] = $twigPaths;
  }
}
