# Elasticsearch for MODX (in development)

Index resources to an Elasticsearch index. Out of the box it indexes modResource and template variables.

## Current Features

- Index resources on save to a selected index. Uses the modResource class_key as the Elasticsearch type and id for Elasticsearch id
- Basic search form for resources
- CMP to view all Elasticsearch indices with the names and statuses (no menu item yet, create a menu item for a=index&namespace=elasticsearch)

## Planned Features

- Better and more customizable front end search form and snippet
- Bulk indexing tool
- Indexing of third party packages (Tagger tags, Commerce products)
- Custom indexers
- Better CMP with ability to add and edit indices

## Search Form

A basic search form with a `multi_match` on `pagetitle, longtitle, description, alias, introtext, content` is included with the package. You can search more fields by adding them to the `fields` list parameter, or TVs with the prefix "tv.". You can adjust the limit per page with the `limit` snippet parameter which defaults to 10. The templates `tpl` (search result row), `wrapTpl` (container for results), and `noResultsTpl` (shows when 0 results) can also be changed.

## Using Elasticsearch in a snippet

You can use the Elasticsearch library in a custom snippet or package by including the Elasticsearch service class and running the getClient method.

```PHP
$path = $modx->getOption('elasticsearch.core_path', null, MODX_CORE_PATH . 'components/elasticsearch/') . 'model/elasticsearch/';
$elasticSearch = $modx->getService('elasticsearchservice', 'ElasticsearchService', $path);
$client = $elasticSearch->getClient();
```

[Read the Elasticsearch PHP documentation](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html).

## Installation

0. Install and configure Elasticsearch to be accessible from your web server.
1. Download and install the transport package from "Releases" on Github or modx.com.
2. Configure the system settings `elasticsearch.hosts` (comma delimited IPs of Elasticsearch instances) and `elasticsearch.resource_index` (the index resources will be indexed to).
3. Save a resource, it should now be indexed. Test it by adding the `[[!elasticsearch]]` snippet to a page and using a form to submit a GET request to it with the query parameter with the search term.

## Developing

Run `composer install` in `core/components/elasticsearch` to download the dependencies. Uses MODX Git Package Management for building and updating the package.