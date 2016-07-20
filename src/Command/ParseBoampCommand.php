<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;
use \SimpleXMLElement;

class ParseBoampCommand extends Command
{

  protected $app;

  public function __construct(KarambolApp $app) {
    parent::__construct();
    $this->app = $app;
  }

  protected function configure()
  {
    $this
      ->setName('zoco-plugin:parse-boamp')
      ->setDescription('Analyse les fichiers XML du BOAMP et les intégre à la base de connaissance de Zoco.')
      ->addOption('dest-dir', null, InputOption::VALUE_OPTIONAL, 'Chemin du dossier de destination pour les archives', __DIR__.'/../../.boamp')
      ->addOption('remote-dir', null, InputOption::VALUE_OPTIONAL, 'Chemin de base du dossier distant sur le serveur FTP', 'BOAMP/'.date("Y"))
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $baseDestDir = $input->getOption('dest-dir');
    $remoteDir = $input->getOption('remote-dir');

    $xmlFiles = glob($baseDestDir.'/xml/'.$remoteDir.'/*/*.xml');

    $ids = [];

    foreach($xmlFiles as $xmlFile) {
      $xmlStr = file_get_contents($xmlFile);
      $xml = new SimpleXMLElement($xmlStr);
      $idWeb = $xml->GESTION->REFERENCE->IDWEB[0];
      $ids[$idWeb] = isset($ids[$idWeb]) ? $ids[$idWeb]++ : 0;
    }

    dump($ids);

    return 0;

  }
}
