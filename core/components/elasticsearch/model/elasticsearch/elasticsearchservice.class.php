<?php
require dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

class ElasticsearchService {
    public $modx;
    public $config = [];
    protected $client;

    /**
     * @param \modX $modx
     * @param array $config
     */
    public function __construct(\modX $modx, array $config = [])
    {
        $this->modx = $modx;
        $basePath = $this->modx->getOption('elasticsearch.core_path', $config, $this->modx->getOption('core_path') . 'components/elasticsearch/');
        $assetsUrl = $this->modx->getOption('elasticsearch.assets_url', $config, $this->modx->getOption('assets_url') . 'components/elasticsearch/');

        $this->config = array_merge([
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath . 'model/',
            'processorsPath' => $basePath . 'processors/',
            'templatesPath' => $basePath . 'templates/',
            'chunksPath' => $basePath . 'elements/chunks/',
            'snippetsPath' => $basePath . 'elements/snippets/',
            'pluginsPath' => $basePath . 'elements/plugins/',
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl . 'connector.php',
        ], $config);
    }

    /**
     * Gets the ElasticSearch instance.
     * @return Client|false
     */
    public function getClient()
    {
        if (!$this->client) {
            $hosts = $this->modx->getOption('elasticsearch.hosts', null, '{"host": "127.0.0.1"}');
            $hosts = json_decode($hosts, true);

            $clientBuilder = ClientBuilder::create();
            $clientBuilder->setHosts($hosts);
            $this->client = $clientBuilder->build();

            // Trying pinging the client to see if it exists.
            if (!$this->client->ping()) {
                $this->modx->log(1, '[Elasticsearch] Could not connect to Elasticsearch.');
                return false;
            }
        }

        return $this->client;
    }
}