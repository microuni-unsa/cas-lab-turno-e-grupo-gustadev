<style type="text/css">
    #system-overview td.value {
        padding: 0 20px;
    }
</style>

<div id="system-overview">
    [{if $showLicenseData}]
    <div class="collapse-container border-bottom">
        <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
            <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
                <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
            </button>
            <h2>[{isys type="lang" ident="LC__UNIVERSAL__LICENE_OVERVIEW"}]</h2>
        </div>
        <div class="content-container border-top hide p20">

            <div class="fl mr20" style="width: 40%;">
                <table class="contentTable">
                    <tbody>
                    <tr>
                        <td class="key">[{isys type="lang" ident="LC__LICENCE__DOCUMENTED_OBJECTS"}]</td>
                        <td class="value pl20">
                            [{$stat_counts.objects}] <span class="ml10 text-neutral-600">([{isys type="lang" ident="LC__LICENCE_OVERVIEW__LICENCE_EXCEEDING"}]: <span class="[{$exceeding.objects_class|default:"text-green"}]">[{$exceeding.objects}]</span>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="key">[{isys type="lang" ident="LC__LICENCE__FREE_OBJECTS"}]</td>
                        <td class="value pl20 [{if $stat_counts.free_objects == 0}]text-red[{/if}]">
                            [{$stat_counts.free_objects}] <span class="ml10 text-neutral-600">([{isys type="lang" ident="LC__LICENCE_OVERVIEW__LICENCE_EXCEEDING"}]: <span class="[{$exceeding.objects_class|default:"text-green"}]">[{$exceeding.objects}]</span>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr /></td>
                    </tr>
                    <tr>
                        <td class="key">[{isys type="lang" ident="LC__LICENCE_OVERVIEW__CMDB_REFERENCES"}]</td>
                        <td class="value pl20">[{$stat_counts.cmdb_references}]</td>
                    </tr>
                    <tr>
                        <td class="key">[{isys type="lang" ident="LC__DASHBOAD__LAST_IDOIT_UPDATE"}]</td>
                        <td class="value pl20">[{$stat_stats.last_idoit_update}]</td>
                    </tr>
                    <tr>
                        <td class="key">Version</td>
                        <td class="value pl20">[{$gProductInfo.version}]</td>
                    </tr>
                    </tbody>
                </table>

                [{if isset($note)}]
                <div class="box-green p5 mt10">
                    <span>[{$note}]</span>
                </div>
                [{/if}]

                [{if isset($error)}]
                <div class="box-red p5 mt10">
                    <strong>[{$error}]</strong>
                </div>
                [{/if}]
            </div>

            <div class="fl border" style="width: 30%;">
                <h3 class="p5 pt10 pb10 bg-neutral-200 border-bottom">[{isys type="lang" ident="LC__LICENCE__OBJECT_COUNTER"}]</h3>
                <div style="height: 300px; overflow: auto">
                    <table class="mainTable border-none">
                        <tbody>
                        [{foreach $stat_counts.objects_by_type as $count}]
                            <tr>
                                <td class="pr10" style="text-align: right">[{$count.count}]</td>
                                <td>[{$count.type}]</td>
                            </tr>
                            [{/foreach}]
                        </tbody>
                    </table>
                </div>
            </div>

            <br class="cb" />

            <h3 class="p5 pt10 pb10 bg-neutral-200 mt20 border">Licensed Add-ons</h3>

            [{foreach $licensedAddOns as $addOnKey => $addOn}]
            [{if $addOn.licensed}]
            <div class="fl display-flex align-items-center p5 border border-neutral-200 mt10 mr10 mb10">
                <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" /><span class="ml5">[{$addOn.label}]</span>
            </div>
            [{/if}]
            [{/foreach}]

            <br class="cb" />

            <p class="mt10 mb10">[{$stringTimeLimit}]</p>

            [{if !$licenseUnlimited}]
                [{if $expiresWithinSixMonths}]
                <div class="progress mt10" style="width: 50%;">
                    <div class="progress-bar" data-width-percent="[{$remainingTimePercentage}]" style="width:0; background-color:transparent;"></div>
                </div>
                [{else}]
                <div class="progress mt10" style="width: 50%;">
                    <div class="progress-bar" data-width-percent="5" style="width:0; background-color:transparent;"></div>
                </div>
                [{/if}]

                <script type="text/javascript">
                    (function () {
                        "use strict";

                        idoit.Require.require('smartyProgress', function () {
                            progressBarInit(true);
                        });
                    }());
                </script>
            [{/if}]

        </div>
    </div>
    [{/if}]

    [{if $showSystemInfo}]
    <div class="collapse-container border-bottom">
        <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
            <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
                <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
            </button>
            <h2>System Config Check</h2>
        </div>
        <div class="content-container border-top hide">
            <table class="contentTable p0 mt10 mb10">
                <colgroup>
                    <col width="205" />
                    <col width="350" />
                    <col width="*" />
                </colgroup>
                <tbody>
                <tr>
                    <td class="key">[{isys type="lang" ident="LC__MODULE__SYSTEM__OVERVIEW__TIME_DIFFERENCE"}] PHP/MySQL</td>
                    <td class="value">[{$timeDiffOut.php}] / [{$timeDiffOut.mysql}]</td>
                    <td>
                        [{if $timeDiffOut.diff < 2}]
                    <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                        <span class="vam text-green">OK</span>
                        [{else}]
                    <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="vam" />
                        <span class="vam text-red">FAIL</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_COUNT"}] [{$tenant}]</td>
                    <td class="value">[{$objectCount}]</td>
                    <td></td>
                </tr>
                <tr>
                    <td class="key">Operating System</td>
                    <td class="value">[{$os}]</td>
                    <td>
                        [{if $isWindows}]
                        <p class="mb10 text-yellow"><img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" /> UNIX/Linux Recommended</p>
                        [{/if}]
                        <p class="mb10">[{isys type="lang" ident="LC__MODULE__SYSTEM__OPERATING_SYSTEM_REQUIREMENTS"}]</p>
                        <a href="https://kb.i-doit.com/display/de/Systemvoraussetzungen#Systemvoraussetzungen-Betriebssystem" target="_blank">
                            [{isys type="lang" ident="LC__LOCALE__GERMAN"}] <img src="[{$dir_images}]axialis/basic/link.svg" class="vam" />
                        </a>
                        [{isys type="lang" ident="LC_UNIVERSAL__OR"}]
                        <a href="https://kb.i-doit.com/display/en/System+Requirements#SystemRequirements-OperatingSystem" target="_blank">
                            [{isys type="lang" ident="LC__LOCALE__ENGLISH"}] <img src="[{$dir_images}]axialis/basic/link.svg" class="vam" />
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="key">Architecture</td>
                    <td class="value">[{$systemArchitecture}] bit</td>
                </tr>
                <tr>
                    <td class="key">PHP Version</td>
                    <td class="value">[{$php_version}] <span class="text-neutral-600">([{$php_version_recommended}] recommended)</span>
                        [{if $php_version_status === 'unsupported'}]
                        <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                            Your PHP version is currently not officially supported.
                            Please have a look at the official system requirements in the <a href="https://kb.i-doit.com/display/en/System+Requirements">Knowledge Base</a>.
                        </p>
                        [{elseif $php_version_status === 'failed'}]
                        <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                            Minimum Requirement PHP version is [{$php_version_minimum}].
                            We urgently advise you to update your system to PHP [{$php_version_recommended}],
                            since the PHP version you are using is not supported for any security issues and/or does not get any updates.
                            See <a href="https://php.net/supported-versions.php">https://php.net/supported-versions.php</a> for details.
                            If you need help updating your PHP version, please open a ticket at <a href="https://help.i-doit.com">https://help.i-doit.com</a>, our support team is happy to help you.
                        </p>
                        [{elseif $php_version_status === 'deprecated'}]
                        <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                            We discourage the use of PHP version below [{$php_version_deprecated}].
                            We will drop support for it in a future release.
                            We urgently advise you to update your system to PHP [{$php_version_recommended}],
                            since the PHP version you are using is not supported for any security issues and/or does not get any updates.
                            See <a href="http://php.net/supported-versions.php">http://php.net/supported-versions.php</a> for details.
                            If you need help updating your PHP version, please open a ticket at <a href="https://help.i-doit.com">https://help.i-doit.com</a>, our support team is happy to help you.
                        </p>
                        [{elseif $php_version_status === 'not_recommended'}]
                        <p class="box box-blue p5 text-justify" style="font-size: 0.9em">
                            You are not using the recommended PHP version [{$php_version_recommended}] on your system.
                            We urgently advise you to update your system to PHP [{$php_version_recommended}].
                            See <a href="https://php.net/supported-versions.php">https://php.net/supported-versions.php</a> for details.
                            If you need help updating your PHP version, please open a ticket at <a href="https://help.i-doit.com">https://help.i-doit.com</a>, our support team is happy to help you.
                        </p>
                        [{/if}]
                    </td>
                    <td>
                        [{if $php_version_status === 'ok' || $php_version_status === 'not_recommended'}]
                    <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                        <span class="vam text-green">OK</span>
                        [{elseif $php_version_status === 'unsupported' || $php_version_status === 'failed'}]
                    <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="vam" />
                        <span class="vam text-red">FAIL</span>
                        [{else}]
                    <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                        <span class="vam text-yellow">WARNING</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">i-doit Code Version</td>
                    <td class="value">[{$idoit_version.version}] <span class="text-neutral-600">[{$idoit_version.step}]</span></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="key">[{$db_title}] Version</td>
                    <td class="value">[{$db_version}] <span class="text-neutral-600">([{$db_version_recommended}] recommended)</span>
                        [{if ($db_unsupported_version)}]
                        <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                            <strong>WARNING!</strong>
                            <br/>
                            You have [{$db_title}] [{$db_version}]. You are running i-doit with a [{$db_title}] version that is currently not officially supported.
                            please have a look at the official system requirements in the <a href=\"https://kb.i-doit.com/display/en/System+Requirements\">Knowledge Base</a>
                        </p>
                        [{elseif ($db_deprecated_version)}]
                        <p class="box box-red p5 text-justify" style="font-size: 0.9em">
                            <strong>WARNING!</strong>
                            <br/>
                            We discourage the use of [{$db_title}] version below [{$db_deprecated_version}].
                            We will drop support for it in a future release.
                            Please upgrade [{$db_title}] to one of the stable versions.
                            We recommend version [{$db_version_recommended}] for the best user experience.
                        </p>
                        [{/if}]
                    </td>
                    <td>
                        [{if ($db_discourage_version || $db_deprecated_version)}]
                    <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                        <span class="vam text-yellow">WARNING</span>
                        [{else}]
                    <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                        <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                [{if $idoit_info.version}]
                    <tr>
                        <td class="key">i-doit Database Version</td>
                        <td class="value">[{$idoit_info.version}] <span class="text-neutral-600">Revision [{$idoit_info.revision}]</span></td>
                        <td>
                            [{if $idoit_info.version != $idoit_version.version}]
                        <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="vam" />
                            <strong class="vam text-red">FAIL</strong>
                        <br />
                        DB VERSION DOES NOT MATCH CODE VERSION!
                        <br />
                            UPDATE YOUR CODE OR DATABASE!!
                            [{/if}]
                        </td>
                    </tr>
                    [{/if}]
                <tr>
                    <td class="key">Database size</td>
                    <td class="value">[{$db_size}]</td>
                    <td></td>
                </tr>
                [{if $updateCheckUrl}]
                <tr>
                    <td class="key">Updates</td>
                    <td class="value"><button type="button" class="btn" id="update-check-button">
                            <img src="[{$dir_images}]axialis/web-email/internet-download.svg" /><span>Check for update</span>
                        </button></td>
                    <td>
                        <div id="update-check-result" class="display-flex align-items-center"></div>
                    </td>
                </tr>
                <tr class="hide">
                    <td class="key"></td>
                    <td class="value"></td>
                    <td>
                        <span class="text-neutral-600">
                            You need to extract the downloaded update into your i-doit source directory:<br />
                            <strong>[{$smarty.const.BASE_DIR}]</strong> and then open the <a href="updates/">i-doit update manager</a>.
                        </span>
                    </td>
                </tr>
                [{/if}]
                <tr>
                    <td class="key">Browser (client)</td>
                    <td class="value">
                        <a target="_blank" href="https://kb.i-doit.com/de/installation/systemvoraussetzungen.html">[{isys type="lang" ident="LC__LOCALE__GERMAN"}] <img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>
                        [{isys type="lang" ident="LC_UNIVERSAL__OR"}]
                        <a target="_blank" href="https://kb.i-doit.com/en/installation/system-requirements.html">[{isys type="lang" ident="LC__LOCALE__ENGLISH"}] <img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="key">[{isys type="lang" ident="LC__MODULE__SYSTEM__CONFIGURATION_EXAMPLES"}]</td>
                    <td class="value">
                        <a target="_blank" href="https://kb.i-doit.com/de/installation/manuelle-installation/systemeinstellungen.html">[{isys type="lang" ident="LC__LOCALE__GERMAN"}] <img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>
                        [{isys type="lang" ident="LC_UNIVERSAL__OR"}]
                        <a target="_blank" href="https://kb.i-doit.com/en/installation/manual-installation/system-settings.html">[{isys type="lang" ident="LC__LOCALE__ENGLISH"}] <img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>
                    </td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="collapse-container border-bottom">
        <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
            <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
                <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
            </button>
            <h2>PHP.ini Settings</h2>
        </div>
        <div class="content-container border-top hide">
            <table class="contentTable p0 mt10 mb10">
                <colgroup>
                    <col width="205" />
                    <col width="350" />
                    <col width="*" />
                </colgroup>
                <tbody>
                <tr>
                    <td class="key">max_execution_time</td>
                    <td class="value">
                        [{if $php.max_execution_time > 0}]
                            [{$php.max_execution_time}] s
                        [{else}]
                            infinite
                        [{/if}]
                    </td>
                    <td>
                        [{if $php.max_execution_time < 180 && $php.max_execution_time != 0}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">>180 recommended</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">upload_max_filesize</td>
                    <td class="value">[{$php.upload_max_filesize}]</td>
                    <td>
                        [{if isys_convert::to_bytes($php.upload_max_filesize) < isys_convert::to_bytes('128M')}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">>=128M recommended, 64M OK</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">post_max_size</td>
                    <td class="value">[{$php.post_max_size}]</td>
                    <td>
                        [{if isys_convert::to_bytes($php.post_max_size) < isys_convert::to_bytes('128M')}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">>=128M recommended, 64M OK</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">allow_url_fopen</td>
                    <td class="value">[{$php.allow_url_fopen}]</td>
                    <td>
                        [{if !$php.allow_url_fopen || $php.allow_url_fopen == 'Off'}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">Enable in order to use web requests (used for automatic updates, report browser, etc.)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">max_input_vars</td>
                    <td class="value">[{$php.max_input_vars}]</td>
                    <td>
                        [{if $php.max_input_vars != 0 && intval($php.max_input_vars) < 10000}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">>= 10000</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">file_uploads</td>
                    <td class="value">[{$php.file_uploads}]</td>
                    <td>
                        [{if !$php.file_uploads || $php.file_uploads == 'Off'}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">Enable in order to upload files (https://php.net/file-uploads)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">memory_limit</td>
                    <td class="value">[{$php.memory_limit}]</td>
                    <td>
                        [{if $php.memory_limit != 0 && isys_convert::to_bytes($php.memory_limit) < isys_convert::to_bytes('256M')}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">>=256M recommended</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="collapse-container border-bottom">
        <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
            <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
                <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
            </button>
            <h2>MySQL Settings</h2>
        </div>
        <div class="content-container border-top hide">
            <table class="contentTable p0 mt10 mb10">
                <colgroup>
                    <col width="205" />
                    <col width="350" />
                    <col width="*" />
                </colgroup>
                <tbody>
                <tr>
                    <td class="key">innodb_buffer_pool_size</td>
                    <td class="value">[{$mysql.innodb_buffer_pool_size/1024/1024}] MB</td>
                    <td>
                        [{if $mysql.innodb_buffer_pool_size/1024/1024 < 1024}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">>=1024MB recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/innodb-buffer-pool.html"><img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">max_allowed_packet</td>
                    <td class="value">[{$mysql.max_allowed_packet/1024/1024}] MB</td>
                    <td>
                        [{if $mysql.max_allowed_packet/1024/1024 < 128}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">>=128MB recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.5/en/packet-too-large.html"><img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">tmp_table_size</td>
                    <td class="value">[{$mysql.tmp_table_size/1024/1024}] MB</td>
                    <td>
                        [{if $mysql.tmp_table_size < 33554432}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">32M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#tmp_table_size"><img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]

                        [{if $mysql.tmp_table_size != $mysql.max_heap_table_size}]
                            <br />
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">The value of max_heap_table_size will be used, since it overrides the value of tmp_table_size.</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">max_heap_table_size</td>
                    <td class="value">[{$mysql.max_heap_table_size/1024/1024}] MB</td>
                    <td>
                        [{if $mysql.max_heap_table_size < 33554432}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">32M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_max_heap_table_size"><img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">join_buffer_size</td>
                    <td class="value">[{$mysql.join_buffer_size}] bytes</td>
                    <td>
                        [{if $mysql.join_buffer_size > 262144}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">262144 recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#join_buffer_size"><img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">sort_buffer_size</td>
                    <td class="value">[{$mysql.sort_buffer_size}] bytes</td>
                    <td>
                        [{if $mysql.sort_buffer_size > 262144}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">262144 recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sort_buffer_size"><img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">innodb_sort_buffer_size</td>
                    <td class="value">[{$mysql.innodb_sort_buffer_size/1024/1024}] MB</td>
                    <td>
                        [{if $mysql.innodb_sort_buffer_size/1024/1024 < 64}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">64M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#innodb_sort_buffer_size"><img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key">innodb_log_file_size</td>
                    <td class="value">[{$mysql.innodb_log_file_size/1024/1024}] MB</td>
                    <td>
                        [{if $mysql.innodb_log_file_size/1024/1024 < 512}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">>=512M recommended (<a target="_blank" href="https://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#innodb_log_file_size"><img class="vam" src="[{$dir_images}]axialis/basic/link.svg" /></a>)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                <tr>
                    <td class="key text-neutral-600">datadir</td>
                    <td class="value text-neutral-600">[{$mysql.datadir}]</td>
                    <td>
                        <img src="[{$dir_images}]axialis/basic/button-info.svg" class="vam" />
                        <span class="vam text-green">INFO</span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="collapse-container border-bottom">
        <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
            <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
                <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
            </button>
            <h2>PHP Extension</h2>
        </div>
        <div class="content-container border-top hide">
            <table class="contentTable p0 mt10 mb10">
                <colgroup>
                    <col width="205" />
                    <col width="350" />
                    <col width="*" />
                </colgroup>
                <tbody>
                [{foreach $php_dependencies as $dependency => $modules}]
                    <tr>
                        <td class="key vat">[{$dependency}]</td>
                        <td class="value">[{$modules}]</td>
                        <td>
                            [{if $dependency == "mysql" && version_compare($smarty.const.PHP_VERSION, '5.6') === 1}]
                                [{if extension_loaded("mysqli")}]<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                                    <span class="vam text-green">OK</span>
                                [{else}]<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="vam" />
                                    <span class="vam text-red">ERROR</span>
                                [{/if}]
                            [{else}]
                                [{if extension_loaded($dependency)}]<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                                    <span class="vam text-green">OK</span>
                                [{else}]<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="vam" />
                                    <span class="vam text-red">ERROR</span>
                                [{/if}]
                            [{/if}]
                        </td>
                    </tr>
                [{/foreach}]

                <tr>
                    <td class="key vat">SNMP</td>
                    <td class="value">Core</td>
                    <td>
                        [{if !extension_loaded("snmp")}]
                            <img src="[{$dir_images}]axialis/basic/warning.svg" class="vam" />
                            <span class="vam text-yellow">WARNING</span>
                            <span class="vam"> - Extension needed for SNMP Connections. (Category SNMP or PDU)</span>
                        [{else}]
                            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                            <span class="vam text-green">OK</span>
                        [{/if}]
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="collapse-container border-bottom">
        <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
            <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
                <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
            </button>
            <h2>Apache Modules</h2>
        </div>
        <div class="content-container border-top hide">
            <table class="contentTable p0 mt10 mb10">
                <colgroup>
                    <col width="205" />
                    <col width="350" />
                    <col width="*" />
                </colgroup>
                <tbody>
                <tr>
                    <td class="key">mod_rewrite</td>
                    <td class="value">[{$apache_dependencies.mod_rewrite}]</td>
                    <td id="mod_rewrite_test">
                        <img class="animation-rotate" alt="" src="[{$dir_images}]axialis/user-interface/loading.svg" />
                    </td>
                </tr>
                [{foreach $apache_dependencies as $dependency => $modules}]
                    [{if $dependency !== 'mod_rewrite'}]
                    <tr>
                        <td class="key">[{$dependency}]</td>
                        <td class="value">[{$modules}]</td>
                        <td>
                            [{if isys_core::is_webserver_module_installed($dependency)}]
                                [{if isys_core::is_webserver_module_configured($dependency)}]
                                    <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                                    <span class="vam text-green">OK</span>
                                [{else}]
                                    <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="vam" />
                                    <span class="vam text-red">
                                    Please verify that the apache module "[{$dependency}]" is correctly configured. An automatic check identified that it is not.</span>
                                [{/if}]
                            [{else}]
                                <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="vam" />
                                <span class="vam text-red">ERROR</span>
                            [{/if}]
                        </td>
                    </tr>
                    [{/if}]
                [{/foreach}]
                </tbody>
            </table>
        </div>
    </div>

    <div class="collapse-container border-bottom">
        <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
            <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
                <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
            </button>
            <h2>Rights & Directories</h2>
        </div>
        <div class="content-container border-top hide">
            <table class="contentTable p0 mt10 mb10">
                <colgroup>
                    <col width="205" />
                    <col width="350" />
                    <col width="*" />
                </colgroup>
                <tbody>
                [{foreach $rights as $k => $r}]
                    <tr>
                        <td class="key">[{$k}]</td>
                        <td class="value"><code>[{$r.dir}]</code></td>
                        <td>
                            [{assign var=chk value=$r.chk}]
                            [{if $r.chk}]
                                <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam" />
                                <span class="vam text-green">[{$r.msg}]</span>
                            [{else}]
                                <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="vam" />
                                <span class="vam text-red text-bold">NOT [{$r.msg}]</span>
                                [{if $r.note}]
                                    <br />
                                    <span class="vam text-bold">[{$r.note}]</span>
                                [{/if}]
                            [{/if}]
                        </td>
                    </tr>
                [{/foreach}]
                </tbody>
            </table>
        </div>
    </div>
    [{/if}]
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        const $systemOverview = $('system-overview');
        const $expandAllButton = $('navbar_item_expand-all');
        const $collapseAllButton = $('navbar_item_collapse-all');

        $systemOverview.on('click', '.toggle-header .btn-secondary', function (ev) {
            var $button = ev.findElement('button'),
                $container = $button.up('.collapse-container').down('.content-container').toggleClassName('hide');

            if ($container.hasClassName('hide')) {
                $button.down('img').writeAttribute('src', window.dir_images + 'axialis/user-interface/angle-right-small.svg');
            } else {
                $button.down('img').writeAttribute('src', window.dir_images + 'axialis/user-interface/angle-down-small.svg');
            }
        });

        // Open the first container.
        $systemOverview.down('.toggle-header .btn-secondary').simulate('click');

        // @see ID-11236 Allow user to expand / collapse all sections.
        $expandAllButton.on('click', () => {
            // Only select the currently hidden containers
            const $hiddenDefinitions = $systemOverview.select('.content-container.hide');

            for (let i in $hiddenDefinitions) {
                if (!$hiddenDefinitions.hasOwnProperty(i)) {
                    continue;
                }

                $hiddenDefinitions[i].up('div').down('.toggle-header button').simulate('click');
            }
        });

        $collapseAllButton.on('click', () => {
            // Only select the containers that are currently shown.
            const $hiddenDefinitions = $systemOverview.select('.content-container:not(.hide)');

            for (let i in $hiddenDefinitions) {
                if (!$hiddenDefinitions.hasOwnProperty(i)) {
                    continue;
                }

                $hiddenDefinitions[i].up('div').down('.toggle-header button').simulate('click');
            }
        });

        const $modRewriteColumn = $('mod_rewrite_test');

        if ($modRewriteColumn) {
            new Ajax.Request(window.www_dir + 'mod-rewrite-test', {
                parameters: {
                    start: (new Date()).getTime() / 1000
                },
                onComplete: function (xhr) {
                    const isJson = xhr.hasOwnProperty('responseJSON')
                        && !Object.isUndefined(xhr.responseJSON)
                        && xhr.responseJSON !== null
                        && !xhr.responseText.blank();

                    if (!isJson) {
                        $modRewriteColumn.addClassName('text-red')
                            .update(new Element('img', { className: 'vam', src: window.dir_images + 'axialis/basic/symbol-cancel.svg', alt: '' }))
                            .insert(new Element('span', { className: 'ml5 vam' }).update('INPROPER RESPONSE, HTTP STATUS ' + xhr.status));

                        return;
                    }

                    $modRewriteColumn.writeAttribute('title', xhr.responseJSON.message);

                    if (xhr.status === 200) {
                        $modRewriteColumn.addClassName('text-green')
                            .update(new Element('img', { className: 'vam', src: window.dir_images + 'axialis/basic/symbol-ok.svg', alt: '' }))
                            .insert(new Element('span', { className: 'ml5 vam' }).update('ACTIVE'));
                    } else if (xhr.status === 404) {
                        $modRewriteColumn.addClassName('text-red')
                            .update(new Element('img', { className: 'vam', src: window.dir_images + 'axialis/basic/symbol-cancel.svg', alt: '' }))
                            .insert(new Element('span', { className: 'ml5 vam' }).update('NOT ACTIVE'));
                    } else {
                        $modRewriteColumn.addClassName('text-yellow')
                            .update(new Element('img', { className: 'vam', src: window.dir_images + 'axialis/basic/warning.svg', alt: '' }))
                            .insert(new Element('span', { className: 'ml5 vam' }).update('HTTP STATUS ' + xhr.status));
                    }
                }
            });
        }

        const $updateCheckButton = $('update-check-button')
        const $updateCheckResult = $('update-check-result')

        if ($updateCheckButton && $updateCheckResult) {
            $updateCheckButton.on('click', function () {
                $updateCheckButton
                    .down('img').addClassName('animation-rotate').writeAttribute('src', window.dir_images + 'axialis/user-interface/loading.svg')
                    .next('span').update('Please wait...');

                $updateCheckResult
                    .removeClassName('text-green')
                    .removeClassName('text-blue')
                    .removeClassName('text-red')
                    .update('');

                $updateCheckResult.up('tr').next('tr').addClassName('hide');

                new Ajax.Request('[{$updateCheckUrl}]', {
                    method: 'GET',
                    onFailure: function () {
                        $updateCheckButton
                            .down('img').removeClassName('animation-rotate').writeAttribute('src', window.dir_images + 'axialis/web-email/internet-download.svg')
                            .next('span').update('Check for update');

                        $updateCheckResult
                            .update(new Element('img', { src: window.dir_images + 'axialis/basic/symbol-cancel.svg', className: 'mr5' }))
                            .insert(new Element('span').update('There was an error, please check the logs!'));
                    },
                    onComplete: function (xhr) {
                        var json = xhr.responseJSON;

                        $updateCheckButton
                            .down('img').removeClassName('animation-rotate').writeAttribute('src', window.dir_images + 'axialis/web-email/internet-download.svg')
                            .next('span').update('Check for update');

                        if (json.success) {
                            if (json.data === null) {
                                $updateCheckResult
                                    .addClassName('text-green')
                                    .update(new Element('img', { src: window.dir_images + 'axialis/basic/symbol-ok.svg', className: 'mr5' }))
                                    .insert(new Element('span').update(json.message));
                            } else {
                                $updateCheckResult
                                    .addClassName('text-blue')
                                    .update(new Element('img', { src: window.dir_images + 'axialis/basic/button-info.svg', className: 'mr5' }))
                                    .insert(new Element('span').update(json.message));

                                $updateCheckResult.up('tr').next('tr').removeClassName('hide');
                            }
                        } else {
                            $updateCheckResult
                                .addClassName('text-red')
                                .update(new Element('img', { src: window.dir_images + 'axialis/basic/symbol-cancel.svg', className: 'mr5' }))
                                .insert(new Element('span').update(json.message));
                        }
                    }
                });
            });
        }
    })();
</script>
