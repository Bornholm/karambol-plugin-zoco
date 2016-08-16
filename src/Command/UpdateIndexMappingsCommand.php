<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;
use Symfony\Component\Console\Command\Command;

class UpdateIndexMappingsCommand extends Command
{

  protected $app;
  protected $indexOptions;

  public function __construct(KarambolApp $app, array $indexOptions) {
    parent::__construct();
    $this->app = $app;
    $this->indexOptions = $indexOptions;
  }

  protected function configure() {
    $this
      ->setName('zoco-plugin:index:update-mappings')
      ->setDescription('Mettre à jour les mappings de l\'index.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $indexOptions = $this->indexOptions;
    $indexName = $indexOptions['name'];
    $indexMappings = $indexOptions['mappings'];

    $client = $this->app['zoco.elasticsearch']->getClient();

    $indicesSettings = $client->indices()->getSettings();

    // Suppression de l'index au cas où celui ci existerait
    if(!isset($indicesSettings[$indexName])) {
      $output->writeln(sprintf('<comment>The index "%s" does not exists.</comment>', $indexName));
      return 1;
    }

    foreach($indexMappings as $documentType => $mapping) {
      $indexParams = [
        'index' => $indexName,
        'type' => $documentType,
        'body' => [
          $documentType => $mapping
        ]
      ];
      $output->writeln(sprintf('<info>Updating "%s/%s" mappings...</info>', $indexName, $documentType));
      $res = $client->indices()->putMapping($indexParams);
    }

    $output->writeln('<info>Done.</info>');
    return 0;

  }

}
