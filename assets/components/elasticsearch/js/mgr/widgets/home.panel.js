Elasticsearch.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        border: false
        , baseCls: 'modx-formpanel'
        , cls: 'container'
        , items: [{
            html: '<h2>' + _('elasticsearch.cmp_title') + '</h2>'
            , border: false
            , cls: 'modx-page-header'
        }, {
            xtype: 'modx-tabs'
            , defaults: { border: false, autoHeight: true }
            , border: true
            , items: [{
                title: _('elasticsearch.cmp_tab_indices')
                , defaults: { autoHeight: true }
                , items: [{
                    html: '<p>' + _('elasticsearch.cmp_desc') + '</p>'
                    , border: false
                    , bodyCssClass: 'panel-desc'
                },{
                    xtype: 'elasticsearch-grid-indices'
                    ,cls: 'main-wrapper'
                    ,preventRender: true
                }]
            }]
            // only to redo the grid layout after the content is rendered
            // to fix overflow components' panels, especially when scroll bar is shown up
            , listeners: {
                'afterrender': function (tabPanel) {
                    tabPanel.doLayout();
                }
            }
        }]
    });
    Elasticsearch.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(Elasticsearch.panel.Home, MODx.Panel);
Ext.reg('elasticsearch-panel-home', Elasticsearch.panel.Home);
