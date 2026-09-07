<script type="text/javascript">
[{include file="modules/templates/templates.js"}]
</script>

<div class="p20">
	<h2 class="mb5">1. [{isys type="lang" ident="LC__MASS_CHANGE__CHOOSE_OBJECTS_TO_BE_CHANGED"}]</h2>

    [{isys
        name="selected_objects"
        type="f_popup"
        id="object_browser"
        multiselection=true
        p_bInfoIconSpacer=0
        p_strPopupType="browser_object_ng"
        callback_accept="idoit.callbackManager.triggerCallback('activate-mass-change-btn');"
        callback_detach="idoit.callbackManager.triggerCallback('activate-mass-change-btn');"}]

	<br class="cb" />

	<h2 class="mt20 mb5">2. [{isys type="lang" ident="LC__MASS_CHANGE__SELECT_TEMPLATE_FOR_MASS_CHANGES"}]</h2>

	[{if !$hasTemplates}]
		<p class="box-blue p5 mt10 mb10">[{isys type="lang" ident="LC__MASS_CHANGE__MASS_CHANGES_DESCRIPTION_CONTENT"}]</p>
	[{/if}]

	<label>
		[{isys type="f_dialog" name="templates" p_bSort=false p_onChange="select_single_template(this);" disableInputGroup=true p_bInfoIconSpacer=0}]
	</label>

	<div class="container mt5" id="selected_templates">
        <ul id="template_list" class="list-style-none m0 p0"></ul>
	</div>
	<div class="cb mb5"></div>

    <h2 class="mt20 mb5">3. [{isys type='lang' ident='LC__MASS_CHANGE__OPTIONS'}]</h2>

    <h3 class="mt10 mb5">3.1 [{isys type='lang' ident='LC__MASS_CHANGE__HANDLING_EMPTY_FIELDS'}]</h3>

    <label class="display-block m5">
        <input type="radio" value="[{$keep}]" name="empty_fields" checked="checked" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__IGNORE_EMPTY_FIELDS'}]
    </label>
    <label class="display-block m5">
        <input type="radio" value="[{$clear}]" name="empty_fields" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__CLEAR_FIELDS'}]
    </label>

    <h3 class="mt10 mb5">3.2 [{isys type='lang' ident='LC__MASS_CHANGE__HANDLING_MULTI-VALUED_CATEGORIES'}]</h3>

    <label class="display-block m5">
        <input type="radio" value="[{$untouched}]" name="multivalue_categories" checked="checked" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__KEEP_CATEGORY_ENTRIES_UNTOUCHED'}]
    </label>
    <label class="display-block m5">
        <input type="radio" value="[{$add}]" name="multivalue_categories" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__ADD_CATEGORY_ENTRIES'}]
    </label>
    <label class="display-block m5">
        <input type="radio" value="[{$delete_add}]" name="multivalue_categories" [{$field_disabled}]/> [{isys type='lang' ident='LC__MASS_CHANGE__DELETE_BEFORE_ADD_CATEGORY_ENTRIES'}]
    </label>

	<h3 class="mt10 mb5">3.3 [{isys type='lang' ident='LC__MASS_CHANGE__LOG_LEVEL'}]</h3>

	<label class="display-block m5">
		<input type="radio" name="log-level" value="C__ERROR" checked="checked" />
		[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__SIMPLE"}]
	</label>
	<label class="display-block m5">
		<input type="radio" name="log-level" value="C__INFO" />
		[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__NORMAL"}]
	</label>
	<label class="display-block m5">
		<input type="radio" name="log-level" value="C__DEBUG" />
		[{isys type="lang" ident="LC__MODULE__IMPORT__CSV__LOGGING__ALL"}]
	</label>

    <h3 class="mt10 mb5">3.4 [{isys type='lang' ident='LC__MASS_CHANGE__OTHER_OPTIONS'}]</h3>

    <label class="display-block m5">
        <input type="checkbox" name="overwrite-cmdb-status" checked [{$field_disabled}]>
        [{isys type="lang" ident="LC__MASS_CHANGE__OVERWRITE_CMDB_STATUS"}]
    </label>

    <h2 class="mt20 mb5">4. [{isys type="lang" ident="LC__MASS_CHANGE__APPLY_MASS_CHANGE"}]</h2>

    <button type="submit" id="apply_mass_change" name="apply_mass_change" class="btn" value="1" disabled>
        <img style="display:none;" id="loader" class="animation-rotate" src="[{$dir_images}]axialis/user-interface/loading.svg" alt="Loading" />
        <span>[{isys type="lang" ident="LC__MASS_CHANGE__APPLY_MASS_CHANGE"}]</span>
    </button>

    <hr class="mt20 mb20" />

	<iframe id="iframe" name="iframe" src="" class="border" style="width:50%;height:250px;display:none;"></iframe>
</div>

<script type="text/javascript">
    (function() {

        var $applyMassChange = $('apply_mass_change');

        const activate_mass_change_btn = function () {
            if ($('templates').value != -1 && $('selected_objects__HIDDEN').value != '') {
                $applyMassChange.enable();
            } else {
                $applyMassChange.disable();
            }
        };

        if ($applyMassChange) {
            $applyMassChange.on('click', function () {
                $('isys_form').writeAttribute('target', 'iframe');
                $('loader').show();
                $('iframe').appear();
            });
        }

        idoit.callbackManager.registerCallback('activate-mass-change-btn', activate_mass_change_btn);

        $('templates').on('change', activate_mass_change_btn);
    })();
</script>
