/**
 * i-doit Location tree class.
 * This will display the location tree by a given root ID and provide checkboxes or radio-buttons, according to the "multiselect" setting.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.LocationLinkedTree = Class.create(window.BaseTree, {
    /**
     * Constructor method.
     *
     * @param   $super
     * @param   $container
     * @param   options
     * @returns {Window.LocationTree}
     */
    initialize: function ($super, $container, options) {
        this.options = {
            mode:            'physical',
            considerRights:  false,
            currentObjectId: 0,
            typeMapping:     {}
        };

        options = Object.extend(this.options, options || {});

        $super($container, options);

        return this;
    },

    /**
     * Method for setting the tree "mode" (will only affect the data-loading).
     *
     * @param   mode
     * @returns {Window.LocationTree}
     */
    setMode: function(mode) {
        if (this.options.typeMapping.hasOwnProperty(mode)) {
            // Empty the cache because each mode will show different results.
            this.cache = {};

            this.options.mode = this.options.typeMapping[mode];
        }

        return this;
    },

    /**
     * Method for rendering a node.
     *
     * @param   data
     * @returns {*}
     */
    renderNode: function(data) {
        const open = this.isOpenNode(data.nodeId);
        const isRoot = this.options.rootNodeId == data.nodeId || data.nodeId == 0;
        const selected = this.options.currentObjectId == data.nodeId;
        var $label = new Element('a', { href: '?objID=' + data.nodeId, className: (selected ? 'text-bold' : '')}).update(data.nodeTitle);

        if (isRoot) {
            // @see ID-8934 The root location should not be clickable.
            $label = new Element('span').update(data.nodeTitle);
        }

        return new Element('li', { 'data-id': data.nodeId })
            .update(new Element('img', { src: this.getToggleImage(open), className: 'child-toggle' + (data.hasChildren && !isRoot ? '' : ' hide') }))
            .insert(new Element('div', { className: 'tree-inner' })
                .insert(new Element('img', { src: data.nodeTypeIcon, className: 'mr5', title: data.nodeTypeTitle }))
                .insert($label))
            .insert(new Element('ul', { className: 'css-tree ' + (open ? '' : 'hide') }));
    },

    /**
     * Method for loading children nodes via ajax.
     *
     * @param nodeId
     * @param callback
     */
    loadChildrenNodes: function (nodeId, callback) {
        new Ajax.Request(window.www_dir + 'cmdb/browse-location/' + nodeId, {
            parameters: {
                mode: this.options.mode,
                onlyContainer: 0,
                considerRights: this.options.considerRights ? 1 : 0
            },
            onComplete: function (xhr) {
                var json = xhr.responseJSON, i;

                if (!json.success) {
                    idoit.Notify.error(json.message || xhr.responseText, {sticky: true});
                }

                this.cache['n' + nodeId] = json.data;

                for (i in json.data.children) {
                    if (!json.data.children.hasOwnProperty(i)) {
                        continue;
                    }

                    this.cache['n' + json.data.children[i].nodeId] = json.data.children[i];
                }

                if (Object.isFunction(callback)) {
                    callback(nodeId, this.cache['n' + nodeId]);
                }
            }.bind(this)
        });
    },

    process: function($super) {
        $super();
    }
});
