<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

// Load the elasticsearch service
$corePath = $modx->getOption('elasticsearch.core_path', null, MODX_CORE_PATH . 'components/elasticsearch/');
require_once $corePath . 'model/elasticsearch/elasticsearchservice.class.php';
$modx->elasticSearch = new ElasticsearchService($modx);

$path = $modx->getOption('processorsPath', $modx->elasticSearch->config, $corePath . 'processors/');
$modx->request->handleRequest([
    'processors_path' => $path,
    'location' => ''
]);
