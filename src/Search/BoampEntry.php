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
    $closingDate = $this->get('[main][GESTION][INDEXATION][DATE_LIMITE_REPONSE]');
    if(empty($closingDate)) $closingDate = $this->get('[main][DONNEES][CONDITION_DELAI][RECEPT_OFFRES]');
    return $closingDate;
  }

  public function getPublicationUrl() {
    $publicationUrl = $this->get('[main][DONNEES][IDENTITE][URL_PARTICIPATION]');
    if(empty($publicationUrl)) $publicationUrl = $this->get('[main][DONNEES][IDENTITE][URL_PROFIL_ACHETEUR]');
    return $publicationUrl;
  }

  public function hasLots() {
    $hasLots = $this->get('[main][DONNEES][OBJET][DIV_EN_LOTS][OUI]');
    return $hasLots !== null;
  }

  public function getLots() {
    $lots = $this->get('[main][DONNEES][OBJET][LOTS][LOT]');
    if(isset($lots['NUM'])) return [$lots];
    return $lots;
  }

  public function get($sourcePath) {
    $accessor = PropertyAccess::createPropertyAccessor();
    if(!$accessor->isReadable($this->source, $sourcePath)) return null;
    return $accessor->getValue($this->source, $sourcePath);
  }

  public function isPinned($userId) {
    $pins = $this->get('[pins]');
    if(!$pins) return false;
    return in_array($userId, $pins);
  }

}
