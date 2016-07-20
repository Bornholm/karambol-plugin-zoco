<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;

class ExtractBoampCommand extends Command
{

  protected $app;

  public function __construct(KarambolApp $app) {
    parent::__construct();
    $this->app = $app;
  }

  protected function configure()
  {
    $this
      ->setName('zoco-plugin:extract-boamp-archives')
      ->setDescription('Extrait les fichiers XML des archives du BOAMP')
      ->addOption('dest-dir', null, InputOption::VALUE_OPTIONAL, 'Chemin du dossier de destination pour les archives', __DIR__.'/../../.boamp')
      ->addOption('remote-dir', null, InputOption::VALUE_OPTIONAL, 'Chemin de base du dossier distant sur le serveur FTP', 'BOAMP/'.date("Y"))
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $baseDestDir = $input->getOption('dest-dir');
    $remoteDir = $input->getOption('remote-dir');
    $destDir = $baseDestDir.'/xml/'.$remoteDir;

    if(!file_exists($destDir)) mkdir($destDir, 0700, true);

    $archives = glob($baseDestDir.'/archives/'.$remoteDir.'/*.taz');

    foreach($archives as $archFile) {
      $stdout = '';
      $output->writeln(sprintf('<info>Extracting archive "%s"...</info>', $archFile));
      exec(sprintf('tar -xzf "%s" -C "%s" --wildcards *.xml', $archFile, $destDir), $stdout);
      $output->writeln($stdout);
    }

    $output->writeln('<info>Done.</info>');

  }
}
