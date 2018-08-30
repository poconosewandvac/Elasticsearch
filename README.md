# Elasticsearch for MODX (in development)

Index resources to an Elasticsearch index. Currently indexes content and template variables.

## Current Features

- Index resources on save to a selected index. Uses the modResource class_key as the Elasticsearch type and id for Elasticsearch id
- CMP to view all Elasticsearch indices with the names and statuses (no menu item yet, create a menu item for a=index&namespace=elasticsearch)

## Planned Features

- Front end search form and snippet
- Indexing of third party packages (Tagger tags, Commerce products)
- Custom indexers
- Better CMP with ability to add and edit indices

## Installation

1. Download and install the transport package from "Releases" on Github or modx.com.
2. Configure the system settings `elasticsearch.hosts` (comma delimited IPs of Elasticsearch instances) and `elasticsearch.resource_index` (the index resources will be indexed to).
3. Save a resource, it should now be indexed.

## Developing

Run `composer install` in `core/components/elasticsearch` to download the dependencies. Uses MODX Git Package Management for building and updating the package.