<?php
require_once dirname(dirname(__FILE__)) . '/model/elasticsearch/elasticsearchservice.class.php';

class ElasticsearchIndexManagerController extends modExtraManagerController
{
    public $client;

    public function initialize()
    {
        $this->client = new ElasticsearchService($this->modx);

        $this->addCss($this->client->config['cssUrl'] . 'mgr.css');
        $this->addJavascript($this->client->config['jsUrl'] . 'mgr/elasticsearch.js');
        $this->addHtml('<script type="text/javascript">
            Ext.onReady(function() {
                Elasticsearch.config = ' . $this->modx->toJSON($this->client->config) . ';
            });
            </script>');

        return parent::initialize();
    }

    public function process(array $scriptProperties = [])
    {
    }

    public function getLanguageTopics()
    {
        return ['elasticsearch:default'];
    }

    public function returnPermissions()
    {
        return true;
    }


    public function getPageTitle()
    {
        return $this->modx->lexicon('elasticsearch.menu_title');
    }

    public function loadCustomCssJs()
    {
        $this->addJavascript($this->client->config['jsUrl'].'mgr/widgets/elasticsearch.grid.js');
        $this->addJavascript($this->client->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->client->config['jsUrl'] . 'mgr/sections/index.js');
    }

    public function getTemplateFile()
    {
        return 'index.tpl';
    }
}