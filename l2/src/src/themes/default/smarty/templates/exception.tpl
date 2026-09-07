<div id="exception-container" class="box-red blurred-shadow slideDown" style="display:none;">
    <a href="javascript:window.close_exception_box();" class="text-bold text-red">&times;</a>

    <h2>
        <img src="[{$dir_images}]icons/infoicon/error.png" class="vam mr5" />
        <span class="vam">[{$error_topic|default:"i-doit system error"}]</span>
    </h2>

    <div class="m5 p5">
        <h3>[{isys type="lang" ident="LC__UNIVERSAL__MESSAGE"}]</h3>

        <p>[{$g_error|nl2br}]</p>

        [{if is_object($g_error)}]
        <h3>Trace:</h3>
        <pre>[{$g_error->get_last_trace()}]</pre>
        [{/if}]
    </div>
</div>
<script type="text/javascript">
    show_overlay();
    $('exception-container').show();

    window.close_exception_box = function () {
        $('overlay', 'exception-container').invoke('hide');
    }
</script>