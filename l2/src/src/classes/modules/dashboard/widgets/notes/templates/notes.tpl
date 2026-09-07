<div style="background:[{$color}]; color:[{$fontcolor}];" class="p20">
	<div id="[{$unique_id}]_text" class="wysiwyg">
		[{$note}]
	</div>
</div>

<script type="text/javascript">
    (function() {
        const $widget = $('[{$unique_id}]');
        const $widgetBody = $widget.down('.widget-body');

        $widgetBody.setStyle({ minHeight: '60px', padding: 0 });

        // A double-click on the note will activate the "inline editing".
        $('[{$unique_id}]_text').on('dblclick', function () {
            var js_[{$unique_id}] = CKEDITOR.replace("[{$unique_id}]_text", {
                // Load the Simple Box plugin.
                versionCheck: false,
                extraPlugins: "",
                removePlugins: ["flash"],
                language: "de",
                allowedContent: true,
                toolbar: [
                    { name: "basicstyles", items: ["Bold","Italic","Underline","Strike","-","RemoveFormat"] },
                    { name: "script", items: ["Subscript","Superscript"] },
                    { name: "paragraph", items: ["NumberedList","BulletedList"] },
                    { name: "indent", items: ["Outdent","Indent"] },
                    { name: "UndoRedo", items: ["Undo","Redo"] },
                    { name: "tools", items: ["Maximize"] }
                ],
                extraAllowedContent: "script",
                menuGroups: "clipboard,table,anchor,link,image",
                removeButtons: "",
                entities: false,
                on: {
                    change: function (evt) {
                        this.updateElement();
                    }
                }
            });

            [{if $note_empty}]
            this.update().setStyle({minHeight: '25px'});
            [{/if}]

            this.addClassName('border p5')
                .setStyle({ backgroundColor: '#fff' });

            $widgetBody.down('div')
                .insert(new Element('button', { type:'button', className:'btn mt5 mr5 accept'})
                    .update(new Element('img', { src: window.dir_images + 'axialis/basic/symbol-ok.svg', className: 'mr5'}))
                    .insert(new Element('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__ACCEPT"}]')))
                .insert(new Element('button', { type:'button', className:'btn mt5 abort'})
                    .update(new Element('img', { src: window.dir_images + 'axialis/basic/symbol-cancel.svg', className: 'mr5'}))
                    .insert(new Element('span').update('[{isys type="lang" ident="LC__WIDGET__CONFIG__ABORT"}]')));

            this.next('button.accept').on('click', function () {
                var el     = $('[{$unique_id}]'),
                    config = {
                        fontcolor: '[{$fontcolor}]',
                        color:     '[{$color}]',
                        title:     '[{$title|escape:"javascript"}]',
                        note:      js_[{$unique_id}].getData()
                    };

                window.dashboard.save_config_and_reload_widget('[{$ajax_url}]', {
                    id:        el.readAttribute('data-id'),
                    unique_id: el.id,
                    config:    Object.toJSON(config)
                });
            }.bind(this));

            this.next('button.abort').on('click', function () {
                window.dashboard.reload_widget($('[{$unique_id}]'));
            });

            this.focus();
        });
    })();
</script>
