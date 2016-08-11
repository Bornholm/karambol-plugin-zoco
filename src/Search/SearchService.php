<?php

namespace KarambolZocoPlugin\Search;

class SearchService {

  use \Karambol\Util\AppAwareTrait;

  /**
   * @var array
   */
  protected $typesMapping = [];

  /**
   * @var string
   */
  protected $index;

  /**
   * @return
   */
  public function getIndex()
  {
    return $this->index;
  }

  /**
   * @param  $index
   *
   * @return static
   */
  public function setIndex($index)
  {
    $this->index = $index;
    return $this;
  }

  /**
   * @param string $entryType
   * @param class $entryClass
   * @return static
   */
  public function registerEntryType($entryType, $entryClass) {
    $this->typesMapping[$entryType] = $entryClass;
    return $this;
  }

  /**
   * @param string $entryType
   * @param class $entryClass
   * @return array
   */
  public function search($params = []) {

    $esClient = $this->app['zoco.elasticsearch_client'];

    $params['index'] = $this->getIndex();

    $results = $esClient->search($params);

    $entries = [];
    if($results && isset($results['hits']['hits'])) {
      foreach($results['hits']['hits'] as $hit) {
        $entries[] = $this->createEntryFromSource($hit['_type'], $hit['_source']);
      }
    }

    return ['raw' => $result, 'entries' => $entries];

  }

  public function fetchEntry($entryType, $entryId) {

    $esClient = $this->app['zoco.elasticsearch_client'];

    $params = [
      'index' => 'zoco',
      'type' => $entryType,
      'id' => $entryId
    ];

    $result = $esClient->get($params);

    $entry = null;
    if($result && isset($result['_source'])) {
      $entry = $this->createEntryFromSource($result['_type'], $result['_source']);
    }

    return ['raw' => $result, 'entry' => $entry];

  }

  public function pin($userId, $entryType, $entryId) {

    $esClient = $this->app['zoco.elasticsearch_client'];

    $params = [
      'index' => $this->getIndex(),
      'id' => $entryId,
      'type' => $entryType,
      'body' => [
        'script' => 'if(!("pins" in ctx._source)) { ctx._source.pins = [] }; if(!(user in ctx._source.pins)) { ctx._source.pins += user }',
        'params' => [
          'user' => $userId
        ]
      ]
    ];

    $result = $esClient->update($params);

    return ['raw' => $result];

  }

  public function unpin($userId, $entryType, $entryId) {

    $esClient = $this->app['zoco.elasticsearch_client'];

    $params = [
      'index' => $this->getIndex(),
      'id' => $entryId,
      'type' => $entryType,
      'body' => [
        'script' => 'if("pins" in ctx._source) { ctx._source.pins -= user }',
        'params' => [
          'user' => $userId
        ]
      ]
    ];

    $result = $esClient->update($params);

    return ['raw' => $result];

  }

  public function fetchPinnedEntries($userId) {

    $esClient = $this->app['zoco.elasticsearch_client'];

    $params = [
      'index' => $this->getIndex(),
      'body' => [
        'filter' => [
          'term' => [
            'pins' => $userId
          ]
        ]
      ]
    ];

    $results = $esClient->search($params);

    $entries = [];
    if($results && isset($results['hits']['hits'])) {
      foreach($results['hits']['hits'] as $hit) {
        $entries[] = $this->createEntryFromSource($hit['_type'], $hit['_source']);
      }
    }

    return ['raw' => $results, 'entries' => $entries];

  }

  protected function createEntryFromSource($entryType, $source) {
    $entryClass = isset($this->typesMapping[$entryType]) ? $this->typesMapping[$entryType] : null;
    if($entryClass !== null) return new $entryClass($source);
    return null;
  }

}
