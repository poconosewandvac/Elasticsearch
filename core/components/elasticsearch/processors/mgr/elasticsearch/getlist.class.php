<?php
class ElasticsearchIndexGetListProcessor extends modProcessor
{
    public $defaultSortField = 'pri';
    public $defaultSortDirection = 'DESC';
    protected $client;

    public function initialize()
    {
        $this->setDefaultProperties([
            'start' => 0,
            'limit' => 20,
            'sort' => $this->defaultSortField,
            'dir' => $this->defaultSortDirection,
            'combo' => false,
            'query' => ''
        ]);

        return parent::initialize();
    }

    public function process()
    {
        $data = $this->getIndices();
        return $this->outputArray($data['results'], $data['total']);
    }

    /**
     * Gets all available indices on the Elastic Search instance.
     *
     * @return array
     */
    public function getIndices()
    {
        if (!$this->client) {
            $path = $this->modx->getOption('elasticsearch.core_path', null, $this->modx->getOption('core_path') . 'components/elasticsearch/') . 'model/elasticsearch/';
            $elasticSearch = $this->modx->getService('elasticsearchservice', 'ElasticsearchService', $path);
            $this->client = $elasticSearch->getClient();
        }

        $searchIndices = $this->client->cat()->indices();

        // Fetching data from external API https://forums.modx.com/thread/99891/getlist-processor-for-external-api-as-opposed-to-database-tables
        $data = [];
        $data['results'] = [];
        $data['total'] = count($searchIndices);

        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));
        $search = $this->getProperty('query');

        $count = 0;
        foreach ($searchIndices as $key => $value) {
            if ($key >= $start) {
                if ($count < $limit) {
                    // Search based on name
                    if ((!empty($search) && strpos($value['index'], $search) !== false) || empty($search)) {
                        array_push($data['results'], $value);
                        $count++;
                    }
                }
            }
            if ($key > $limit) break;
        }

        // Sorting
        if (empty($sortKey = $this->getProperty('sort'))) $sortKey = $this->defaultSortField;
        if (empty($sortDir = $this->getProperty('dir'))) $sortDir = $this->defaultSortDirection;

        if ($sortDir == 'DESC') {
            foreach ($data['results'] as $key => $row) {
                $se[$key] = $row[$sortKey];
            }
            array_multisort((array) $se, SORT_DESC, $data['results']);
        } else {
            foreach ($data['results'] as $key => $row) {
                $se[$key] = $row[$sortKey];
            }
            array_multisort((array) $se, SORT_ASC, $data['results']);
        }

        return $data;
    }
}

return 'ElasticsearchIndexGetListProcessor';