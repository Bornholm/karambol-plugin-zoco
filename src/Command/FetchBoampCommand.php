<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Karambol\KarambolApp;

class FetchBoampCommand extends BoampCommand
{

  protected $output;
  protected $lastReconnect;

  protected function configure()
  {
    $this
      ->setName('zoco-plugin:boamp:fetch-archives')
      ->setDescription('Télécharge les fichiers XML du BOAMP à partir du serveur FTP de la DILA')
      ->addOption('year', null, InputOption::VALUE_OPTIONAL, 'Année de publication des marchés à télécharger', date("Y"))
      ->addOption('newer-than', null, InputOption::VALUE_OPTIONAL, 'Télécharger uniquement les fichiers crééés plus récemment que l\'espace de temps donné', null)
      ->addOption('force-sync', null, InputOption::VALUE_NONE, 'Retélécharger les fichiers si leur taille est différente')
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Ne pas télécharger les fichiers, uniquement décrire les opérations')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $this->output = $output;

    $newerThan = $input->getOption('newer-than');
    $dryRun = $input->getOption('dry-run');
    $forceSync = $input->getOption('force-sync');

    if(!empty($newerThan)) {
      $newerThan = new \DateInterval($newerThan);
      $pivotDate = new \DateTime();
      $pivotDate->sub($newerThan);
    }

    $ftpServer = $this->options['ftp']['host'];
    $ftpUser = $this->options['ftp']['user'];
    $ftpPassword = $this->options['ftp']['password'];

    $baseDestDir = $this->getDataDirectory();
    $remoteDir = $this->getRemoteDir($input->getOption('year'));
    $destDir = $baseDestDir.'/archives/'.$remoteDir;

    if(!file_exists($destDir)) mkdir($destDir, 0700, true);

    $ftpConn = $this->getFTPConnection($ftpServer, $ftpUser, $ftpPassword);
    if(!$ftpConn) return 1;

    $remoteFiles = ftp_nlist($ftpConn, $remoteDir);

    foreach($remoteFiles as $remoteFilename) {

      if(time() - $this->lastReconnect > 30) {
        $ftpConn = $this->reconnect($ftpConn, $ftpServer, $ftpUser, $ftpPassword);
        if(!$ftpConn) return 1;
      }

      $remoteFile = $remoteDir.DIRECTORY_SEPARATOR.$remoteFilename;
      $destFile = $destDir.DIRECTORY_SEPARATOR.$remoteFilename;

      if($newerThan !== null) {

        $lastModifTimestamp = ftp_mdtm($ftpConn, $remoteFile);

        if($lastModifTimestamp === -1) {
          $output->writeln(sprintf('<error>Error while retreiving "%s" informations... Retrying.</error>', $remoteFile));
          $ftpConn = $this->reconnect($ftpConn, $ftpServer, $ftpUser, $ftpPassword);
          if(!$ftpConn) return 1;
          prev($remoteFiles);
          continue;
        }

        $lastModification = new \DateTime();
        $lastModification->setTimestamp($lastModifTimestamp);

        if($lastModification < $pivotDate) {
          $output->writeln(sprintf(
            '<comment>Remote file "%s" (%s) has not been created/modified within %s.</comment>',
            $remoteFile,
            $lastModification->format('d/M/Y H:i:s'),
            $newerThan->format('%d day(s)'))
          );
          continue;
        }

        $output->writeln(sprintf(
          '<comment>Remote file "%s" (%s) has been created/modified within %s. Downloading...</comment>',
          $remoteFile,
          $lastModification->format('d/M/Y H:i:s'),
          $newerThan->format('%d day(s)'))
        );

      }

      if(file_exists($destFile)) {

        if(!$forceSync) {
          $output->writeln(sprintf('<comment>File "%s" already downloaded. Skipping...</comment>', $remoteFile));
          continue;
        }

        $remoteFileSize = ftp_size($ftpConn, $remoteFile);

        if($remoteFileSize === -1) {
          $output->writeln(sprintf('<error>Error while retreiving "%s" size... Retrying.</error>', $remoteFile));
          $ftpConn = $this->reconnect($ftpConn, $ftpServer, $ftpUser, $ftpPassword);
          if(!$ftpConn) return 1;
          prev($remoteFiles);
          continue;
        }

        $destFileSize = filesize($destFile);

        if($remoteFileSize === $destFileSize) {
          $output->writeln(sprintf('<comment>File "%s" in sync. Skipping...</comment>', $remoteFile));
          continue;
        }

        if(!$dryRun) unlink($destFile);

        $output->writeln(sprintf('<comment>File "%s" not in sync.</comment>', $remoteFile));

      }

      $output->writeln(sprintf('<info>Downloading "%s"...</info>', $remoteFile));

      if(!$dryRun) {
        $result = ftp_get($ftpConn, $destFile, $remoteFile, FTP_BINARY);
        if(!$result) {
          $output->writeln(sprintf('<error>Couldn\'t download "%s". Retrying...</error>', $remoteFile));
          $ftpConn = $this->reconnect($ftpConn, $ftpServer, $ftpUser, $ftpPassword);
          if(!$ftpConn) return 1;
          prev($remoteFiles);
        }
      }

    }

    ftp_close($ftpConn);

    $output->writeln('<info>Done.</info>');

    return 0;

  }

  protected function reconnect($ftpConn, $ftpServer, $ftpUser, $ftpPassword) {
    $this->output->writeln('<comment>Renewing connection...</comment>');
    ftp_close($ftpConn);
    $ftpConn = $this->getFTPConnection($ftpServer, $ftpUser, $ftpPassword);
    if(!$ftpConn) return false;
    return $ftpConn;
  }

  protected function getFTPConnection($ftpServer, $ftpUser, $ftpPassword) {

    $ftpConn = ftp_connect($ftpServer);

    if(!$ftpConn) {
      $this->output->writeln(sprintf('<error>Couldn\'t connect to "%s".</error>', $ftpServer));
      return false;
    }

    $this->lastReconnect = time();

    $loginResult = ftp_login($ftpConn, $ftpUser, $ftpPassword);

    if(!$loginResult) {
      $this->output->writeln(sprintf('<error>Couldn\'t login as "%s".</error>', $ftpUser));
      return false;
    }

    $passiveMode = ftp_pasv($ftpConn, true);
    if(!$passiveMode) $this->output->writeln('<comment>Couldn\'t switch to passive mode.</comment>');

    return $ftpConn;

  }

}
