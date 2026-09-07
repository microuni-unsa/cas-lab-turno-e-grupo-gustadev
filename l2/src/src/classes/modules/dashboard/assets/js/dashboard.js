window.dashboard = {
    /**
     * Method for (re)loading a certain widget.
     * @param  el
     * @param  options
     */
    reload_widget: function (el, options) {
        var hash = (window.location.href.search('lang=') > 0 ? (+new Date()) : "[{if isys_application::instance()->container->get('request')->request->get('login_username')}][{time()}][{/if}]"),
            opts = {
                id:         el.readAttribute('data-id'),
                identifier: el.readAttribute('data-identifier'),
                config:     (el.hasAttribute('data-config') ? el.readAttribute('data-config') : null),
                unique_id:  el.id,
                default:    (window.default_dashboard || 1)
        };

        options = Object.extend(opts, options);

        new Ajax.Request('[{$widget_ajax_url}]&func=load_widget&hash=' + hash, {
            parameters: options,
            method:     'post',
            onSuccess:  function (xhr) {
                const json = xhr.responseJSON;
                let title = '';

                // @see ID-11578 Jump out, if the user navigated somewhere else and the element does not exist anymore.
                if (!$(el.id)) {
                    return;
                }

                if (json) {
                    if (json.success) {
                        el.update(new Element('div', {className: 'widget-body'}).update(json.data.html));
                    } else {
                        el.update(new Element('p', {className: 'p5 m10 box-red'}).update(json.message));
                    }

                    try {
                        title = json.data.options.title;
                    } catch (e) {
                    }

                    this.add_title_bar(el.id, title);

                    // Refresh tooltips
                    $('body').fire('update:tooltips');
                } else {
                    el.update(xhr.responseText);
                }
            }.bind(this)
        });
    },

    /**
     * Method for creating the title-bar (including "edit" and "delete"-buttons).
     * @param  unique_id
     */
    add_title_bar: function (unique_id, forceTitle) {
        var $widgetContainer = $(unique_id),
            $title = new Element('strong'),
            $titleBlock = new Element('div', { className: 'widget-title' })
                .update($title);

        // Don't use '.update()' because it evaluates HTML.
        $title.innerText = forceTitle || $widgetContainer.readAttribute('data-title');

        // Only display the "edit" and "delete" buttons, if we are not displaying the default dashboard.
        if (window.default_dashboard == 0) {
            // Only display the "edit" button, if the widget is configurable and we are not in "default" mode.
            if ($widgetContainer.readAttribute('data-configurable') == 1 && window.is_allowed_to_configure_widgets == 1) {
                $titleBlock
                    .insert(new Element('button', { type: 'button', className: 'btn btn-secondary ml-auto edit', title: '[{isys type="lang" ident="LC__WIDGET__EDIT"}]', 'data-tooltip': 1 })
                        .update(new Element('img', { src: window.dir_images + 'axialis/basic/tool-pencil.svg'})));
            }

            if ($widgetContainer.readAttribute('data-removable') == 1 && window.is_allowed_to_configure_dashboard == 1) {
                const hasEditButton = $titleBlock.down('button');

                $titleBlock
                    .insert(new Element('button', { type: 'button', className: 'btn btn-secondary delete ' + (hasEditButton ? 'ml15' : 'ml-auto'), title: '[{isys type="lang" ident="LC__WIDGET__REMOVE"}]', 'data-tooltip': 1 })
                        .update(new Element('img', { src: window.dir_images + 'axialis/basic/button-remove.svg'})));
            }
        }

        $(unique_id).insert({ top: $titleBlock });
    },

    save_config_and_reload_widget: function (ajax_url, options) {
        // options needs "id", "unique_id" and "config"
        new Ajax.Request(
            ajax_url,
            {
                parameters: options,
                method: 'post',
                onSuccess: function (response) {
                    var json = response.responseJSON;

                    if (json.success) {
                        $(options.unique_id).update(new Element('div', { className: 'widget-body' }).update(json.data.html));
                        window.dashboard.add_title_bar(options.unique_id, json.data.options.title || '');

                        popup_close($('widget-container-popup'));
                    } else {
                        idoit.Notify.error(json.message);
                        popup_close($('widget-container-popup'));
                    }
                }
            }
        );
    }
};
