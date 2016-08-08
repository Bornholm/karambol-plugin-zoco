<?php

namespace KarambolZocoPlugin\Search;

interface SearchEntryInterface {

  public function getTitle();
  public function getDescription();
  public function getType();
  public function getId();
  public function getPublicationDate();
  public function getClosingDate();
  public function getPublicationUrl();

}
