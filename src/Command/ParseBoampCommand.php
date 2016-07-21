<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;
use KarambolZocoPlugin\Entity\BoampEntry;
use \SimpleXMLElement;

class ParseBoampCommand extends BoampCommand
{

  protected function configure()
  {
    $this
      ->setName('zoco-plugin:boamp:parse-xml')
      ->setDescription('Analyse les fichiers XML du BOAMP et les intégre à la base de connaissance de Zoco.')
      ->addOption('year', null, InputOption::VALUE_OPTIONAL, 'Année de publication des marchés à télécharger', date("Y"))
      ->addOption('stop-on-parse-error', null, InputOption::VALUE_OPTIONAL, 'Arreter le traitement en cas d\'erreur d\'analyse.', false)
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $client = $this->app['zoco_elasticsearch_client'];
    $baseDestDir = $this->getDataDirectory();
    $remoteDir = $this->getRemoteDir($input->getOption('year'));
    $stopOnParseError = $input->getOption('stop-on-parse-error') === 'true';

    $xmlFiles = glob($baseDestDir.'/xml/'.$remoteDir.'/*/*.xml');

    $total = count($xmlFiles);
    $bulkSize = 250;
    $totalBulks = (int)($total/$bulkSize);

    $params = ['body' => []];
    $bulkCount = 0;
    foreach($xmlFiles as $xmlFile) {

      $xmlStr = file_get_contents($xmlFile);
      $xml = null;

      try {
        $xml = new SimpleXMLElement($xmlStr);
      } catch(\Exception $ex) {
        $output->writeln(sprintf('<error>Error while parsing file "%s" !</error>', $xmlFile));
        $output->writeln($ex->getTraceAsString());
        if($stopOnParseError) return 1;
        continue;
      }

      $webId = (string)$xml->GESTION->REFERENCE->IDWEB;
      $body = json_decode(json_encode($xml), true);

      $params['body'][] = [
        'index' => [
          '_index' => 'zoco',
          '_type' => 'boamp'
        ]
      ];

      $params['body'][] = $body;

      $flush = count($params['body']) >= $bulkSize*2;
      if($flush) {
        $output->writeln(sprintf('<info>Flushing bulk (%s/%s)...</info>', $bulkCount++, $totalBulks));
        $client->bulk($params);
        $params = ['body' => []];
      }

    }

    $output->writeln('<info>Flushing last bulk...</info>');
    $client->bulk($params);

    $output->writeln('<info>Done.</info>');

    return 0;

  }

}
