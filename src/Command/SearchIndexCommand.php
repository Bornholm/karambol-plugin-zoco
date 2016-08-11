<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;
use Symfony\Component\Console\Command\Command;

class SearchIndexCommand extends Command
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
      ->setName('zoco-plugin:index:search')
      ->setDescription('Search for entries in the Zoco index')
      ->addArgument('search', InputArgument::REQUIRED, 'La recherche à effectuer sur l\'index')
      ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Maximum de résultats à retourner', 1)
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $indexOptions = $this->indexOptions;
    $indexName = $indexOptions['name'];
    $search = $input->getArgument('search');
    $limit = $input->getOption('limit');

    $client = $this->app['zoco.elasticsearch_client'];

    $searchParams = [
      'index' => $indexName,
      'body' => [
        'query' => [
          'query_string' => [
            'query' => $search
          ]
        ],
        'size' => $limit
      ]
    ];

    $res = $client->search($searchParams);

    var_dump($res['hits'][0]);

    $output->writeln('<info>Done.</info>');
    return 0;

  }

}
