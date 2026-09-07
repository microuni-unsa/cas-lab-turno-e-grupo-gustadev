<table class="w100 border-bottom" style="table-layout: fixed">
    <colgroup>
        <col width="49%" />
        <col width="2%" />
        <col width="49%" />
    </colgroup>
    <tr>
        <td><h3 class="m5">[{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__FRONT"}]</h3></td>
        <td></td>
        <td><h3 class="m5">[{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__BACK"}]</h3></td>
    </tr>
</table>

[{if $inputs->num_rows() > 0}]

    [{while $row = $inputs->get_row()}]
    [{assign var="row" value=$list_dao->modify_row($row)}]
    [{assign var="l_sibling_id" value=$row.isys_catg_connector_list__id}]
    [{assign var="siblings" value=$dao_connector->get_data_by_sibling($l_sibling_id, $smarty.session.cRecStatusListView, $sortField, $sortDirection)}]

    <div class="connector p10">
        <table id="inputs" class="w100" style="table-layout: fixed">
            <colgroup>
                <col width="49%" />
                <col width="2%" />
                <col width="49%" />
            </colgroup>
            <tr>
                <td class="border" style="vertical-align: top;">
                    <div style="overflow-x: scroll">
                        <table class="mainTable">
                            <thead>
                            <tr>
                                <th><input class="check_input" type="checkbox" onClick="CheckAllBoxes(this, 'check_input');" value="X" /></th>

                                [{foreach $list_dao->get_fields() as $header_key => $header}]
                                <th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
                                    <a href="javascript:" onclick="$('dir').setValue('[{$list_dao->get_order()}]'); $('sort').setValue('[{$header_key}]'); form_submit();">
                                        [{isys type="lang" ident=$header}]
                                    </a>
                                </th>
                                [{/foreach}]
                            </tr>
                            </thead>
                            <tbody>
                            <tr data-connector-id="[{$row.isys_catg_connector_list__id}]">
                                <td><input type="checkbox" class="checkbox check_input" name="id[]" value="[{$row.isys_catg_connector_list__id}]" /></td>

                                [{foreach $list_dao->get_fields() as $header_key => $header}]
                                <td data-link="[{$conn_link}]&cateID=[{$row.isys_catg_connector_list__id}]">[{$row.$header_key}]</td>
                                [{/foreach}]
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>

                <td>
                    <div class="dash"></div>
                </td>

                <td class="border" style="background-color: #ccc; vertical-align: top;">
                    [{if $siblings->num_rows() > 0}]
                    <div style="overflow-x: scroll">
                        <table class="mainTable">
                            <thead>
                            <tr>
                                <th><input class="check_output" type="checkbox" onClick="CheckAllBoxes(this, 'check_output');" value="X" /></th>

                                [{foreach $list_dao->get_fields() as $header_key => $header}]
                                <th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
                                    <a href="javascript:" onclick="$('dir').setValue('[{$list_dao->get_order()}]'); $('sort').setValue('[{$header_key}]'); form_submit();">
                                        [{isys type="lang" ident=$header}]
                                    </a>
                                </th>
                                [{/foreach}]
                            </tr>
                            </thead>
                            <tbody>
                            [{while $sibblingRow = $siblings->get_row()}]
                                [{assign var="sibblingRow" value=$list_dao->modify_row($sibblingRow)}]

                                <tr data-connector-id="[{$sibblingRow.isys_catg_connector_list__id}]">
                                    <td><input type="checkbox" class="checkbox check_output" name="id[]" value="[{$sibblingRow.isys_catg_connector_list__id}]" /></td>

                                    [{foreach $list_dao->get_fields() as $header_key => $header}]
                                    <td data-link="[{$conn_link}]&cateID=[{$sibblingRow.isys_catg_connector_list__id}]">[{$sibblingRow.$header_key}]</td>
                                    [{/foreach}]
                                </tr>
                                [{/while}]
                            </tbody>
                        </table>
                    </div>
                    [{else}]

                    <h3 class="p10">[{isys type="lang" ident="LC__UNIVERSAL__UNASSIGNED"}]</h3>

                    [{/if}]
                </td>
            </tr>
        </table>
    </div>
    [{/while}]
    [{/if}]

[{if $outputs->num_rows() > 0}]
    <div class="connector m10">
        <table id="outputs" class="w100" style="table-layout: fixed">
            <colgroup>
                <col width="49%" />
                <col width="2%" />
                <col width="49%" />
            </colgroup>
            <tr>
                <td class="border" style="background-color: #ccc; vertical-align: top;">
                    <h3 class="p10">[{isys type="lang" ident="LC__UNIVERSAL__UNASSIGNED"}]</h3>
                </td>
                <td>
                    <div class="dash"></div>
                </td>
                <td class="border" style="vertical-align: top;">
                    <div style="overflow-x: scroll">
                        <table class="mainTable">
                            <thead>
                            <tr>
                                <th><input class="check_output" type="checkbox" onClick="CheckAllBoxes(this, 'check_output');" value="X" /></th>

                                [{foreach $list_dao->get_fields() as $header_key => $header}]
                                <th title="[{isys type="lang" ident="LC__UNIVERSAL__SORT"}]">
                                    <a href="javascript:" class="display-flex align-items-center" onclick="$('dir').setValue('[{$list_dao->get_order()}]'); $('sort').setValue('[{$header_key}]'); form_submit();">
                                        <span>[{isys type="lang" ident=$header}]</span>
                                        [{if $smarty.post.sort eq $header_key}]
                                            [{if $sortDirection == 'desc'}]
                                                <img src="/images/axialis/user-interface/arrow-down.svg" class="ml5" alt="v" />
                                            [{else}]
                                                <img src="/images/axialis/user-interface/arrow-up.svg" class="ml5" alt="^" />
                                            [{/if}]
                                        [{/if}]
                                    </a>
                                </th>
                                [{/foreach}]
                            </tr>
                            </thead>
                            <tbody>
                            [{while $row = $outputs->get_row()}]
                                [{assign var="row" value=$list_dao->modify_row($row)}]
                                <tr data-connector-id="[{$row.isys_catg_connector_list__id}]">
                                    <td><input type="checkbox" class="checkbox check_output" name="id[]" value="[{$row.isys_catg_connector_list__id}]" /></td>

                                    [{foreach $list_dao->get_fields() as $header_key => $header}]
                                    <td data-link="[{$conn_link}]&cateID=[{$row.isys_catg_connector_list__id}]">[{$row.$header_key}]</td>
                                    [{/foreach}]
                                </tr>
                                [{/while}]
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    [{/if}]


[{if $inputs->num_rows() <=0 && $outputs->num_rows() <= 0}]

    <h3 class="p10">[{isys type="lang" ident="LC__CATG__CONNECTOR__NO_CONNECTORS"}]</h3>

    [{/if}]

<div class="hide">
    [{isys
    title='LC__POPUP__BROWSER__UI_CON_SELECTION'
    name='C__CATG__CONNECTOR__ASSIGNED_CONNECTOR'
    type='f_popup'
    p_strPopupType='browser_cable_connection_ng'
    secondSelection=true
    secondList='isys_cmdb_dao_category_g_connector::object_browser'
    multiselection=true
    edit_mode=true
    callback_accept="window.createNewConnection();"
    usageWarning="LC__BROWSER_CABLE_CONNECTION__ERROR"}]
</div>

<style>
    .mainTable tr .input-group {
        opacity: 0;
    }

    .mainTable tr:hover .input-group {
        opacity: 1;
    }

    .mainTable .input-group .input-group-addon {
        padding: 1px;
        height: 18px;
        min-width: 18px;
    }

    .mainTable .input-group .input-group-addon img {
        width: 14px;
        height: 14px;
    }

    #scroller {
        overflow-x: auto;
    }
</style>

<script>
    (function () {
        'use strict';

        var currentConnector = 0;

        $('scroller').on('click', 'td[data-link]', function (ev) {
            var $target = ev.findElement('.input-group-addon-clickable'),
                $tr, $connector, connId;

            //  Only follow links, if we didn't click an any function-buttons.
            if (!$target) {
                document.location = ev.findElement('td').readAttribute('data-link');
                return;
            }

            $tr = $target.up('tr');
            connId = $tr.readAttribute('data-connector-id');
            $connector = $tr.down('.connected-connector');

            if ($target.hasClassName('detach')) {
                if ($target.up('.input-group').readAttribute('data-connector-id') == '0') {
                    idoit.Notify.info('[{isys type="lang" ident="LC__CABLE_CONNECTION__NO_CONNECTOR_AVAILABLE"}]', {life: 5});
                    return;
                }

                if (confirm('[{isys type="lang" ident="LC__CABLE_CONNECTION__POPUP_CONNECTION_DISCONNECT_SELECTED_CONNECTOR" p_bHtmlEncode=false}]')) {
                    $connector
                        .update(new Element('img', {
                            src:       window.dir_images + 'ajax-loading.gif',
                            className: 'mr5',
                            style:     'width:12px; height:12px;'
                        }))
                        .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

                    new Ajax.Request('?ajax=1&call=connector&method=detachConnector', {
                        parameters: {
                            connector: connId
                        },
                        method:     'post',
                        onSuccess:  function (xhr) {
                            var json = xhr.responseJSON;

                            if (is_json_response(xhr, true)) {
                                if (json.success) {
                                    $connector.update('[{isys_tenantsettings::get('gui.empty_value', '-')}]');
                                    $tr.down('.cable-name').update();
                                    $target.up('.input-group').writeAttribute('data-connector-id', 0);
                                } else {
                                    idoit.Notify.error(json.message, {sticky: true});
                                }
                            }
                        }
                    });
                }
            } else {
                currentConnector = connId;
                $('C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__HIDDEN').setValue($target.up('.input-group').readAttribute('data-connector-id'));
                $('C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__VIEW').next('button.attach').simulate('click');
            }
        });

        // @see ID-4592 and ID-4647
        window.createNewConnection = function () {
            var connector  = $F('C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__HIDDEN'),
                $tr        = $('scroller').down('tr[data-connector-id="' + currentConnector + '"]'),
                $connector = $tr.down('span.connected-connector');

            if (connector > 0) {
                $connector
                    .update(new Element('img', {
                        src:       window.dir_images + 'ajax-loading.gif',
                        className: 'mr5',
                        style:     'width:12px; height:12px;'
                    }))
                    .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

                new Ajax.Request('?ajax=1&call=connector&method=connectConnectors', {
                    parameters: {
                        a: currentConnector,
                        b: connector
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON;

                        if (!is_json_response(xhr, true)) {
                            return;
                        }

                        if (!json.success) {
                            idoit.Notify.error(json.message, {sticky: true});
                            return;
                        }

                        $connector
                            .update(new Element('a', {
                                id:   'reloaded_' + json.data[0].connId,
                                href: '?objID=' + json.data[0].objId
                            }).update(json.data[0].objTitle + ' &raquo; ' + json.data[0].connTitle))
                            .previous('.input-group').writeAttribute('data-connector-id', json.data[0].connId);

                        new Tip('reloaded_' + json.data[0].connId, '', {
                            ajax:      {url: '?ajax=1&call=quick_info&objID=' + json.data[0].objId},
                            delay:     [{isys_usersettings::get('gui.quickinfo.delay', 0.5)}],
                            className: 'objectinfo'
                        });

                        // Display the connected cable.
                        $tr.down('.cable-name')
                            .update(new Element('a', {
                                id:   'reloaded_c_' + json.data[0].cableId,
                                href: '?objID=' + json.data[0].cableId
                            }).update(json.data[0].cableTitle));

                        new Tip('reloaded_c_' + json.data[0].cableId, '', {
                            ajax:      {url: '?ajax=1&call=quick_info&objID=' + json.data[0].cableId},
                            delay:     [{isys_usersettings::get('gui.quickinfo.delay', 0.5)}],
                            className: 'objectinfo'
                        });
                    }
                });
            }
        };
    })();
</script>
