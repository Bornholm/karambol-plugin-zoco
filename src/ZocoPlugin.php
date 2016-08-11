<?php

namespace KarambolZocoPlugin;

use Karambol\KarambolApp;
use Karambol\Plugin\Plugin;
use KarambolZocoPlugin\Command as Command;
use KarambolZocoPlugin\Provider\ElasticsearchClientProvider;
use KarambolZocoPlugin\Provider\SearchProvider;
use KarambolZocoPlugin\Controller as Controller;
use KarambolZocoPlugin\Controller\SearchController;

class ZocoPlugin extends Plugin {

  public function boot(KarambolApp $app, array $options) {

    parent::boot($app, $options);

    $this->registerViews(__DIR__.'/Views');
    $this->registerControllers([
      Controller\SearchController::class,
      Controller\SearchEntryController::class,
      Controller\PinboardController::class
    ]);
    $this->registerTranslation('fr', __DIR__.'/../i18n/fr.yml');
    $this->addSystemPages($app);
    $this->addCommands($app, $options);
    $this->addServices($app, $options);

  }

  protected function addCommands(KarambolApp $app, array $options) {
    $boampOptions = $options['boamp'];
    $app['console']->add(new Command\FetchBoampCommand($app, $boampOptions));
    $app['console']->add(new Command\ExtractBoampCommand($app, $boampOptions));
    $app['console']->add(new Command\ParseBoampCommand($app, $boampOptions));
    $app['console']->add(new Command\CreateIndexCommand($app, $options['elasticsearch']['index']));
    $app['console']->add(new Command\SearchIndexCommand($app, $options['elasticsearch']['index']));
  }

  protected function addServices(KarambolApp $app, array $options) {
    $elasticsearchOptions = $options['elasticsearch'];
    $app->register(new ElasticsearchClientProvider($elasticsearchOptions));
    $app->register(new SearchProvider($elasticsearchOptions));
  }

  protected function addSystemPages($app) {
    $urlGen = $app['url_generator'];
    $this->registerSystemPage('plugins.zoco.search_page', $urlGen->generate('plugins_zoco_search'), 'zoco-search');
    $this->registerSystemPage('plugins.zoco.pinboard_page', $urlGen->generate('plugins_zoco_pinboard'), 'zoco-pinboard');
  }

}
