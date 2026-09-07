[{isys_group name="tom.popup.interval"}]
	<div id="modal-interval">
        <div class="modal-header">
            <h1>[{isys type="lang" ident="LC__INTERVAL__POPUP_HEADER"}]</h1>
            <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
                <img class="fr mouse-pointer" alt="" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
            </button>
        </div>

		<div class="modal-content">
			<table class="contentTable">
				<tr>
					<td class="key">
						[{isys type="lang" ident="LC__INTERVAL__REPEAT_EVERY"}]
					</td>
					<td class="value">
						[{isys type="f_count" name="C__INTERVAL__REPEAT_EVERY"}] [{isys type="f_dialog" name="C__INTERVAL__REPEAT_EVERY_UNIT"}]
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="value pl20">
						<div id="interval-week-options" class="hide mt5">
							[{isys type="lang" ident="LC__INTERVAL__REPEAT_ON"}]<br />
							[{foreach $days as $day => $dayName}]
							<label class="mr5"><input type="checkbox" name="C__INTERVAL__REPEAT_WEEKLY[]" value="[{$day}]" class="mr5" />[{$dayName}]</label>
							[{/foreach}]
						</div>
						<div id="interval-month-options" class="hide mt5">
							<label class="display-block">
								<input type="radio" name="C__INTERVAL__REPEAT_MONTHLY" value="absolute" class="mr5" checked>[{isys type="lang" ident="LC__INTERVAL__MONTHLY_AT"}] [{$initialDate}].
							</label>
							<label class="display-block"><input type="radio" name="C__INTERVAL__REPEAT_MONTHLY" value="relative" class="mr5">[{$relativeDate}]</label>
						</div>
					</td>
				</tr>
				<tr>
                    <td class="category-spacer" colspan="2"><hr /></td>
				</tr>
				<tr>
					<td class="key vat pt5">[{isys type="lang" ident="LC__INTERVAL__ENDS"}]</td>
					<td class="value pl20 end-condition">
						<label class="display-block">
                            <input type="radio" name="C__INTERVAL__END_AFTER" value="[{$endAfterNever}]" class="mr5" checked />
							[{isys type="lang" ident="LC__INTERVAL__ENDS_NEVER"}]
						</label>
						<label class="display-block mt5">
                            <input type="radio" name="C__INTERVAL__END_AFTER" value="[{$endAfterDate}]" class="mr5" />
                            <div class="input-group" style="width:250px;">
                                <span>[{isys type="lang" ident="LC__INTERVAL__ENDS_ON"}]</span>
                                [{isys type="f_popup" name="C__INTERVAL__END_DATE" p_strPopupType="calendar"}]
                            </div>
						</label>
						<label class="display-block mt5">
                            <input type="radio" name="C__INTERVAL__END_AFTER" value="[{$endAfterEvents}]" class="mr5" />
                            <div class="input-group" style="width:250px;">
                                <span>[{isys type="lang" ident="LC__INTERVAL__ENDS_AFTER"}]</span>
                                [{isys type="f_count" name="C__INTERVAL__END_EVENT_AMOUNT"}]
                                <span>[{isys type="lang" ident="LC__INTERVAL__ENDS_AFTER_DATES"}]</span>
                            </div>
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="modal-footer">
			<button type="button" id="modal-interval-save" class="btn mr5">
				<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" />
                <span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
			</button>
			<button type="button" class="btn popup-closer">
				<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" />
                <span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
			</button>
		</div>
	</div>
	<script type="text/javascript">
        (function () {
            "use strict";

            window.idoit.Require.addModule('popupInterval', '[{$dir_tools}]js/popups/interval.js');

            idoit.Require.require('popupInterval', function () {
                const $modal = $('modal-interval');
                const $selfView = $('[{$selfView}]');
                const $selfHidden = $('[{$selfHidden}]');
                const $saveButton = $('modal-interval-save');

                let config = JSON.parse('[{$config|json_encode|escape:"javascript"}]');

                const popupInterval = new PopupInterval($modal, {
                    config: config,
                    intervalEveryUnits: {
                        "[{$repeatUnitDay}]":   [
                            '[{isys type="lang" ident="LC__UNIVERSAL__DAY"}]',
                            '[{isys type="lang" ident="LC__UNIVERSAL__DAYS"}]'
                        ],
                        "[{$repeatUnitWeek}]":  [
                            '[{isys type="lang" ident="LC__UNIVERSAL__WEEK"}]',
                            '[{isys type="lang" ident="LC__UNIVERSAL__WEEKS"}]'
                        ],
                        "[{$repeatUnitMonth}]": [
                            '[{isys type="lang" ident="LC__UNIVERSAL__MONTH"}]',
                            '[{isys type="lang" ident="LC__UNIVERSAL__MONTHS"}]'
                        ],
                        "[{$repeatUnitYear}]":  [
                            '[{isys type="lang" ident="LC__UNIVERSAL__YEAR"}]',
                            '[{isys type="lang" ident="LC__UNIVERSAL__YEARS"}]'
                        ]
                    },
	                repeatUnit:{
                        day: '[{$repeatUnitDay}]',
                        week: '[{$repeatUnitWeek}]',
                        month: '[{$repeatUnitMonth}]',
                        year: '[{$repeatUnitYear}]'
	                },
	                endAfter: {
		                never: '[{$endAfterNever}]',
		                date: '[{$endAfterDate}]',
		                events: '[{$endAfterEvents}]'
	                }
                });

                $saveButton.on('click', function () {
                    config = popupInterval.getConfig();

                    if (config.endAfter === 'date' && config.endDetails.blank()) {
                        idoit.Notify.warning('[{isys type="lang" ident="LC__INTERVAL__VALIDATION__END_AFTER_DATE_MISSING"}]', { life: 5 });
                        return;
                    }

                    $selfHidden.setValue(JSON.stringify(config));

                    if ($selfHidden.next('button')) {
                        const newParameter = JSON.stringify({ name: "[{$selfView}]", config: config });

                        $selfHidden.next('button').writeAttribute('onclick', 'Modal.openLegacy({ "popupType":"interval", "urlParameter":"", "maxWidth":600, "maxHeight":400, "parameters":{ "static":' + newParameter + ' }, "minWidth":500, "minHeight":360})')
                    }

                    new Ajax.Request('[{$ajaxUrl}]&func=humanReadableInterval', {
                        parameters: {
                            config: $selfHidden.getValue()
                        },
                        onSuccess:  function (xhr) {
                            const json = xhr.responseJSON;

                            if (json.success) {
                                $selfView.setValue(json.data);
                            } else {
                                idoit.Notify.error(json.message || xhr.responseText, {sticky: true});
                            }
                        }
                    });

                    Modal.close($modal.up('.modal'));
                });

                $modal.on('click', '.popup-closer', function () {
                    Modal.close($modal.up('.modal'));
                });
            });
        }());
	</script>
    <style>
        #modal-interval {
            height: 100%;
        }

        #modal-interval .contentTable .key {
            width: 130px;
        }

        #modal-interval .end-condition label {
            height: 36px;
            display: flex;
            align-items: center;
        }
	</style>
[{/isys_group}]
