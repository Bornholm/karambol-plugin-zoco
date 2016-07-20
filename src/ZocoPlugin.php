<?php

namespace KarambolZocoPlugin;

use Karambol\KarambolApp;
use Karambol\Plugin\PluginInterface;
use KarambolZocoPlugin\Command as Command;

class ZocoPlugin implements PluginInterface {

  public function boot(KarambolApp $app, array $options) {
    $this->addEntities($app);
    $this->addCommands($app, $options);
  }

  protected function addCommands(KarambolApp $app, array $options) {
    $app['console']->add(new Command\FetchBoampCommand($app));
    $app['console']->add(new Command\ExtractBoampCommand($app));
    $app['console']->add(new Command\ParseBoampCommand($app));
  }

  public function addEntities(KarambolApp $app) {
    $annotationDriver = $app['orm']->getConfiguration()->getMetadataDriverImpl();
    $annotationDriver->addPaths([__DIR__.'/Entity']);
  }

}
