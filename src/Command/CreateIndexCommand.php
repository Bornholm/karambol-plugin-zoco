<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;
use Symfony\Component\Console\Command\Command;

class CreateIndexCommand extends Command
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
      ->setName('zoco-plugin:index:create')
      ->setDescription('Initialise l\'index pour le moteur de recherche des marchés')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $indexOptions = $this->indexOptions;
    $indexName = $indexOptions['name'];
    $indexMappings = $indexOptions['mappings'];
    $indexSettings = $indexOptions['settings'];

    $client = $this->app['zoco.elasticsearch_client'];

    $indicesSettings = $client->indices()->getSettings();

    // Suppression de l'index au cas où celui ci existerait
    if(isset($indicesSettings[$indexName])) {
      $output->writeln(sprintf('<comment>The index "%s" already exists. Deleting...</comment>', $indexName));
      $res = $client->indices()->delete(['index' => $indexName]);
      if(!$res || !isset($res['acknowledged']) || $res['acknowledged'] !== true) {
        $output->writeln(sprintf('<error>An error occured while deleting the "%s" index !</error>', $indexName));
        return 1;
      }
    }

    $indexCreationParams = [
      'index' => $indexName,
      'body' => []
    ];

    if($indexMappings) $indexCreationParams['body']['mappings'] = $indexMappings;
    if($indexSettings) $indexCreationParams['body']['settings'] = $indexSettings;

    $output->writeln(sprintf('<info>Creating index "%s"...</info>', $indexName));
    $res = $client->indices()->create($indexCreationParams);

    $output->writeln('<info>Done.</info>');
    return 0;

  }

}
