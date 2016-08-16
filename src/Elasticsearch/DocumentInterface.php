<?php

namespace KarambolZocoPlugin\Elasticsearch;

interface DocumentInterface {

  public function getIndex();
  public function getType();
  public function getId();

}
