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

}
