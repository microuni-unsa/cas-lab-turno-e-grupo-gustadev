document.observe('dom:loaded', function () {
    'use strict';

    new Ajax.Request(idoit.Router.getRoute('synetics_admin.modal.advertise'), {
        method:     'GET',
        onComplete: (xhr) => {
            if (!is_json_response(xhr, true)) {
                return;
            }

            const json = xhr.responseJSON;

            if (!json.success) {
                idoit.Notify.warning(json.message, {life: 10});
                return;
            }

            Modal.open(json.data, {
                maxWidth:  550,
                maxHeight: 450
            });
        }
    });
});
