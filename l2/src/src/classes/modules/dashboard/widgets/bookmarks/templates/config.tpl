<div id="widget-config-popup" class="p5">
    <table class="contentTable">
        <tr>
            <td class="key">[{isys type="f_label" name="widget-popup-config-bookmark-title" ident="LC__WIDGET__BOOKMARKS_CONFIG__TITLE"}]</td>
            <td class="value">[{isys type="f_text" id="widget-popup-config-bookmark-title" p_strClass="input-small"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type="f_label" name="widget-popup-config-bookmark-link" ident="LC__WIDGET__BOOKMARKS_CONFIG__LINK"}]</td>
            <td class="value">[{isys type="f_text" id="widget-popup-config-bookmark-link" p_strClass="input-small" p_strPlaceholder="http://"}]</td>
        </tr>
        <tr>
            <td class="key">[{isys type="f_label" name="widget-popup-config-bookmark-new-window" ident="LC__WIDGET__BOOKMARKS_CONFIG__NEW_WINDOW"}]</td>
            <td class="value">[{isys type="f_dialog" id="widget-popup-config-bookmark-new-window" p_arData=$dialog_selection p_strClass="input-mini" p_bDbFieldNN=true}]</td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button type="button" id="widget-config-bookmark-add-button" class="btn ml20">
                    <img src="[{$dir_images}]axialis/basic/symbol-add.svg" alt="+" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
                </button>
                <button type="button" id="widget-config-bookmark-cancel-button" class="btn ml5">
                    <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="x" class="mr5" /><span>[{isys type="lang" ident="LC__WIDGET__CONFIG__RESET"}]</span>
                </button>
            </td>
        </tr>
    </table>

    <div class="mt5 bg-neutral-200 p5 border">[{isys type="lang" ident="LC__WIDGET__BOOKMARKS"}]</div>

    <ul id="bookmark-list">
    [{foreach $bookmark_list as $bookmark}]
        <li data-id="[{$bookmark.id}]" data-new-window="[{intval($bookmark.new_window)}]" data-url="[{$bookmark.link}]" data-title="[{$bookmark.title}]">
            <span class="handle">&nbsp;&nbsp;&nbsp;</span>
            <span class="title">[{$bookmark.title}]</span>
            <em class="url">[{$bookmark.link}]</em>
            <button type="button" class="btn btn-secondary edit ml-auto" title="[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__EDIT_BOOKMARK"}]" data-tooltip="1">
                <img src="[{$dir_images}]axialis/basic/tool-pencil.svg" alt="" />
            </button>
            <button type="button" class="btn btn-secondary delete ml5" title="[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__REMOVE_BOOKMARK"}]" data-tooltip="1">
                <img src="[{$dir_images}]axialis/industry-manufacturing/waste-bin.svg" alt="" />
            </button>
        </li>
    [{/foreach}]
    </ul>
</div>

<style>
    #bookmark-list {
        list-style: none;
        margin: 0;
        height: 245px;
        overflow: hidden;
        overflow-y: scroll;
    }

    #bookmark-list li {
        border-bottom: 1px solid #c8c9c9;
        padding: 5px;
        display: flex;
        align-items: center;
    }

    #bookmark-list li .handle {
        background: url("[{$dir_images}]/icons/hatch.gif");
        cursor: ns-resize;
        margin-right: 5px;
    }

    #bookmark-list li span.title {
        min-width: 100px;
        max-width: 250px;
        margin-left: 5px;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #bookmark-list li .url {
        min-width: 100px;
        max-width: 300px;
        margin-left: 10px;
        color: #787979;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script type="text/javascript">
    (function () {
        'use strict';

        var current_edit   = null,
            $buttonCancel  = $('widget-config-bookmark-cancel-button'),
            $buttonSave    = $('widget-config-bookmark-add-button'),
            $bookmark_list = $('bookmark-list'),
            $link          = $('widget-popup-config-bookmark-link'),
            $title         = $('widget-popup-config-bookmark-title'),
            $new_window    = $('widget-popup-config-bookmark-new-window');

        function reset_observer() {
            var scrollBackup = 0;

            Position.includeScrollOffsets = true;

            Sortable.destroy('bookmark-list');

            Sortable.create('bookmark-list', {
                handle:   'handle',
                onChange: window.remember_bookmarks,
                dragOnStart: function() {
                    scrollBackup = $bookmark_list.scrollTop;
                },
                dragOnEnd: function() {
                    scrollBackup = $bookmark_list.scrollTop;
                },
                dragSnap: function(x, y, drag) {
                    return [0, y - (scrollBackup - $bookmark_list.scrollTop)];
                }
            });
        }

        $buttonSave.on('click', function () {
            $title.removeClassName('box-red');
            $link.removeClassName('box-red');

            if ($title.getValue().blank()) {
                $title.addClassName('box-red');
                return;
            }

            if ($link.getValue().blank()) {
                $link.addClassName('box-red');
                return;
            }

            // Try to avoid XSS and such.
            $title.setValue($title.getValue().encodeHTML());
            $link.setValue($link.getValue().encodeHTML());

            if (current_edit === null) {
                $bookmark_list
                    .insert(new Element('li', {
                        'data-id': new Date().getTime(),
                        'data-new-window': $new_window.getValue(),
                        'data-url': $link.getValue(),
                        'data-title': $title.getValue()
                    })
                        .update(new Element('span', { className: 'handle' }).update('&nbsp;&nbsp;&nbsp;'))
                        .insert(new Element('span', { className: 'title' }).update($title.getValue()))
                        .insert(new Element('em', { className: 'url' }).update($link.getValue()))
                        .insert(new Element('button', {
                            className: 'btn btn-secondary ml-auto edit',
                            type: 'button',
                            title: '[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__EDIT_BOOKMARK"}]',
                            'data-tooltip': 1
                        }).update(new Element('img', { src: window.dir_images + 'axialis/basic/tool-pencil.svg', alt: '' })))
                        .insert(new Element('button', {
                            className: 'btn btn-secondary ml5 delete',
                            type: 'button',
                            title: '[{isys type="lang" ident="LC__WIDGET__BOOKMARKS_CONFIG__REMOVE_BOOKMARK"}]',
                            'data-tooltip': 1
                        }).update(new Element('img', { src: window.dir_images + 'axialis/industry-manufacturing/waste-bin.svg', alt: '' })))
                    );

                reset_observer();
            } else {
                var $li = $bookmark_list.down('li[data-id="' + current_edit + '"]');

                if ($li) {
                    $li
                        .highlight()
                        .writeAttribute('data-url', $link.getValue())
                        .writeAttribute('data-title', $title.getValue())
                        .writeAttribute('data-new-window', $new_window.getValue())
                        .down('span.ml5').update($title.getValue())
                        .next('em').update($link.getValue());
                }
            }

            window.remember_bookmarks();
            $buttonCancel.simulate('click');
        });

        $buttonCancel.on('click', function () {
            $link.setValue('').removeClassName('box-red');
            $title.setValue('').removeClassName('box-red');
            $new_window.setValue(1);

            $buttonSave
                .down('img').writeAttribute('src', window.dir_images + 'axialis/basic/symbol-add.svg')
                .next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]');

            $buttonCancel
                .down('img').writeAttribute('src', window.dir_images + 'axialis/basic/symbol-cancel.svg')
                .next('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__RESET"}]');

            current_edit = null;
        });

        $bookmark_list.on('click', '.delete', function (ev) {
            ev.findElement('li').remove();

            window.remember_bookmarks();
            current_edit = null;
        });

        $bookmark_list.on('click', '.edit', function (ev) {
            var $li = ev.findElement('li');

            $link.setValue($li.readAttribute('data-url')).highlight();
            $title.setValue($li.readAttribute('data-title')).highlight();
            $new_window.setValue($li.readAttribute('data-new-window')).highlight();
            current_edit = $li.readAttribute('data-id');

            $buttonSave
                .down('img').writeAttribute('src', '[{$dir_images}]axialis/basic/symbol-ok.svg')
                .next('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__SAVE"}]');

            $buttonCancel
                .down('img').writeAttribute('src', '[{$dir_images}]axialis/basic/symbol-cancel.svg')
                .next('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__ABORT"}]');
        });

        window.remember_bookmarks = function () {
            var bookmarks = [];

            $bookmark_list.select('li').each(function ($el) {
                bookmarks.push({
                    id:         $el.readAttribute('data-id'),
                    title:      $el.readAttribute('data-title'),
                    link:       $el.readAttribute('data-url'),
                    new_window: $el.readAttribute('data-new-window') == 1
                });
            });

            $('widget-popup-config-changed').setValue('1');
            $('widget-popup-config-hidden').setValue(Object.toJSON(bookmarks));
            $('body').fire('update:tooltips');
        };

        window.remember_bookmarks();
        reset_observer();
    })();
</script>
