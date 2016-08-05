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
      ->addOption('newer-than', null, InputOption::VALUE_OPTIONAL, 'Extraire uniquement les archives créées/modifiées plus récemment que l\'espace de temps donné', null)
      ->addOption('year', null, InputOption::VALUE_OPTIONAL, 'Année de publication des marchés à télécharger', date("Y"))
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Ne pas télécharger les fichiers, uniquement décrire les opérations')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $baseDestDir = $this->getDataDirectory();
    $remoteDir = $this->getRemoteDir($input->getOption('year'));
    $destDir = $baseDestDir.'/xml/'.$remoteDir;
    $newerThan = $input->getOption('newer-than');
    $dryRun = $input->getOption('dry-run');

    if(!empty($newerThan)) {
      $newerThan = new \DateInterval($newerThan);
      $pivotDate = new \DateTime();
      $pivotDate->sub($newerThan);
    }

    if(!file_exists($destDir)) mkdir($destDir, 0700, true);

    $archives = glob($baseDestDir.'/archives/'.$remoteDir.'/*.taz');

    foreach($archives as $archFile) {

      if($newerThan !== null) {

        $lastModifTimestamp = filemtime($archFile);
        $lastModification = new \DateTime();
        $lastModification->setTimestamp($lastModifTimestamp);

        if($lastModification < $pivotDate) {
          $output->writeln(sprintf(
            '<comment>The archive "%s" (%s) is older than %s. Skipping.</comment>',
            $archFile,
            $lastModification->format('d/M/Y H:i:s'),
            $newerThan->format('%d day(s)'))
          );
          continue;
        }

      }

      $output->writeln(sprintf('<info>Extracting archive "%s"...</info>', realpath($archFile)));

      if(!$dryRun) {
        $stdout = '';
        exec(sprintf('tar -xzf "%s" -C "%s" --wildcards *.xml', $archFile, $destDir), $stdout);
        $output->writeln($stdout);
      }

    }

    $output->writeln('<info>Done.</info>');

  }
}
