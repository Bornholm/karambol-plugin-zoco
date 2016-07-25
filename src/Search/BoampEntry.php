<?php

namespace KarambolZocoPlugin\Search;

use Symfony\Component\PropertyAccess\PropertyAccess;

class BoampEntry implements SearchEntryInterface {

  protected $source = null;

  public function __construct($source) {
    $this->source = $source;
  }

  public function getId() {
    return $this->get('[main][GESTION][REFERENCE][IDWEB]');
  }

  public function getType() {
    return 'boamp';
  }

  public function getDescription() {
    return $this->get('[main][DONNEES][OBJET][OBJET_COMPLET]');
  }

  public function getTitle() {
    $title = $this->get('[main][GESTION][INDEXATION][RESUME_OBJET]');
    if(empty($title)) $title = $this->get('[main][DONNEES][OBJET][TITRE_MARCHE]');
    if(empty($title)) $title = $this->getDescription();
    return $title;
  }

  public function getPublicationDate() {
    return $this->get('[main][GESTION][INDEXATION][DATE_PUBLICATION]');
  }

  public function getClosingDate() {
    return $this->get('[main][GESTION][INDEXATION][DATE_LIMITE_REPONSE]');
  }

  public function get($sourcePath) {
    $accessor = PropertyAccess::createPropertyAccessor();
    if(!$accessor->isReadable($this->source, $sourcePath)) return null;
    return $accessor->getValue($this->source, $sourcePath);
  }

}
