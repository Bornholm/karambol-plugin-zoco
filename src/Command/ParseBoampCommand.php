<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;
use KarambolZocoPlugin\Entity\BoampEntry;
use Symfony\Component\Filesystem\Filesystem;
use \SimpleXMLElement;

class ParseBoampCommand extends Command
{

  protected $app;
  protected $output;

  public function __construct(KarambolApp $app) {
    parent::__construct();
    $this->app = $app;
  }

  protected function configure()
  {
    $this
      ->setName('zoco-plugin:parse-boamp-xml')
      ->setDescription('Analyse les fichiers XML du BOAMP et les intégre à la base de connaissance de Zoco.')
      ->addOption('dest-dir', null, InputOption::VALUE_OPTIONAL, 'Chemin du dossier de destination pour les archives', realpath(__DIR__.'/../../.boamp'))
      ->addOption('remote-dir', null, InputOption::VALUE_OPTIONAL, 'Chemin de base du dossier distant sur le serveur FTP', 'BOAMP/'.date("Y"))
      ->addOption('stop-on-parse-error', null, InputOption::VALUE_OPTIONAL, 'Arreter le traitement en cas d\'erreur d\'analyse.', false)
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $this->output = $output;
    $fs = new Filesystem();

    $baseDestDir = $input->getOption('dest-dir');
    $remoteDir = $input->getOption('remote-dir');
    $stopOnParseError = $input->getOption('stop-on-parse-error') === 'true';

    $xmlFiles = glob($baseDestDir.'/xml/'.$remoteDir.'/*/*.xml');

    $batchCount = 0;
    foreach($xmlFiles as $xmlFile) {

      $xmlFile = realpath($xmlFile);

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

      $batchCount++;
      $flush = $batchCount % 100 === 0;
      if($flush) $batchCount = 0;
      $shortXmlPath = rtrim($fs->makePathRelative($xmlFile, $baseDestDir), '/');
      $result  = $this->upsertBoampEntry($shortXmlPath, $xml, $flush);
      if(!$result) continue;

    }

    $this->app['orm']->flush();

    $output->writeln('<info>Done.</info>');

    return 0;

  }

  protected function upsertBoampEntry($xmlFile, $xml, $flush = false) {

    $orm = $this->app['orm'];

    $webId = (string)$xml->GESTION->REFERENCE->IDWEB[0];

    $entry = $orm->getRepository('KarambolZocoPlugin\Entity\BoampEntry')->findOneByWebId($webId);

    if(!$entry) {
      $entry = new BoampEntry();
      $entry->webId = $webId;
      $orm->persist($entry);
    }

    $entry->xmlFile = $xmlFile;

    $this->output->writeln(sprintf('<info>Saving BOAMP entry "%s"...</info>', $webId));

    if($flush) $orm->flush();

    return true;

  }

}
