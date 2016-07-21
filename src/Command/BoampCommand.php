<?php

namespace KarambolZocoPlugin\Command;

use Symfony\Component\Console\Command\Command;
use Karambol\KarambolApp;

class BoampCommand extends Command
{

  protected $app;
  protected $options;

  public function __construct(KarambolApp $app, array $options) {
    parent::__construct();
    $this->app = $app;
    $this->options = $options;
  }

  protected function getDataDirectory() {
    $localDataDir = $this->options['data_dir'];
    return $localDataDir && $localDataDir[0] === DIRECTORY_SEPARATOR ? $localDataDir : $this->app['app_path']->getPath($localDataDir);
  }

  protected function getRemoteDir($year) {
    return $this->options['ftp']['base_remote_dir'].DIRECTORY_SEPARATOR.$year;
  }


}
