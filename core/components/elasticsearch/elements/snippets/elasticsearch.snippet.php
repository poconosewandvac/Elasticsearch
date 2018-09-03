<?php
/**
 * Elasticsearch
 *
 * Copyright 2018 by Tony Klapatch <tony@klapatch.net>
 *
 * @package Elasticsearch
 * @license See core/components/elasticsearch/docs/license.txt
 */

// System settings
$index = $modx->getOption('elasticsearch.resource_index', null, '');
$tvPrefix = $modx->getOption('elasticsearch.tv_prefix', null, 'tv.');

// Script options
$queryParam = $modx->getOption('queryParam', $scriptProperties, 'query');
$offsetParam = $modx->getOption('offsetParam', $scriptProperties, 'offset');
// @todo $contexts = $modx->getOption('contexts', $scriptProperties, 'contexts');
$minChars = $modx->getOption('minChars', $scriptProperties, 3);
// @todo $parents = array_map('trim', explode(',', $modx->getOption('parents', $scriptProperties, '0')));
// @todo, for parents $depth = $modx->getOption('depth', $scriptProperties, 10);
$deleted = (bool)$modx->getOption('deleted', $scriptProperties, 0);
$unpublished = (bool)$modx->getOption('unpublished', $scriptProperties, 0);
$unsearchable = (bool)$modx->getOption('unsearchable', $scriptProperties, 0);
$limit = $modx->getOption('limit', $scriptProperties, 10);
$offset = $modx->getOption($offsetParam, $_GET, 0);
$fields = array_map('trim', explode(',', $modx->getOption($fields, $scriptProperties, 'pagetitle^5.0, longtitle^4.0, description^3.0, alias^4.0, introtext^2.0, content^1.0')));
$query = $modx->getOption($queryParam, $_GET, '');

// Template options
$tpl = $modx->getOption('tpl', $scriptProperties, 'elasticsearch.tpl');
$wrapTpl = $modx->getOption('wrapTpl', $scriptProperties, 'elasticsearch.wrapTpl');
$noResultsTpl = $modx->getOption('noResultsTpl', $scriptProperties, 'elasticsearch.noResultsTpl');
$output = '';

// Load the lexicon
$modx->lexicon->load('elasticsearch:default');

// Preliminary checks
if (!$query) {
    return $modx->getChunk($noResultsTpl, [
        'query' => htmlentities($query),
        'reason' => $modx->lexicon('elasticsearch.frontend_no_query')
    ]);
}

if (strlen($query) < $minChars) {
    return $modx->getChunk($noResultsTpl, [
        'query' => htmlentities($query),
        'reason' => $modx->lexicon('elasticsearch.frontend_query_not_long_enough', ['minChars' => $minChars])
    ]);
}

// Load the Elasticsearch service.
$path = $modx->getOption('elasticsearch.core_path', null, MODX_CORE_PATH . 'components/elasticsearch/') . 'model/elasticsearch/';
$elasticSearch = $modx->getService('elasticsearchservice', 'ElasticsearchService', $path);
$client = $elasticSearch->getClient();
if (!$client) {
    return;
}

// Initialize params
$params = [
    'size' => $limit,
    'from' => $offset,
    'index' => $index,
    'body' => [
        'query' => [
            'bool' => [
                'must' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => $fields
                    ]
                ],
                'filter' => []
            ]
        ]
    ]
];

// Fetching filters
if (!$deleted) {
    $params['body']['query']['bool']['filter'][] = ['term' => ['deleted' => false]];
}
if (!$unpublished) {
    $params['body']['query']['bool']['filter'][] = ['term' => ['published' => true]];
}
if (!$unsearchable) {
    $params['body']['query']['bool']['filter'][] = ['term' => ['searchable' => true]];
}

$modx->invokeEvent('ElasticsearchBeforeSearch', [
    'index' => $index,
    'query' => $query,
    'fields' => $fields,
    'params' => &$params
]);

try {
    $results = $client->search($params);
} catch (Exception $e) {
    $modx->log(MODX_LOG_LEVEL_ERROR, '[Elasticsearch] An error occured trying to search. This could be the result of a malformed request or Elasticsearch is down.');
    return 'Could not get search results.';
}

$modx->invokeEvent('ElasticsearchSearch', [
    'index' => $index,
    'query' => $query,
    'fields' => $fields,
    'params' => $params,
    'results' => &$results
]);

// Check if there are no results
if ($results['hits']['total'] === 0)  {
    return $modx->getChunk($noResultsTpl, [
        'query' => htmlentities($query),
        'reason' => $modx->lexicon('elasticsearch.frontend_no_results', ['query' => htmlentities($query)])
    ]);
}

foreach ($results['hits']['hits'] as $result) {
    $output .= $modx->getChunk($tpl, $result);
}

return $modx->getChunk($wrapTpl, [
    'query' => htmlentities($query),
    'took' => $results['took'],
    'timed_out' => $results['timed_out'],
    'total' => $results['hits']['total'],
    'max_score' => $results['hits']['max_score'],
    'output' => $output,
]);

/* echo '<pre>';
print_r($results);
echo '</pre>'; */