Elasticsearch.grid.Indices = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'elasticsearch-grid-indices'
        , url: Elasticsearch.config.connectorUrl
        , baseParams: { action: 'mgr/elasticsearch/getList' }
        // , fields: ['health', 'status', 'index', 'pri', 'docs.count', 'store.size']
        , fields: ['health', 'status', 'index', 'pri']
        , paging: true
        , remoteSort: true
        , anchor: '97%'
        , autoExpandColumn: 'name'
        , columns: [{
            header: _('elasticsearch.cmp_col_health')
            , dataIndex: 'health'
            , sortable: true
            , width: 60
        }, {
            header: _('elasticsearch.cmp_col_status')
            , dataIndex: 'status'
            , sortable: true
            , width: 60
        }, {
            header: _('elasticsearch.cmp_col_index')
            , dataIndex: 'index'
            , sortable: false
            , width: 120
        }, {
            header: _('elasticsearch.cmp_col_pri')
            , dataIndex: 'pri'
            , sortable: false
            , width: 60
        }, /* {
            header: _('elasticsearch.cmp_col_doc_count')
            , dataIndex: 'docs.count'
            , sortable: false
            , width: 60
        }, {
            header: _('elasticsearch.cmp_col_store_size')
            , dataIndex: 'store.size'
            , sortable: false
            , width: 60
        }*/]
        , tbar: [{
            xtype: 'textfield'
            , id: 'elasticsearch-search-filter'
            , emptyText: _('elasticsearch.cmp_search_placeholder')
            , listeners: {
                'change': { fn: this.search, scope: this }
                , 'render': {
                    fn: function (cmp) {
                        new Ext.KeyMap(cmp.getEl(), {
                            key: Ext.EventObject.ENTER
                            , fn: function () {
                                this.fireEvent('change', this);
                                this.blur();
                                return true;
                            }
                            , scope: cmp
                        });
                    }, scope: this
                }
            }
        }]
        /* , getMenu: function () {
            return [{
                text: _('elasticsearch.update')
                , handler: this.updateIndex
            }, '-', {
                text: _('elasticsearch.delete')
                , handler: this.removeIndex
            }];
        } */
        , updateIndex: function (btn, e) {
            e.preventDefault();
            if (!this.updateIndexWindow) {
                this.updateIndexWindow = MODx.load({
                    xtype: 'elasticsearch-window-index-update'
                    , record: this.menu.record
                    , listeners: {
                        'success': { fn: this.refresh, scope: this }
                    }
                });
            }
            this.updateIndexWindow.setValues(this.menu.record);
            this.updateIndexWindow.show(e.target);
        }
    });
    Elasticsearch.grid.Indices.superclass.constructor.call(this, config)
};
Ext.extend(Elasticsearch.grid.Indices, MODx.grid.Grid, {
    search: function (tf, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
});
Ext.reg('elasticsearch-grid-indices', Elasticsearch.grid.Indices);

// Update window
Elasticsearch.window.UpdateIndex = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('elasticsearch.cmp_update_index')
        , url: Elasticsearch.config.connectorUrl
        , baseParams: {
            action: 'mgr/elasticsearch/update'
        }
        , fields: [{
            xtype: 'textarea'
            , fieldLabel: _('elasticsearch.cmp_params_label')
            , name: 'params'
            , anchor: '100%'
        }]
    });
    Elasticsearch.window.UpdateIndex.superclass.constructor.call(this, config);
};
Ext.extend(Elasticsearch.window.UpdateIndex, MODx.Window);
Ext.reg('elasticsearch-window-index-update', Elasticsearch.window.UpdateIndex);