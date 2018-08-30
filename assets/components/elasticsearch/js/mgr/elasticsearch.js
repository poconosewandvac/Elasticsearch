var Elasticsearch = function (config) {
    config = config || {};
    Elasticsearch.superclass.constructor.call(this, config);
};
Ext.extend(Elasticsearch, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}
});
Ext.reg('elasticsearch', Elasticsearch);
Elasticsearch = new Elasticsearch();