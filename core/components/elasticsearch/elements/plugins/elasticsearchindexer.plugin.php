<?php
/**
 * Elasticsearch
 *
 * Copyright 2018 by Tony Klapatch <tony@klapatch.net>
 *
 * @package Elasticsearch
 * @license See core/components/elasticsearch/docs/license.txt
 */

$index = $modx->getOption('elasticsearch.resource_index', null, '');
$tvPrefix = $modx->getOption('elasticsearch.tv_prefix', null, 'tv.');

// Load the Elasticsearch service.
$path = $modx->getOption('elasticsearch.core_path', null, MODX_CORE_PATH . 'components/elasticsearch/') . 'model/elasticsearch/';
$elasticSearch = $modx->getService('elasticsearchservice', 'ElasticsearchService', $path);
$client = $elasticSearch->getClient();
if (!$client) {
    return;
}

// Make sure the index exists
if (!$client->indices()->exists(['index' => $index])) {
    $modx->log(MODX_LOG_LEVEL_ERROR, '[Elasticsearch] Could not index; index not found.');
}

$page = $modx->getObject('modResource', $id);
if (!$page) {
    $modx->log(MODX_LOG_LEVEL_ERROR, '[Elasticsearch] Could not index; page not found');
}

$params = [
    'index' => $index,
    'type' => $page->get('class_key'),
    'id' => $page->get('id')
];

switch ($modx->event->name) {
    case 'OnResourceDuplicate':
        $page = $newResource;
    case 'OnResourceUndelete':
    case 'OnDocPublished':
    case 'OnDocFormSave':
        if (!$page->get('searchable')) {
            return;
        }

        // Index the resource
        $params['body'] = $page->toArray();

        // Add the TVs
        $tvs = $page->getTemplateVars();
        if (!empty($tvs)) {
            foreach ($tvs as $tv) {
                $tvValue = $tv->renderOutput($page->get('id'));
                $params['body'][$tvPrefix . $tv->get('name')] = $tvValue;
            }
        }

        $modx->invokeEvent('ElasticsearchBeforeIndex', [
            'index' => $index,
            'tvPrefix' => $tvPrefix,
            'params' => &$params
        ]);

        // Send it to Elasticsearch
        try {
            $results = $client->index($params);
        } catch (Exception $e) {
            $modx->log(MODX_LOG_LEVEL_ERROR, '[Elasticsearch] Could not index; malformed request or Elasticsearch is down.');
            return;
        }

        $modx->invokeEvent('ElasticsearchIndex', [
            'params' => $params,
            'results' => $results
        ]);

        break;

    case 'OnResourceDelete':
    case 'OnDocUnPublished':
    case 'OnDocFormDelete':
        $results = $client->delete($params);
        break;
}