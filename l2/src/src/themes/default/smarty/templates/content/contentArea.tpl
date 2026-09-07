<ul id="breadcrumb" class="noprint [{if !$showBreadcrumb|default:true}]hide[{/if}]">
    [{isys type="breadcrumb_navi" name="breadcrumb" home=true}]

    [{if $trialInfo}]
        <li class="text-bold text-red">
            [{$trialInfo.message}]
        </li>
    [{/if}]
</ul>

<div id="main_content">
    [{include file=$index_includes.contentarea|default:"content/main.tpl"}]
</div>

<div id="infoBox">
    <div class="message">[{$infobox->show_html()|strip}]</div>
    <div class="version">i-doit [{$gProductInfo.version}] [{$gProductInfo.step}]</div>
</div>
