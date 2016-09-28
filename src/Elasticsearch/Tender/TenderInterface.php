<?php

namespace KarambolZocoPlugin\Elasticsearch\Tender;

interface TenderInterface {

  public function getTitle();
  public function getDescription();
  public function getPublicationDate();
  public function getClosingDate();
  public function isClosed();
  public function getPublicationUrl();

  public function isPinned($userId);

}
