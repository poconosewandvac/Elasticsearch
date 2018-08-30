<?php
class ElasticsearchIndexGetListProcessor extends modProcessor
{
    public function process()
    {
        $update = $this->updateIndex();
        return $this->outputArray()
    }

    public function updateIndex()
    {
        if (!$this->client) {
            $path = $this->modx->getOption('elasticsearch.core_path', null, $this->modx->getOption('core_path') . 'components/elasticsearch/') . 'model/elasticsearch/';
            $elasticSearch = $this->modx->getService('elasticsearchservice', 'ElasticsearchService', $path);
            $this->client = $elasticSearch->getClient();
        }
    }
}