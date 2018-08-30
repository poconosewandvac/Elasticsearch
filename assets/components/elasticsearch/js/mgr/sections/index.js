Ext.onReady(function() {
    MODx.load({ xtype: 'elasticsearch-page-home'});
});
Elasticsearch.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'elasticsearch-panel-home'
            ,renderTo: 'elasticsearch-panel-home-div'
        }]
    });
    Elasticsearch.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(Elasticsearch.page.Home, MODx.Component);
Ext.reg('elasticsearch-page-home', Elasticsearch.page.Home);