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
      ->addOption('bulk-size', null, InputOption::VALUE_OPTIONAL, 'Nombre d\'éléments à traiter par lot', 1000)
      ->addOption('newer-than', null, InputOption::VALUE_OPTIONAL, 'Extraire uniquement les archives créées/modifiées plus récemment que l\'espace de temps donné', null)
      ->addOption('stop-on-parse-error', null, InputOption::VALUE_NONE, 'Arreter le traitement en cas d\'erreur d\'analyse')
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Ne pas appliquer les changements')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $client = $this->app['zoco_elasticsearch_client'];
    $baseDestDir = $this->getDataDirectory();
    $remoteDir = $this->getRemoteDir($input->getOption('year'));
    $stopOnParseError = $input->getOption('stop-on-parse-error') === 'true';
    $newerThan = $input->getOption('newer-than');
    $dryRun = $input->getOption('dry-run');
    $bulkSize = $input->getOption('bulk-size');

    if(!empty($newerThan)) {
      $newerThan = new \DateInterval($newerThan);
      $pivotDate = new \DateTime();
      $pivotDate->sub($newerThan);
    }

    $output->writeln('<info>Selecting XML files...</info>');
    $xmlFiles = glob($baseDestDir.'/xml/'.$remoteDir.'/*/*.xml');
    $total = count($xmlFiles);
    $totalBulks = (int)($total/$bulkSize);
    $bulk = ['body' => []];
    $bulkIndex = 0;

    $output->writeln(sprintf('<comment>%s file to index.</comment>', $total));

    foreach($xmlFiles as $xmlFile) {

      if($newerThan !== null) {

        $lastModifTimestamp = filemtime($xmlFile);
        $lastModification = new \DateTime();
        $lastModification->setTimestamp($lastModifTimestamp);

        if($lastModification < $pivotDate) {
          $output->writeln(sprintf(
            '<comment>The xml file "%s" (%s) is older than %s. Skipping.</comment>',
            $xmlFile,
            $lastModification->format('d/M/Y H:i:s'),
            $newerThan->format('%d day(s)'))
          );
          continue;
        }

      }

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
      $docId = null;
      $prevAnnouncement = $xml->xpath('//GESTION/MARCHE/ANNONCE_ANTERIEUR');
      $isModification = count($prevAnnouncement) > 0;

      if($isModification) {
        $docId = (string)$xml->GESTION->MARCHE->ANNONCE_ANTERIEUR->REFERENCE->IDWEB;
        $webId = (string)$xml->GESTION->REFERENCE->IDWEB;
      } else {
        $docId = $webId = (string)$xml->GESTION->REFERENCE->IDWEB;
      }

      $body = json_decode(json_encode($xml), true);

      $bulk['body'][] = [
        'update' => [
          '_index' => 'zoco',
          '_type' => 'boamp',
          '_id' => $docId
        ]
      ];

      $doc = [];

      if(!$isModification) {
        $doc['main'] = $body;
      } else {
        $doc['modifications'] = [
          $webId => $body
        ];
      }

      $bulk['body'][] = [
        'doc' => $doc,
        'doc_as_upsert' => true
      ];

      $bulkItemsCount = count($bulk['body'])/2;
      $flush = $bulkItemsCount % $bulkSize === 0;
      if($flush) {
        $output->writeln(sprintf('<comment>Flushing bulk %s/%s...</comment>', $bulkIndex, $totalBulks));
        if(!$dryRun) $client->bulk($bulk);
        $bulk = ['body' => []];
        $bulkIndex++;
      }

    }

    $output->writeln(sprintf('<comment>Flushing bulk %s/%s...</comment>', $bulkIndex, $totalBulks));
    if(!$dryRun) $client->bulk($bulk);

    $output->writeln('<info>Done.</info>');

    return 0;

  }

}
