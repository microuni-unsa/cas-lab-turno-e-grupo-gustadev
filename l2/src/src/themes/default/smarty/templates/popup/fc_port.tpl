<style type="text/css">
    #fc_port_list {
        height: 320px;
        overflow: auto;
    }

    #fc_port_list label {
        padding: 10px;
        margin-bottom: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    #fc_port_list label:hover {
        background: #e6e7e7; /* neutral-200 */
    }

    #fc_port_list label input {
        margin-right: 10px;
    }
</style>

<div id="fcport_browser">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__BROWSER__TITLE__FC_PORT"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img class="fr mouse-pointer" alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

    <div class="popup-content">
        <div id="fc_port_list" class="border-bottom"></div>

        <div class="p10 display-flex align-items-center">
            [{isys type="f_label" name="fc_port_primary" ident="LC_FC_PORT_POPUP__PRIMARY_PORTS"}]

            <select id="fc_port_primary" name="fc_port_primary" class="input input-small ml20"></select>
        </div>
    </div>

    <div class="popup-footer-ng">
        <button type="button" id="fcport_browser_save" class="btn mr5">
            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt=""/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_SAVE"}]</span>
        </button>
        <button type="button" class="btn popup-closer">
            <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt=""/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
        </button>
    </div>
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        var $container = $('fcport_browser'),
            $fc_port_list = $('fc_port_list'),
            $fc_port_primary = $('fc_port_primary'),
            fc_ports = JSON.parse('[{$fc_ports|escape:"quotes"}]'),
            selection = JSON.parse('[{$fc_ports_selection|escape:"quotes"}]'),
            i;

        $container.select('.popup-closer').invoke('on', 'click', function () {
            popup_close();
        });

        // Observe the checkboxes.
        $container.on('change', 'input', function (ev, $el) {
            var id = parseInt($el.readAttribute('data-id'));

            if ($el.checked) {
                selection.push(id);
            } else {
                selection = selection.without(id)
            }

            refresh_dropdown();
        });

        // Move the selection to the form-fields.
        $('fcport_browser_save').on('click', function () {
            var selection_str = [],
                $returnview = $('[{$viewField}]'),
                $returnhidden = $('[{$hiddenField}]'),
                $returnprimary = $('[{$primField}]');

            var initialPortSelection = $returnhidden.getValue();
            var initialPrimaryPort = $returnprimary.getValue();

            if ($returnview && $returnhidden && $returnprimary) {
                if ($fc_port_primary.getValue() >= 0) {
                    for (i in selection) {
                        if (selection.hasOwnProperty(i)) {
                            selection_str.push(fc_ports[selection[i]].title);
                        }
                    }

                    $returnview.setValue(selection_str.join(', '));
                    $returnhidden.setValue(selection.join(','));
                    $returnprimary.setValue($fc_port_primary.getValue());
                } else {
                    $returnview.setValue('[{isys type="lang" ident="LC_UNIVERSAL__NONE_SELECTED" p_bHtmlEncode=false}]');
                    $returnhidden.setValue('');
                    $returnprimary.setValue('');
                }
            }

            // Check for changes before updating the view field
            if (initialPortSelection != $returnhidden.getValue() || initialPrimaryPort != $returnprimary.getValue() ) {
                [{if $callback_accept}]
                    [{$callback_accept}]
                [{/if}]
            }

            popup_close();
        });

        // Create all FC port items.
        if (typeof fc_ports == 'object' && Object.keys(fc_ports).length > 0) {
            for (i in fc_ports) {
                if (fc_ports.hasOwnProperty(i)) {
                    $fc_port_list
                        .insert(new Element('label')
                            .update(new Element('input', { type:'checkbox', name:'fc' + i, 'data-id': i, checked: (selection.in_array(parseInt(i))) }))
                            .insert(new Element('span').update(fc_ports[i].title)));
                }
            }
        } else {
            $fc_port_list.update(new Element('p', { className:'p5 m5 box-red' }).update('[{isys type="lang" ident="LC_FC_PORT_POPUP__NO_PORTS"}]'));
        }

        // Handle preselection.
        if (selection.length > 0) {
            refresh_dropdown();
        }

        // Refresh the drop down by the given selection and select the primary path.
        function refresh_dropdown () {
            var i, m;

            $fc_port_primary.update(new Element('option', { value:-1 }).update('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));

            if (selection.length > 0) {
                for (i = 0, m = selection.length; i < m; i++) {
                    $fc_port_primary.insert(new Element('option', { value: selection[i] }).update(fc_ports[selection[i]].title));
                }
            }

            // Preselect the primary path.
            if (!'[{$primary}]'.blank()) {
                $fc_port_primary.setValue('[{$primary}]');
            }

            $fc_port_primary.highlight({ endcolor:'#ffffff', restorecolor:'#ffffff' });
        }
    })();
</script>
