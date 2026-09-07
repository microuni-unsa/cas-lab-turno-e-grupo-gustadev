<style type="text/css">
    body {
        background: #fff;
    }

    #ckeditor-filebrowser {
	    border: none;
    }

    #ckeditor-filebrowser .grid-item {
        height: 200px;
        width: 200px;
	    background: #fff url("[{$dir_images}]pattern3.png");
	    border:1px solid #aaa;
	    margin: 5px;
	    cursor: pointer;
        display:flex;
        align-items: center;
        justify-content: center;
    }

    #ckeditor-filebrowser .grid-item img {
        max-height: 200px;
        max-width: 200px;
    }

    #ckeditor-filebrowser .grid-item.selected {
	    outline:3px solid #a00;
    }
</style>

<div id="ckeditor-filebrowser" class="popup">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__UNIVERSAL__CHOOSE_FILE_NOW"}]</h1>

        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div id="ckeditor-filebrowser-gallery" class="popup-content">
		[{foreach $files as $file}]
		<div class="fl grid-item">
            <img src="[{$file}]"/>
		</div>
		[{foreachelse}]
		<p class="info m5 p5"><img src="[{$dir_images}]axialis/basic/button-info.svg" class="mr5 vam"/><span class="vam">[{$message}]</span></p>
		[{/foreach}]
	</div>

	<div class="popup-footer-ng">
		<button id="ckeditor-filebrowser-accept" type="button" class="btn">
			<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" /><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>
		<button type="button" class="btn ml5 popup-closer">
			<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt=""/><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
        <button id="ckeditor-filebrowser-delete" type="button" class="btn ml-auto">
            <img src="[{$dir_images}]axialis/industry-manufacturing/waste-bin.svg" alt=""/><span>[{isys type="lang" ident="LC_UNIVERSAL__DELETE"}]</span>
        </button>
	</div>
</div>

<script type="text/javascript">
    (function () {
        'use static';

        var $popup = $('ckeditor-filebrowser'),
	        $container = $('ckeditor-filebrowser-gallery'),
            $button_accept = $('ckeditor-filebrowser-accept'),
            $button_delete = $('ckeditor-filebrowser-delete').disable(),
	        delete_image = function () {
		        if (confirm('[{isys type="lang" ident="LC__UNIVERSAL__DELETE_FILE_CONFIRM" p_bHtmlEncode=false}]')) {
			        var $selection = $container.down('.selected');

			        new Ajax.Request('[{$delete_url}]', {
				        parameters:{
					        file: $selection.down('img').readAttribute('src')
				        },
				        method:'post',
				        onSuccess:function (transport) {
					        var json = transport.responseJSON;

					        if (json.success) {
						        $button_delete.disable();
						        idoit.Notify.success('[{isys type="lang" ident="LC__UNIVERSAL__FILE_DELETED" p_bHtmlEncode=false}]');

						        new Effect.Morph($selection, {
							        style:'opacity:0;',
							        afterFinish: function () {
								        $selection.remove();
							        }
						        });
					        } else {
						        idoit.Notify.error('[{isys type="lang" ident="LC__UNIVERSAL__FILE_NOT_DELETED" p_bHtmlEncode=false}] - ' + json.message, {sticky:true});
					        }
				        }
			        });
		        }
	        },
            select_image = function (ev) {
	            $container.select('.selected').invoke('removeClassName', 'selected');

                ev.findElement('.grid-item').addClassName('selected');

	            $button_delete.enable();
            };

        const closePopup = () => {
            self.close();
        }

        $container.select('.grid-item')
            .invoke('observe', 'click', select_image)
            .invoke('observe', 'dblclick', function (ev) {
                select_image(ev);
                $button_accept.simulate('click')
            });

        // Action for the "accept" button.
        $button_accept.on('click', function () {
            var $selection = $container.down('.selected').down('img');

            if ($selection) {
                // "opener" is the window element, which opened this popup.
                opener.CKEDITOR.tools.callFunction('[{$ckeditor_func_num}]', $selection.readAttribute('src'), '');
            }

            closePopup();
        });

	    // Add the delete action.
	    $button_delete.on('click', delete_image);

        // Action for the "abort" button.
        $popup.on('click', '.popup-closer', function () {
            closePopup();
        });
    })();
</script>
