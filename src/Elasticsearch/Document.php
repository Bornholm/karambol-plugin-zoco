<?php

namespace KarambolZocoPlugin\Elasticsearch;

use Symfony\Component\PropertyAccess\PropertyAccess;

class Document implements DocumentInterface {

  protected $index;
  protected $type;
  protected $id;
  protected $source;

  public function __construct($index, $type, $id, $source = []) {
    $this->index = $index;
    $this->type = $type;
    $this->id = $id;
    $this->source = $source;
  }

  public function getIndex() {
    return $this->index;
  }

  public function getType() {
    return $this->type;
  }

  public function getId() {
    return $this->id;
  }

  public function getSource() {
    return $this->source;
  }

  public function get($sourcePath, $defaultValue = null) {
    $accessor = PropertyAccess::createPropertyAccessor();
    if(!$accessor->isReadable($this->source, $sourcePath)) return $defaultValue;
    return $accessor->getValue($this->source, $sourcePath);
  }

  public function __toString() {
    return sprintf('Document[%s/%s/%s]', $this->getIndex(), $this->getType(), $this->getId());
  }

}
