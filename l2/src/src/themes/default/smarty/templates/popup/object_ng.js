(function () {
	"use strict";

	// Initialize preselection component.
    window.browserPreselection = new Browser.preselection('objectPreselection', {
        $selectionCounter: 'numObjects',
        secondElement:      false,
        multiselection:     JSON.parse('[{if $multiselection}]true[{else}]false[{/if}]'),
        latestLogElement:   'latestLog',
        instanceName:       'browserPreselection',
        urlBase:            '[{$config.www_dir}]',
        returnElement:      '[{$return_element}]',
        objectBrowserName:  '[{$objectBrowserName}]',
        afterFinish:        function () {
            $('preselectionLoader').addClassName('hide');
            $('browser-content').show();
        },
        afterRemove:        function () {
            try {
                window.browserSearch.updateTable();
            } catch (e) {

            }

            try {
                window.browserReport.updateTable();
            } catch (e) {

            }

            try {
                window.browserList.updateTable();
            } catch (e) {

            }
        }
    });

    window.browserParentPreselection = JSON.parse('[{$parentPreselection|json_encode|escape:"javascript"}]') || [];
    window.browserPreselection.setSelection(JSON.parse('[{$preselection|json_encode|escape:"javascript"}]'));

    if (Object.isArray(window.browserParentPreselection)) {
        window.browserParentPreselection = [];
    }

	// Moves object browser data to a parent field.
	window.moveToParent = function (hiddenElement, viewElement) {
        var $view = $(viewElement),
            $hidden = $(hiddenElement),
            selection = window.browserPreselection.getSelection();

		if (window.browserPreselection.isMultiselection()) {

            $hidden.setValue(JSON.stringify(selection));

			if ($view) {
                $view.setAttribute('data-last-value', $view.getValue());
                $view.setValue('');

                if (selection.length > 0) {
                    window.browserPreselection.getObjectMetaData(selection.slice(0, 20), function (metaData) {
                        // @see ID-10734 Limit the objects to 20, it does not make sense to load more.
                        $view.setValue(metaData.map((d) => d.objectTypeTitle + ' >> ' + d.objectTitle).join(', ') + (selection.length > 20 ? ', ...' : ''));

                        [{if $callback_accept}][{$callback_accept}][{/if}]
                    });
                } else {
                    [{if $callback_accept}][{$callback_accept}][{/if}]
                }
			}
		} else {
			if (selection.length > 0) {
				if ($view && selection[0]) {
                    $view.setValue(idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING'));
                    $view.setAttribute('data-last-value', $view.getValue());

                    window.browserPreselection.getObjectMetaData([selection[0]], function (metaData) {
                        $view.setValue(metaData[0].objectTypeTitle + ' >> ' + metaData[0].objectTitle);
                        [{if $callback_accept}][{$callback_accept}][{/if}]
                    });
				}

				if ($hidden && selection[0]) {
                    $hidden.setValue(selection[0]);
				}
			} else {
				if ($view) {
					$view.setValue('[{isys type="lang" ident="LC__UNIVERSAL__CONNECTION_DETACHED" p_bHtmlEncode=0}]');
                    $view.setAttribute('data-last-value', $view.getValue());
				}

				if ($hidden) {
                    $hidden.setValue('');
				}
			}
		}
	}

    // @see ID-9218 Don't "pre-load" the object list, this will already be done in 'browser.js'.

    // @see  ID-8219  Focus the filter, if no other form element is in focus. Wait for the DOM to build.
    if (!['input', 'select'].include(document.activeElement.tagName.toLowerCase())) {
        setTimeout(function () {
            const $filter = $('data-grid-objectList-filter-data');

            if ($filter) {
                $filter.focus();
            }
        }, 250);
    }
}());
