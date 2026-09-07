<div class="display-block" style="position: absolute; left: 0; top: 52px; right: 0; bottom: 0;">
    <div class="p10 h100 overflow-auto">
        <table class="mainTable" id="tickets_table">
            <thead>
            <tr>
                <th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_SUBJECT"}]</th>
                <th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_QUEUE"}]</th>
                <th>[{isys type="lang" ident="LC__UNIVERSAL__STATUS"}]</th>
                <th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_PRIORITY"}]</th>
                <th>[{isys type="lang" ident="LC__UNIVERSAL__DATE_CREATED"}]</th>
                <th>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_LASTUPDATED"}]</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            [{if is_array($tickets) && count($tickets) > 0}]
                [{foreach $tickets as $ticket_id => $ticket}]
                [{if $ticket_id > 0}]
                <tr class="listRow mouse-pointer [{cycle values="odd,even"}]" data-trigger="ticketDataTemplate_[{$ticket_id}]">
                    <td data-sort="[{$ticket.subjectsort}]">[{$ticket.subject}]</td>
                    <td data-sort="[{$ticket.queue}]">[{$ticket.queue}]</td>
                    <td data-sort="[{$ticket.status}]">[{$ticket.status}]</td>
                    <td data-sort="[{$ticket.priority}]">[{$ticket.priority}]</td>
                    <td data-sort="[{$ticket.created}]">[{$ticket.created}]</td>
                    <td data-sort="[{$ticket.lastupdated}]">[{$ticket.lastupdated}]</td>
                    <td data-sort="[{$ticket.link}]">
                        <a href="[{$ticket.link}]" class="btn" target="_blank">
                            <img src="[{$dir_images}]axialis/basic/link.svg" alt="" /><span>[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__OPEN_TTS"}]</span>
                        </a>
                    </td>
                </tr>
                <tr class="hide"><!-- Create this to restore the 'even / odd' UI --></tr>
                <tr class="hide" id="ticketDataTemplate_[{$ticket_id}]">
                    <td colspan="7" class="p10">
                        <table class="contentTable table p0 border bg-white">
                            <tbody>
                            <tr>
                                <td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_OWNER"}]</td>
                                <td class="value">[{$ticket.owner}]</td>
                            </tr>
                            <tr>
                                <td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_REQUESTOR"}]</td>
                                <td class="value">[{$ticket.requestor}]</td>
                            </tr>
                            <tr>
                                <td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_STARTTIME"}]</td>
                                <td class="value">[{$ticket.starts}]</td>
                            </tr>
                            <tr>
                                <td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_LASTUPDATED"}]</td>
                                <td class="value">[{$ticket.lastupdated}]</td>
                            </tr>
                            <tr>
                                <td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_CATEGORY"}]</td>
                                <td class="value">[{$ticket.customcategory}]</td>
                            </tr>
                            <tr>
                                <td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_OBJECTS"}]</td>
                                <td class="value">[{$ticket.customobjects}]</td>
                            </tr>
                            <tr>
                                <td class="key">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__TICKET_OBJPRIORITY"}]</td>
                                <td class="value">[{$ticket.custompriority}]</td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                [{/if}]
                [{/foreach}]
                [{else}]
                <tr class="no_tickets">
                    <td colspan="7">[{isys type="lang" ident="LC__CATG__VIRTUAL_TICKETS__NO_TICKETS_FOR_OBJECT"}]</td>
                </tr>
                [{/if}]
            </tbody>
        </table>
    </div>
</div>
