<div class="p20">

    <table style="width:700px;">
        <tbody>
        <tr>
            <th style="width:200px;height:35px"></th>
            <td class="td_width">
                <strong class="ml5" style="position:relative;bottom:5px;">[{$main_obj.link}]</strong><br />
                <div class="ml5">
                    <table class="matrixvalues">
                        <tr>
                            <td>RAM: [{isys_convert::formatNumber($main_obj.memory['value'])}] [{$main_obj.memory['unit']}]</td>
                            <td>CPU: [{isys_convert::formatNumber($main_obj.cpu['value'])}] [{$main_obj.cpu['unit']}]</td>
                            <td>
                                DISK: [{isys_convert::formatNumber($main_obj.disc_space['value'])}] [{$main_obj.disc_space['unit']}]
                                <img class="vam" src="images/icons/infobox/blue.png" title="[{isys type="lang" ident="LC__CMDB__CATG__OBJECT_VITALITY__DISK_INFO"}]">
                            </td>
                            <td>LAN: [{isys_convert::formatNumber($main_obj.bandwidth['value'])}] [{$main_obj.bandwidth['unit']}]</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <div id="matrix_scroller" style="overflow:auto;width:700px;max-height:205px;">
        <table style="width:700px;margin-top:0px" cellspacing="0" cellpadding="0">
            [{foreach from=$c_members item="s" key="obj_id"}]
            <tr>
                <th style="width:200px;height:45px">
                    <strong style="position:relative;bottom:5px;">[{$s.link}]</strong><br />
                    <div id="service_detail_[{$service_key}]">
                        ([{isys type="lang" ident=$s.type}])<br />
                    </div>
                </th>
                <td class="td_width">
                    <div class="ml5">
                        <table class="matrixvalues">
                            <tr>
                                <td>RAM: [{isys_convert::formatNumber($s.memory['value'])}] [{$s.memory['unit']}]</td>
                                <td>CPU: [{isys_convert::formatNumber($s.cpu['value'])}] [{$s.cpu['unit']}]</td>
                                <td>DISK: [{isys_convert::formatNumber($s.disc_space['value'])}] [{$s.disc_space['unit']}]</td>
                                <td>LAN: [{isys_convert::formatNumber($s.bandwidth['value'])}] [{$s.bandwidth['unit']}]</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            [{/foreach}]
        </table>
    </div>

    <div style="width:700px; border-bottom:1px solid #787979;"></div>

    <table style="width:700px;" cellspacing="0" cellpadding="0">
        <tr>
            <th style="width:200px;height:35px;">
                <strong>[{isys type="lang" ident="LC__CMDB__CLUSTER_VITALITY__CONSUMPTION"}]</strong>
            </th>
            <td class="td_width">
                <div class="ml5">
                    <table class="matrixvalues">
                        <tr>
                            <td>RAM: [{isys_convert::formatNumber($main_obj.memory_consumption['value'])}] [{$main_obj.memory_consumption['unit']}]</td>
                            <td>CPU: [{isys_convert::formatNumber($main_obj.cpu_consumption['value'])}] [{$main_obj.cpu_consumption['unit']}]</td>
                            <td>DISK: [{isys_convert::formatNumber($main_obj.disc_space_consumption['value'])}] [{$main_obj.disc_space_consumption['unit']}]</td>
                            <td>LAN: [{isys_convert::formatNumber($main_obj.bandwidth_consumption['value'])}] [{$main_obj.bandwidth_consumption['unit']}]</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <th style="width:200px;height:35px;">
                <strong>[{isys type="lang" ident="LC__CMDB__OBJECT_VITALITY__REMAINING_RESOURCES"}]</strong>
            </th>
            <td class="td_width">
                <div class="ml5">
                    <table class="matrixvalues">
                        <tr>
                            <td>
                                [{if $main_obj.memory['value'] > 0}]
                                [{assign var=memory_calc value=$main_obj.memory_rest['value']*100/$main_obj.memory['value']}]
                                [{else}]
                                [{assign var=memory_calc value=0}]
                                [{/if}]

                                <p style="color: [{if $main_obj.memory_rest['value'] <= 0 || $main_obj.memory_rest['negative']}]#e42d2c[{elseif $main_obj.memory_rest['value'] < $main_obj.memory['value']*0.2}]#e57428[{else}]#0ba04a[{/if}]" title="[{isys_convert::formatNumber($memory_calc)}]%">
                                    RAM: [{if $main_obj.memory_rest['negative']}]-[{/if}] [{isys_convert::formatNumber($main_obj.memory_rest['value'])}] [{$main_obj.memory_rest['unit']}]
                                </p>
                            </td>
                            <td>
                                [{if $main_obj.cpu['value'] > 0}]
                                [{assign var=cpu_calc value=$main_obj.cpu_rest['value']*100/$main_obj.cpu['value']}]
                                [{else}]
                                [{assign var=cpu_calc value=0}]
                                [{/if}]

                                <p style="color: [{if $main_obj.cpu_rest['value'] <= 0 || $main_obj.cpu_rest['negative']}]#e42d2c[{elseif $main_obj.cpu_rest['value'] < $main_obj.cpu['value']*0.2}]#e57428[{else}]#0ba04a[{/if}]" title="[{isys_convert::formatNumber($cpu_calc)}]%">
                                    CPU: [{if $main_obj.cpu_rest['negative']}]-[{/if}] [{isys_convert::formatNumber($main_obj.cpu_rest['value'])}] [{$main_obj.cpu_rest['unit']}]<br />
                                </p>
                            </td>
                            <td>
                                [{if $main_obj.disc_space['value'] > 0}]
                                [{assign var=disc_space_calc value=$main_obj.disc_space_rest['value']*100/$main_obj.disc_space['value']}]
                                [{else}]
                                [{assign var=disc_space_calc value=0}]
                                [{/if}]

                                <p style="color: [{if $main_obj.disc_space_rest['value'] <= 0 || $main_obj.disc_space_rest['negative']}]#e42d2c[{elseif $main_obj.disc_space_rest['value'] < $main_obj.disc_space['value']*0.2}]#e57428[{else}]#0ba04a[{/if}]" title="[{isys_convert::formatNumber($disc_space_calc)}]%">
                                    DISK: [{if $main_obj.disc_space_rest['negative']}]-[{/if}] [{isys_convert::formatNumber($main_obj.disc_space_rest['value'])}] [{$main_obj.disc_space_rest['unit']}]<br />
                                </p>
                            </td>
                            <td>
                                [{if $main_obj.bandwidth['value'] > 0}]
                                [{assign var=bandwidth_calc value=$main_obj.bandwidth_rest['value']*100/$main_obj.bandwidth['value']}]
                                [{else}]
                                [{assign var=bandwidth_calc value=0}]
                                [{/if}]

                                <p style="color: [{if $main_obj.bandwidth_rest['value'] <= 0 || $main_obj.bandwidth_rest['negative']}]#e42d2c[{elseif $main_obj.bandwidth_rest['value'] < $main_obj.bandwidth['value']*0.2}]#e57428[{else}]#0ba04a[{/if}]" title="[{isys_convert::formatNumber($bandwidth_calc)}]%">
                                    LAN: [{if $main_obj.bandwidth_rest['negative']}]-[{/if}] [{isys_convert::formatNumber($main_obj.bandwidth_rest['value'])}] [{$main_obj.bandwidth['unit']}]<br />
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>
