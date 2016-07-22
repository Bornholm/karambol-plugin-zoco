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
      ->addOption('stop-on-parse-error', null, InputOption::VALUE_OPTIONAL, 'Arreter le traitement en cas d\'erreur d\'analyse', false)
      ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, 'Ne pas appliquer les changements', false)
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $client = $this->app['zoco_elasticsearch_client'];
    $baseDestDir = $this->getDataDirectory();
    $remoteDir = $this->getRemoteDir($input->getOption('year'));
    $stopOnParseError = $input->getOption('stop-on-parse-error') === 'true';
    $dryRun = $input->getOption('dry-run');

    $xmlFiles = glob($baseDestDir.'/xml/'.$remoteDir.'/*/*.xml');
    $total = count($xmlFiles);

    foreach($xmlFiles as $i => $xmlFile) {

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

      $webId = null;
      $prevAnnouncement = $xml->xpath('//GESTION/MARCHE/ANNONCE_ANTERIEUR');

      if(count($prevAnnouncement) > 0) {
        $webId = (string)$xml->GESTION->MARCHE->ANNONCE_ANTERIEUR->REFERENCE->IDWEB;
      } else {
        $webId = (string)$xml->GESTION->REFERENCE->IDWEB;
      }

      $body = json_decode(json_encode($xml), true);

      $params = [
        'index' => 'zoco',
        'type' => 'boamp',
        'id' => $webId,
        'body' => [
          'doc' => [
            $webId => $body
          ],
          'upsert' => [
            $webId => $body
          ]
        ]
      ];

      $output->writeln(sprintf('<info>Indexing entry %s/%s...</info>', $i, $total));
      if(!$dryRun) $client->update($params);

    }

    $output->writeln('<info>Done.</info>');

    return 0;

  }

}
