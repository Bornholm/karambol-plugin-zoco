<?php

namespace KarambolZocoPlugin;

use Karambol\KarambolApp;
use Karambol\Plugin\PluginInterface;
use KarambolZocoPlugin\Command as Command;
use KarambolZocoPlugin\Provider\ZocoElasticsearchClientProvider;

class ZocoPlugin implements PluginInterface {

  public function boot(KarambolApp $app, array $options) {
    $this->addEntities($app);
    $this->addCommands($app, $options);
    $this->addServices($app, $options);
  }

  protected function addCommands(KarambolApp $app, array $options) {
    $boampOptions = $options['boamp'];
    $app['console']->add(new Command\FetchBoampCommand($app, $boampOptions));
    $app['console']->add(new Command\ExtractBoampCommand($app, $boampOptions));
    $app['console']->add(new Command\ParseBoampCommand($app, $boampOptions));
  }

  protected function addEntities(KarambolApp $app) {
    $annotationDriver = $app['orm']->getConfiguration()->getMetadataDriverImpl();
    $annotationDriver->addPaths([__DIR__.'/Entity']);
  }

  protected function addServices(KarambolApp $app, array $options) {
    $elasticsearchOptions = $options['elasticsearch'];
    $app->register(new ZocoElasticsearchClientProvider($elasticsearchOptions));
  }

}
