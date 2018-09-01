<?php
/**
 * Elasticsearch
 *
 * Copyright 2018 by Tony Klapatch <tony@klapatch.net>
 *
 * @package imgix
 * @license See core/components/elasticsearch/docs/license.txt
 */

// System settings
$index = $modx->getOption('elasticsearch.resource_index', null, '');
$tvPrefix = $modx->getOption('elasticsearch.tv_prefix', null, 'tv.');

// Script options
$queryParam = $modx->getOption('queryParam', $scriptProperties, 'query');
$offsetParam = $modx->getOption('offsetParam', $scriptProperties, 'offset');
// $contexts = $modx->getOption('contexts', $scriptProperties, 'contexts');
$minChars = $modx->getOption('minChars', $scriptProperties, 3);
$limit = $modx->getOption('limit', $scriptProperties, 10);
$offset = $modx->getOption($offsetParam, $_GET, 0);
$fields = array_map('trim', explode(',', $modx->getOption($fields, $scriptProperties, 'pagetitle, longtitle, description, alias, introtext, content')));
$query = $modx->getOption($queryParam, $_GET, '');

// Template options
$tpl = $modx->getOption('tpl', $scriptProperties, 'elasticsearch.tpl');
$wrapTpl = $modx->getOption('wrapTpl', $scriptProperties, 'elasticsearch.wrapTpl');
$noResultsTpl = $modx->getOption('noResultsTpl', $scriptProperties, 'elasticsearch.noResultsTpl');
$output = '';

// Load the lexicon
$modx->lexicon->load('elasticsearch:default');

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

// Initialize params
// @todo Make configurable by end user
$params = [
    'size' => $limit,
    'from' => $offset,
    'index' => $index,
    'body' => [
        'query' => [
            'multi_match' => [
                'query' => $query,
                'fields' => $fields
            ]
        ]
    ]
];

$results = $client->search($params);

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

echo '<pre>';
print_r($results);
echo '</pre>';