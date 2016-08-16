<?php

namespace KarambolZocoPlugin\Elasticsearch;

use Elasticsearch\ClientBuilder;

class ElasticsearchService {

  protected $elasticsearchClient;

  public function __construct($clientConfig, $logger = null) {
    $this->elasticsearchClient = $this->createElasticsearchClient($clientConfig, $logger);
  }

  /**
   * @var array
   */
  protected $documentsMapping = [];

  /**
   * @param string $entryType
   * @param class $entryClass
   * @return static
   */
  public function registerDocumentMapping($documentIndex, $documentType, $documentClass) {
    $this->documentsMapping[$documentIndex.'/'.$documentType] = $documentClass;
    return $this;
  }

  /**
   * @return KarambolZocoPlugin\Elasticsearch\ElasticsearchResult
   */
  public function query($params = []) {

    $esClient = $this->elasticsearchClient;

    $rawResults = $esClient->search($params);

    $documents = [];
    if($rawResults && isset($rawResults['hits']['hits'])) {
      foreach($rawResults['hits']['hits'] as $hit) {
        $documents[] = $this->createDocumentFromHit($hit);
      }
    }

    $result = new ElasticsearchResult($documents, $rawResults['hits']['total']);

    return $result;

  }

  public function get($documentIndex, $documentType, $documentId) {

    $esClient = $this->elasticsearchClient;

    $params = [
      'index' => $documentIndex,
      'type' => $documentType,
      'id' => $documentId
    ];

    $rawResult = $esClient->get($params);

    $document = null;
    if($rawResult && isset($rawResult['_source'])) {
      $document = $this->createDocumentFromHit($rawResult);
    }

    return $document;

  }

  public function getClient() {
    return $this->elasticsearchClient;
  }

  public function createDocumentFromHit(array $hit) {
    $mappingKey = $hit['_index'].'/'.$hit['_type'];
    $documentClass = isset($this->documentsMapping[$mappingKey]) ? $this->documentsMapping[$mappingKey] : null;
    if($documentClass === null) return new Document($hit['_index'], $hit['_type'], $hit['_id'], $hit['_source']);
    return new $documentClass($hit['_index'], $hit['_type'], $hit['_id'], $hit['_source']);
  }

  protected function createElasticsearchClient($clientConfig, $logger = null) {
    return ClientBuilder::create()
      ->setHosts($clientConfig['hosts'])
      ->setLogger($logger)
      ->build()
    ;
  }

}
