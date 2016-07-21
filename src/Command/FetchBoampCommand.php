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
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $this->output = $output;

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

      $remoteFile = $remoteDir.'/'.$remoteFilename;
      $destFile = $destDir.'/'.$remoteFilename;

      if(!file_exists($destFile)) {
        $output->writeln(sprintf('<info>Downloading "%s"...</info>', $remoteFile));
        $result = ftp_get($ftpConn, $destFile, $remoteFile, FTP_BINARY);
        if(!$result) {
          $output->writeln(sprintf('<error>Couldn\'t download "%s". Skipping...</error>', $remoteFile));
          $ftpConn = $this->reconnect($ftpConn, $ftpServer, $ftpUser, $ftpPassword);
          if(!$ftpConn) return 1;
        }
      } else {
        $output->writeln(sprintf('<comment>File "%s" already downloaded. Skipping...</comment>', $remoteFile));
      }

      if(time() - $this->lastReconnect > 30) {
        $ftpConn = $this->reconnect($ftpConn, $ftpServer, $ftpUser, $ftpPassword);
        if(!$ftpConn) return 1;
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
