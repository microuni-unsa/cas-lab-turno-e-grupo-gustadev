[{strip}]
	<div id="tree_content">
		[{if isset($index_includes.lefttreetop)}]
			[{include file=$index_includes.lefttreetop}]
		[{/if}]

        [{if $bShowMenuTreeButtons && $treeMode == $smarty.const.C__CMDB__VIEW__TREE_LOCATION}]
        <button id="sidebar-changer-location-toggle" type="button" class="btn btn-secondary ml20">
            <span>[{isys type="lang" ident="LC__CMDB__TREE_VIEW_TYPES"}]</span>
            <img src="[{$dir_images}]axialis/user-interface/angle-down-small.svg" class="ml5" alt="" />
        </button>

        <div id="tree_config" class="ml20 mt10 hide">
            <label class="display-block p5"><input type="radio" name="tree_type" value="[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOCATION}]" [{if $smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOCATION == $treeType || null == $treeType}]checked="checked"[{/if}] /> [{isys type="lang" ident="LC__CMDB__TREE_VIEW__LOCATION"}]</label>
            <label class="display-block p5"><input type="radio" name="tree_type" value="[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS}]" [{if $smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS == $treeType}]checked="checked"[{/if}]/> [{isys type="lang" ident="LC__CMDB__TREE_VIEW__LOGICAL_UNIT"}]</label>
            <label class="display-block p5"><input type="radio" name="tree_type" value="[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__COMBINED}]" [{if $smarty.const.C__CMDB__VIEW__TREE_LOCATION__COMBINED == $treeType}]checked="checked"[{/if}]/> [{isys type="lang" ident="LC__CMDB__TREE_VIEW__COMBINED"}]</label>
        </div>

        <script>
            (function () {
                const $toggleButton = $('sidebar-changer-location-toggle');
                const $treeConfig = $('tree_config');

                $toggleButton.on('click', function () {
                    $toggleButton.toggleClassName('pressed');

                    if ($toggleButton.hasClassName('pressed')) {
                        $toggleButton.down('img').writeAttribute('src', window.dir_images + 'axialis/user-interface/angle-up-small.svg')
                        $treeConfig.removeClassName('hide');
                    } else {
                        $toggleButton.down('img').writeAttribute('src', window.dir_images + 'axialis/user-interface/angle-down-small.svg')
                        $treeConfig.addClassName('hide');
                    }
                })
            })();
        </script>
        [{/if}]

        <div class="p20 overflow-auto" id="menuTreeJS">
            [{$menu_tree}]
        </div>

		[{if isset($index_includes.lefttreebottom)}]
			[{include file=$index_includes.lefttreebottom}]
		[{/if}]
	</div>
[{/strip}]

<script type="text/javascript">
	if (menu_tree) {
		[{if $bMenuTreeHideable}]
		if ('addNodesVisibility' in menu_tree) {
			menu_tree.addNodesVisibility([{$treeHide}], '[{$rootNodeIdentifier}]');
		}
		[{/if}]

		[{if $bMenuTreeSearcheable}]
		if ('addSearchCapabilities' in menu_tree) {
			menu_tree.addSearchCapabilities();
		}
		[{/if}]
	}

    if ($('menuTreeJS') && $('tree_config')) {
        idoit.Require.requireQueue(['treeBase', 'treeLocationLinked'], function () {
            const tree = new LocationLinkedTree($('menuTreeJS'), {
                rootNodeId: '[{$smarty.const.C__OBJ__ROOT_LOCATION}]',
                nodeSorting: function(a, b) {
                    return a.nodeTitle.localeCompare(b.nodeTitle);
                },
                considerRights: !!parseInt('[{isys_tenantsettings::get('auth.use-in-location-tree', 0)}]'),
                currentObjectId: parseInt('[{$objectId}]'),
                typeMapping: {
                    '[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOCATION}]': '[{\idoit\Module\Cmdb\Model\Tree::MODE_PHSYICAL}]',
                    '[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS}]': '[{\idoit\Module\Cmdb\Model\Tree::MODE_LOGICAL}]',
                    '[{$smarty.const.C__CMDB__VIEW__TREE_LOCATION__COMBINED}]': '[{\idoit\Module\Cmdb\Model\Tree::MODE_COMBINED}]'
                }
            });

            tree.setOpenNodes('[{$openNodes}]'.split(','))
                .setMode($('tree_config').down(':checked').getValue())
                .process();

            $('tree_config').on('change', 'input', function(e) {
                // Re-process the tree after the type has been changed.
                tree.setMode($('tree_config').down(':checked').getValue()).process()
            });
        });
    }

	window.ObjectSelected = function(objId, objTypeId, objTitle, objTypeTitle, callbackId, clickedElement) {
        // We have to build the path to the clicked Element to prevent server-side detection.
        var l_element = $(clickedElement.parentNode);
        var l_path = [];

        while (!l_element.hasClassName('dtree')) {
            if (l_element.id.startsWith('menuTreeJS_Node_')) {
                l_path.push(l_element.id.replace('menuTreeJS_Node_', ''));
            }

            l_element = l_element.up();
        }

		window.location.href = window.www_dir + '?' + C__CMDB__GET__OBJECT + '=' + objId + "&treeView="+"&treePath=" + l_path.join(',');
	};
</script>
