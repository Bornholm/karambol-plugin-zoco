<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;

class ExtractBoampCommand extends BoampCommand
{

  protected function configure() {
    $this
      ->setName('zoco-plugin:boamp:extract-archives')
      ->setDescription('Extrait les fichiers XML des archives du BOAMP')
      ->addOption('year', null, InputOption::VALUE_OPTIONAL, 'Année de publication des marchés à télécharger', date("Y"))
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $baseDestDir = $this->getDataDirectory();
    $remoteDir = $this->getRemoteDir($input->getOption('year'));
    $destDir = $baseDestDir.'/xml/'.$remoteDir;

    if(!file_exists($destDir)) mkdir($destDir, 0700, true);

    $archives = glob($baseDestDir.'/archives/'.$remoteDir.'/*.taz');

    foreach($archives as $archFile) {
      $stdout = '';
      $output->writeln(sprintf('<info>Extracting archive "%s"...</info>', realpath($archFile)));
      exec(sprintf('tar -xzf "%s" -C "%s" --wildcards *.xml', $archFile, $destDir), $stdout);
      $output->writeln($stdout);
    }

    $output->writeln('<info>Done.</info>');

  }
}
