[{if $g_list}]
    [{$g_list}]
[{else}]

<div>
	<h2 class="bg-neutral-200 p5 border-bottom">[{isys type='lang' ident='LC__NOTIFICATIONS__MANAGE_TEMPLATES'}]</h2>

	<div class="p10">
		<h3 class="mb5">[{$type_title}]</h3>

		<p>[{$type_description}]</p>

		<p>&nbsp;</p>

		<p><a href="[{$type_templates}]">[{isys type='lang' ident='LC__NOTIFICATIONS__MANAGE_NOTIFICATIONS'}]</a></p>

		[{isys type='f_text' name='C__NOTIFICATIONS__TEMPLATE_ID'}]
		[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_TYPE_ID'}]
	</div>

    <h3 class="p5 bg-neutral-200 border-top border-bottom">[{isys type='lang' ident='LC__NOTIFICATIONS__COMMON_SETTINGS'}]</h3>

    <table class="contentTable">
        <tr>
            <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE' ident='LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE'}]</td>
            <td class="value">[{isys type='f_dialog' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_LOCALE'}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT' ident='LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT'}]</td>
            <td class="value">[{isys type='f_text' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_SUBJECT'}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_TEXT' ident='LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_TEXT'}]</td>
            <td class="value">[{isys type='f_textarea' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_TEXT'}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type='f_label' name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT' ident='LC__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT'}]</td>
            <td class="value pl20">[{isys type='f_property_selector' custom_fields=1 dynamic_properties=1 name='C__NOTIFICATIONS__NOTIFICATION_TEMPLATE_REPORT'}]</td>
        </tr>
    </table>

    <h3 class="p5 bg-neutral-200 border-top border-bottom">[{isys type='lang' ident='LC__NOTIFICATIONS__PLACEHOLDERS'}]</h3>

    <table class="listing">
        <thead>
        <tr>
            <th>[{isys type="lang" ident="LC__NOTIFICATIONS__NOTIFICATION_DESCRIPTION"}]</th>
            <th>[{isys type="lang" ident="LC__NOTIFICATIONS__PLACEHOLDERS"}]</th>
            <th>[{isys type="lang" ident="LC_UNIVERSAL__INFORMATION"}]</th>
        </tr>
        </thead>
        <tbody>
        [{foreach $placeholders as $placeholder}]
            <tr>
                <td>[{$placeholder.title}]</td>
                <td><code>[{$placeholder.value}]</code></td>
                <td>[{$placeholder.description}]</td>
            </tr>
            [{/foreach}]
        </tbody>
    </table>
</div>
[{/if}]
