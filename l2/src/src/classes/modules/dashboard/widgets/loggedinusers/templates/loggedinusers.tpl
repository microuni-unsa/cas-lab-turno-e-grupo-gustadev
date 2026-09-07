<button type="button" class="btn" data-action="reload">
    <img src="[{$dir_images}]axialis/basic/button-update.svg" /><span>[{isys type="lang" ident="LC__UNIVERSAL__REFRESH"}]</span>
</button>

<table class="mainTable">
    <thead>
    <tr>
        <th>[{isys type="lang" ident="LC__CATG__CONTACT_IDOIT_USER"}]</th>
        <th>[{isys type="lang" ident="LC__WIDGET__LOGGED_IN_USERS__LAST_ACTION"}]</th>
    </tr>
    </thead>
    <tbody id="[{$unique_id}]_content">
    [{foreach from=$tabledata item=row}]
        <tr>
            <td>[{$row.title_link}]</td>
            <td>[{$row.last_action}]</td>
        </tr>
        [{/foreach}]
    </tbody>
</table>

<script type="text/javascript">
    const $widget = $('[{$unique_id}]'),
          $button = $widget.down('button[data-action="reload"]');

    $button.on('click', function () {
        $button.down('img')
            .writeAttribute('src', window.dir_images + 'axialis/user-interface/loading.svg')
            .addClassName('animation-rotate');

        // $('[{$unique_id}]_content').hide();

        new Ajax.Updater('[{$unique_id}]_content', '[{$ajax_url}]', {
            method:     'post',
            onComplete: function () {
                $button.down('img')
                    .writeAttribute('src', window.dir_images + 'axialis/basic/button-update.svg')
                    .removeClassName('animation-rotate');

                // new Effect.SlideDown($('[{$unique_id}]_content'), {duration:0.8});
            }
        });
    })
</script>
