<?php

/**
 * Elasticsearch
 *
 * Copyright 2018 by Tony Klapatch <tony@klapatch.net>
 *
 * @package imgix
 * @license See core/components/elasticsearch/docs/license.txt
 */

$index = $modx->getOption('elasticsearch.resource_index', null, '');
$tvPrefix = $modx->getOption('elasticsearch.tv_prefix', null, 'tv.');

// Load the Elasticsearch service.
$path = $modx->getOption('elasticsearch.core_path', null, MODX_CORE_PATH . 'components/elasticsearch/') . 'model/elasticsearch/';
$elasticSearch = $modx->getService('elasticsearchservice', 'ElasticsearchService', $path);
$client = $elasticSearch->getClient();

// Make sure the index exists
if (!$client->indices()->exists(['index' => $index])) {
    $modx->log(1, '[Elasticsearch] Could not index; index not found.');
}

// Getting the object instead of referencing $resource to strip out unnecessary fields
$page = $modx->getObject('modResource', $id);
if (!$page) {
    $modx->log(1, '[Elasticsearch] Could not index; page not found');
}

switch ($modx->event->name) {
    case 'OnDocFormSave':
        // Index the resource
        $params = [
            'index' => $index,
            'type' => $page->get('class_key'),
            'id' => $page->get('id')
        ];
        $params['body'] = $page->toArray();

        // Add the TVs
        $tvs = $page->getTemplateVars();
        if (!empty($tvs)) {
            foreach ($tvs as $tv) {
                $tvValue = $tv->renderOutput($page->get('id'));
                $params['body'][$tvPrefix . $tv->get('name')] = $tvValue;
            }
        }

        // Send it to Elasticsearch
        $response = $client->index($params);
        break;
}