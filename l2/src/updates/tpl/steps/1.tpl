<h2>i-doit Update</h2>
<table class="info">
    <colgroup>
        <col width="150" />
    </colgroup>
    <tr>
        <td colspan="2"><h3>Compatibility check</h3></td>
    </tr>
    <tr>
        <td class="key">Operating System:</td>
        <td>[{$g_os.name}]</td>
    </tr>
    <tr>
        <td class="key">version:</td>
        <td>[{$g_os.version}]</td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    [{if $php_version_message}]
        <tr>
            <td class="key" style="vertical-align: top">PHP version</td>
            <td>
                <div style="display: flex; align-items: center">
                    <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" style="margin-right: 5px;" />
                    <span>[{$smarty.const.PHP_VERSION}] (PHP [{$smarty.const.UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED}] recommended)</span>
                </div>
                <p class="box-[{$php_version_message_color}] bold p5">[{$php_version_message}]</p>
            </td>
        </tr>
    [{else}]
        <tr>
            <td class="key" style="vertical-align: top">PHP version</td>
            <td>
                <div style="display: flex; align-items: center">
                    <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" style="margin-right: 5px;" />
                    <span>[{$smarty.const.PHP_VERSION}] (PHP [{$smarty.const.UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED}] recommended)</span>
                </div>
            </td>
        </tr>
    [{/if}]
    [{if $sql_version_error}]
        <tr>
            <td class="key" style="vertical-align: top">[{$dbTitle}] version</td>
            <td>
                <div style="display: flex; align-items: center">
                    <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" style="margin-right: 5px;" />
                    <span>[{$currentDbVersion}] ([{$dbTitle}] [{$recommendedDbVersion}] recommended)</span>
                </div>
                <p class="box-red bold p5">[{$sql_version_error}]</p>
            </td>
        </tr>
    [{else}]
        <tr>
            <td class="key" style="vertical-align: top">[{$dbTitle}] version</td>
            <td>
                <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" style="margin-right: 5px" />
                <span>[{$currentDbVersion}] ([{$dbTitle}] [{$recommendedDbVersion}] recommended)</span>
            </td>
        </tr>
    [{/if}]
    [{if $addon_version_notification}]
        <tr>
            <td class="key">Add-on versions</td>
            <td>
                <p class="box-yellow bold p5">[{$addon_version_notification}]</p>
            </td>
        </tr>
    [{/if}]
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td class="key" style="vertical-align: top;">PHP Settings</td>
        <td>
            <ul>
                [{foreach $php_settings as $setting => $data}]
                    <li>
                        <strong style="float: left">[{$setting}]</strong>
                        <span style="float:left; width:50px;">[{$data.value}]</span>
                        [{if $data.check}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" />
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" />
                            <span class="red">[{$data.message}]</span>
                        [{/if}]
                    </li>
                [{/foreach}]
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td class="key" style="vertical-align: top;">PHP Extensions</td>
        <td>
            <ul>

                [{foreach $dependencies as $dependency => $module}]
                    [{if $dependency == "mysql" && version_compare($smarty.const.PHP_VERSION, '5.6') === 1}]
                        <li>
                            <strong>
                                [{$dependency}] <img src="[{$dir_images}]axialis/basic/button-info.svg" alt="" class="mouse-help" title="Used by [{implode(', ', $module)}]" />
                            </strong>

                            [{if extension_loaded("mysqli")}]
                                <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" /> <span class="green">OK</span>
                            [{else}]
                                <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" /> <span class="red">NOT FOUND</span>
                            [{/if}]
                        </li>
                    [{else}]
                        <li>
                            <strong>
                                [{$dependency}] <img src="[{$dir_images}]axialis/basic/button-info.svg" alt="" class="mouse-help" title="Used by [{implode(', ', $module)}]" />
                            </strong>

                            [{if extension_loaded($dependency)}]
                                <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" /> <span class="green">OK</span>
                            [{else}]
                                <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" /> <span class="red">NOT FOUND</span>
                            [{/if}]
                        </li>
                    [{/if}]
                [{/foreach}]
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td class="key" style="vertical-align: top;">Apache modules</td>
        <td>
            <ul>
                [{foreach $apache_dependencies as $dependency => $module}]
                    [{if $dependency==='mod_rewrite'}]
                    <li>
                        <strong>mod_rewrite
                            <img src="[{$dir_images}]axialis/basic/button-info.svg" alt="" class="mouse-help" title="Used by [{implode(', ', $module)}]" />
                        </strong>

                        <img id="webserver_mod_rewrite_icon" alt="" class="animation-rotate" src="[{$dir_images}]axialis/user-interface/loading.svg" />
                        <span id="webserver_mod_rewrite_status">...</span>
                    </li>
                    [{else}]
                    <li>
                        <strong>[{$dependency}]
                            <img src="[{$dir_images}]axialis/basic/button-info.svg" alt="" class="mouse-help" title="Used by [{implode(', ', $module)}]" />
                        </strong>
                        [{if isys_update::is_webserver_module_installed($dependency)}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" />
                            <span class="green">OK</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" />
                            <span class="red">NOT FOUND</span>
                        [{/if}]
                    </li>
                    [{/if}]
                [{/foreach}]
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="2"><h3>i-doit</h3></td>
    </tr>
    <tr>
        <td class="key">Current version</td>
        <td>[{$g_info.version|default:"<= 0.9"}]</td>
    </tr>
    <tr>
        <td class="key">Current revision</td>
        <td>[{$g_info.revision|default:"<= 2500"}]</td>
    </tr>
</table>

<style type="text/css">
    ul, li {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    li strong {
        clear: both;
        width: 110px;
        display: block;
        float: left;
    }

    li strong img {
        height: 12px;
    }

    li strong,
    li span,
    li img {
        vertical-align: middle;
    }

    li strong,
    li img {
        margin-right: 5px;
    }

    .mouse-help {
        cursor: help;
    }

    span.green {
        color: #009900;
    }

    span.yellow {
        color: #e57428;
    }

    span.red {
        color: #AA0000;
    }

    .animation-rotate {
        animation-name: animateRotation;
        animation-duration: 750ms;
        animation-timing-function: ease-out;
        animation-iteration-count: infinite;
        visibility: visible !important;
    }
    @keyframes animateRotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>

<script type="text/javascript">
    (function () {
        'use strict';

        const $rewriteIcon = $('webserver_mod_rewrite_icon');
        const $rewriteSpan = $('webserver_mod_rewrite_status');

        if ($rewriteIcon && $rewriteSpan) {
            new Ajax.Request('[{$www_dir}]mod-rewrite-test', {
                parameters: {
                    start: (new Date()).getTime() / 1000
                },
                onComplete: function (xhr) {
                    const isJson = xhr.hasOwnProperty('responseJSON')
                        && !Object.isUndefined(xhr.responseJSON)
                        && xhr.responseJSON !== null
                        && !xhr.responseText.blank();

                    $rewriteIcon.removeClassName('animation-rotate');

                    if (!isJson) {
                        $rewriteIcon.writeAttribute('src', '[{$dir_images}]axialis/basic/symbol-cancel.svg');
                        $rewriteSpan.addClassName('red').update('INPROPER RESPONSE, HTTP STATUS ' + xhr.status);

                        return;
                    }

                    $rewriteSpan.writeAttribute('title', xhr.responseJSON.message);

                    if (xhr.status === 200) {
                        $rewriteIcon.writeAttribute('src', '[{$dir_images}]axialis/basic/symbol-ok.svg');
                        $rewriteSpan.addClassName('green').update('ACTIVE');
                    } else if (xhr.status === 404) {
                        $rewriteIcon.writeAttribute('src', '[{$dir_images}]axialis/basic/symbol-cancel.svg');
                        $rewriteSpan.addClassName('red').update('NOT ACTIVE');
                    } else {
                        $rewriteIcon.writeAttribute('src', '[{$dir_images}]axialis/basic/warning.svg');
                        $rewriteSpan.addClassName('yellow').update('HTTP STATUS ' + xhr.status);
                    }
                }
            });
        }
    })();
</script>
