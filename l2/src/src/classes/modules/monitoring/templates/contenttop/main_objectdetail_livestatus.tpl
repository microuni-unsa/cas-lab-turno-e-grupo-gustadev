<script type="text/javascript">
    $('contentTopTitle')
        .insert(new Element('span', { id: 'monitoring_livestatus_head', className: 'pl15 display-flex align-items-center', title: '[{isys type="lang" ident="LC__CATG__LIVESTATUS__LOADING_STATE"}]' })
            .update(new Element('img', { src: window.dir_images + 'axialis/user-interface/loading.svg', className: 'animation-rotate', alt: '[{isys type="lang" ident="LC__CATG__LIVESTATUS__LOADING_STATE"}]' })));

    new Ajax.Request('?ajax=1&call=monitoring_livestatus&func=load_livestatus_state', {
        parameters: {'[{$smarty.const.C__CMDB__GET__OBJECT}]': '[{$smarty.get.objID|strip_tags|escape}]'},
        method:     'post',
        onComplete: function (transport) {
            try {
                const json = transport.responseJSON;
                const $livestatusElement = $('monitoring_livestatus_head');

                if (json.success) {
                    display_monitoring_state(json.data, $livestatusElement);
                } else {
                    $livestatusElement
                        .addClassName('red')
                        .update(json.message)
                        .writeAttribute('title', null);
                }
            } catch (e) {

            }
        }
    });
</script>
