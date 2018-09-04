# Elasticsearch for MODX

An event driven Elasticsearch extra for MODX wrapping around the Elasticsearch PHP SDK. Still very much in development and **not recommended for production use** (yet). Designed to be similar to SimpleSearch, but offer more features and customization.

## Current Features

- Index resources and their template variables on save to a selected index. Uses the modResource class_key as the Elasticsearch type and id for Elasticsearch id
- Basic configurable search form for resources
- CMP to view all Elasticsearch indices with the names and statuses (no menu item yet, create a menu item for a=index&namespace=elasticsearch)

## Planned Features

- Better and more customizable front end search form and snippet
- Search pagination
- Bulk indexing tool
- Better CMP with ability to add and edit indices

## Installation

1. Install and configure [Elasticsearch](https://www.elastic.co/products/elasticsearch) on a server thats accessible from your web server.
2. Install the Elasticsearch transport package from [modx.com](https://modx.com/extras/package/elasticsearch).
3. Configure the system setting `elasticsearch.hosts`. This setting is a [JSON array](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html) containing the host and port. By default this is set to 127.0.0.1 (with the assumed default port of 9200).
4. Configure the `elasticsearch.resource_index` that will be used to store the indexed resources. You can also optionally (but recommended) use a mapping for your index by creating the index within Elasticsearch with your [mapping configuration](https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html).

Now your installation should be set up. To test it, try saving a resource and checking the error log. If there are no errors after saving a resource, you should be good to go.

## Search Form

A configurable search form is included with the package to search indexed resources. Include the search form by creating a search page and putting the snippet (make sure it is uncached!) `[[!elasticsearch]]` on the page.

By default, the search form conducts a multi_match search on the pagetitle, longtitle, description, alias, introtext, and content fields.

### Options

- fields: Comma delimited list of fields to search from. Make sure to include the tv prefix if searching template variables. Supports boosting fields with the ^ operator after the field name. For example, the pagetitle field by default has a boost of ^5.0.
- minChars: Minimum characters to allow a search. Defaults to 3.
- limit: Limit per page. Defaults to 10.
- deleted: Search deleted resources. Defaults to 0.
- unpublished: Search unpublished resources. Defaults to 0.
- unsearchable: Search resources with the searchable option turned off. Defaults to 0.
- queryParam: The parameter to use for the search field. Defaults to query.
- offsetParam: The parameter to use to offset the search results. Defaults to offset.

### Events

If you need to modify the search form, there are two custom events you can hook into.

#### ElasticsearchBeforeSearch

Runs prior to when the query is passed to Elasticsearch. Includes the parameters index, query, fields, and params. The params field is passed by reference. For example, if you had an event that needed to increase the limit of the query to 1 if the query is equal to "lorem".

```PHP
if ($query === 'lorem') {
    $params['size'] = 1;
}
```

Because params is passed by reference, any changes to the params array will be passed to the Elasticsearch query.

#### ElasticsearchSearch

Runs after the query is **successfully** completed. Includes the parameters index, query, fields, params, and results. The results field is passed by reference.

This event could be used if you wanted to modify the results array before being parsed into chunks or if you wanted to log the queries somewhere.

## Indexer

A configurable indexer is included with the package to index resources. The indexer runs on the following events:

- OnResourceDuplicate
- OnResourceUndelete
- OnDocPublished
- OnDocFormSave
- OnResourceDelete
- OnDocUnPublished
- OnDocFormDeleted

### Events

If you need to modify whats indexed, there are two custom events you can hook into.

#### ElasticsearchBeforeIndex

Runs before the resource is sent to be indexed. Includes the parameters index, tvPrefix, and params. The params field is passed by reference.

You can use this event to add additional fields to be indexed. For example, if you wanted to add a field labeled color that has the value green:

```PHP
$params['body']['color'] = 'green';
```

This will put the field into the Elasticsearch index. This is especially useful if you wanted to index Tagger tags or Commerce products.

#### ElasticsearchIndex

Runs after the index is **successfully** completed. Includes the parameters params and results.

## Using the Elasticsearch PHP SDK

If you need to use the Elasticsearch PHP SDK in a custom snippet or extra, you can include it by loading the service class:

```PHP
$path = $modx->getOption('elasticsearch.core_path', null, MODX_CORE_PATH . 'components/elasticsearch/') . 'model/elasticsearch/';
$elasticSearch = $modx->getService('elasticsearchservice', 'ElasticsearchService', $path);
$client = $elasticSearch->getClient();
```

Note that the getClient method returns either the client or false. If it returns false, it could not successfully connect to Elasticsearch.