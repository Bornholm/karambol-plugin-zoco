<?php

namespace KarambolZocoPlugin\Elasticsearch;

class ElasticsearchResult implements \IteratorAggregate {

  /**
   * @var integer
   */
  protected $total;

  /**
   * @var array
   */
  protected $documents;

  public function __construct(array $documents, $total) {
    $this->documents = $documents;
    $this->total = $total;
  }

  public function getTotal() {
    return $this->total;
  }

  public function getDocuments() {
    return $this->documents;
  }

  public function getIterator() {
    return new \ArrayIterator($this->getDocuments());
  }

}
