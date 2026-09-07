<style>
    body {
        padding: 0;
        margin: 0;
        min-height: 100%;
        overflow: hidden;
    }

    iframe {
        overflow: hidden;
        height: 100%;
        width: 100%;
        border: none;
    }

    #contentWrapper {
        top: 0;
    }

    #contentBottom, #contentBottomContent, #scroller {
        overflow: hidden;
    }
</style>
<iframe id="[{$customerPortalIframe}]" src="[{$wwwPath}]/portal/[{$customerPortalSlug}]"></iframe>
<script type="application/javascript">
    var rootPath = '[{$wwwPath}]';
    var customerPortalIframe = "[{$customerPortalIframe}]";
    var customerPortalHost = window.origin;
</script>
<script type="module" crossorigin src="[{$wwwPath}]/src/classes/modules/synetics_admin/js/dist/index.js"></script>
