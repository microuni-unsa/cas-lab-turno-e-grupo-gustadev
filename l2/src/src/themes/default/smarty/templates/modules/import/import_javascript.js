function object_retrieve(p_id) {
    $('object_retrieve_row').show();
    $('object_retrieve').update('Checking..');
    aj_submit('?request=object&objtype=1&[{$smarty.const.C__CMDB__GET__OBJECT}]=' + p_id, 'get', 'object_retrieve');

    $('force').checked = 'checked';
}

function select_obj_type(p_type_id) {
    $('obj_type').setValue(p_type_id);
}

function submit_import(importType, importResultContainerId) {
    $('type').value = importType;
    $(importResultContainerId)
        .update(new Element('div', { className: 'display-flex align-items-center p10' })
            .update(new Element('img', { src: window.dir_images + 'axialis/user-interface/loading.svg', className: 'mr5 animation-rotate' }))
            .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]')));

    new Effect.Appear(importResultContainerId, {duration: 0.8});

    new Ajax.Request('?ajax=1&call=import', {
        method:     'post',
        parameters: $('isys_form').serialize(true),
        onSuccess:  function (xhr) {
            const json = xhr.responseJSON;

            $(importResultContainerId).update(json.data);
        }
    });

    Event.observe(window, importResultContainerId, function () {
        if ($(importResultContainerId).visible()) {
            new Effect.Fade(importResultContainerId, {duration: 0.2});
        }
    });
}

function import_error(p_message) {
    $('import-message').hide();
    $('import-message').update(p_message);
    new Effect.Appear('import-message', {duration: 0.4});

    Event.observe(window, 'click', function () {
        if ($('import-message') && $('import-message').visible()) {
            new Effect.Fade('import-message', {duration: 0.2});
        }
    });
}

function select_importfile(type, filename, $td) {
    // Set import type and filename.
    $('type').setValue(type);
    $('selected_file').setValue(filename);

    // Remove opacity from the container and enable the corresponding import button.
    if ($(type + '_import_button')) {
        $(type + '_import_button').disabled = false;
    }

    $td.up('table').select('.highlightLine').invoke('removeClassName', 'highlightLine');
    $td.up('tr').addClassName('highlightLine');
}

function delete_import(file) {
    if (confirm('[{isys type="lang" ident="LC__MODULE__IMPORT__CONFIRM_DELETE_FILE"}]')) {
        new Ajax.Request(document.location.href, {
            method:     'post',
            parameters: {
                delete_import: file
            },
            onSuccess:  function (transport) {
                var $json = transport.responseJSON;

                if ($json.success) {
                    idoit.Notify.success($json.message);
                } else {
                    idoit.Notify.error($json.message);
                }
            }
        });
    }
}
