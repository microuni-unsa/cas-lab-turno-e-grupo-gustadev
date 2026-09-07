<div class="p10">
    [{if $ticket_new_url.use_queue != 0}]
    <select id="select_queue" name="queue_name" class="input input-small mr10">
        [{foreach $ticket_new_url.select_queue as $ticket_key => $ticket_data}]
        <option value="[{$ticket_key}]">[{$ticket_data}]</option>
        [{/foreach}]
    </select>
    [{/if}]

    <button id="new_ticket" class="btn">
        <img src="[{$dir_images}]axialis/basic/symbol-add.svg" alt="" /><span>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_NEW"}]</span>
    </button>
</div>
<script type="text/javascript">
    (function () {
        'use strict';

        $('new_ticket').on('click', function () {
            const $selectQueue = $('select_queue');
            let queue_option;

            if ([{$ticket_new_url.use_queue|default:'null'}] != 0 && $selectQueue) {
                queue_option = $selectQueue.getValue();
            }

            window.open('[{$ticket_new_url.url}]' + (queue_option || ''), '_blank');
        });
    })();
</script>
