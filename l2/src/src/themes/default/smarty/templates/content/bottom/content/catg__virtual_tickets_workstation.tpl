<h2 class="p10 bg-neutral-200 border-top border-bottom"><span class="black">[{$workstation.object_title}] ([{$workstation.object_type}])</span></h2>
<div class="p10">
    <table class="mainTable" id="tickets_table" cellpadding="0" cellspacing="0">
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 50%;">
        </colgroup>
        <thead>
        <tr>
            <th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_SUBJECT"}]</th>
            <th>URL</th>
        </tr>
        </thead>
        <tbody>
        [{if (is_array($workstation.tickets)) && count($workstation.tickets) > 0}]
            [{foreach from=$workstation.tickets key="ticket_id" item="ticket"}]
            [{if ($ticket_id > 0)}]
            <tr class="listRow">
                <td>[{$ticket.subject}]</td>
                <td>
                    <a href="[{$ticket.link}]" class="btn" target="_blank">
                        <img src="[{$dir_images}]axialis/basic/link.svg" alt="" /><span>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__OPEN_TTS"}]</span>
                    </a>
                </td>
            </tr>
            [{/if}]
            [{/foreach}]
            [{else}]
            <tr class="no_tickets">
                <td colspan="2">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__NO_TICKETS_FOR_OBJECT"}]</td>
            </tr>
        [{/if}]
        </tbody>
    </table>
</div>
[{foreach from=$workstation.components key="ticket" item="ticket_object"}]
<h2 class="p10 bg-neutral-200 border-top border-bottom"><a class="black" href="?objID=[{$ticket_object.object_id}]">[{$ticket_object.object_title}] ([{$ticket_object.object_type}])</a></h2>
<div class="p10">
    <table class="mainTable" id="tickets_table" cellpadding="0" cellspacing="0">
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 50%;">
        </colgroup>
        <thead>
        <tr>
            <th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_SUBJECT"}]</th>
            <th>URL</th>
        </tr>
        </thead>
        <tbody>
        [{if (is_array($ticket_object.tickets)) && count($ticket_object.tickets) > 0}]
            [{foreach from=$ticket_object.tickets key="ticket_id" item="ticket"}]
            [{if ($ticket_id > 0)}]
            <tr class="listRow">
                <td>[{$ticket.subject}]</td>
                <td>
                    <a href="[{$ticket.link}]" class="btn" target="_blank">
                        <img src="[{$dir_images}]icons/silk/link.png" class="mr5" />
                        <span>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__OPEN_TTS"}]</span>
                    </a>
                </td>
            </tr>
            <tr style="display:none;">
                <td colspan="2"></td>
            </tr>
            [{/if}]
            [{/foreach}]
            [{else}]
            <tr class="no_tickets">
                <td colspan="2">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__NO_TICKETS_FOR_OBJECT"}]</td>
            </tr>
            [{/if}]
        </tbody>
    </table>
</div>
[{/foreach}]
