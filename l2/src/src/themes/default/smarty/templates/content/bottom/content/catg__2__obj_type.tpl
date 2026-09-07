<style type="text/css">
    #category_list_overview,
    #category_list_active {
        margin: 0 !important;
    }

    #category_list_overview li,
    #category_list_active li {
        position: relative;
    }

    #category_list_overview li input,
    #category_list_active li input {
        position: absolute;
        top: 7px;
        right: 5px;
    }

    #category_list_overview li:first-child,
    #category_list_active li:first-child {
        border: none !important;
    }
</style>

<table class="contentTable">
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__OBJTYPE__ID"}]</td>
        <td class="value">[{isys type="f_data" name="C__OBJTYPE__ID"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__OBJTYPE__NAME"}]</td>
        <td class="value">[{isys type="f_data" name="C__OBJTYPE__TRANSLATED_TITLE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__TITLE" ident="LC__CMDB__OBJTYPE__CONST_NAME"}]</td>
        <td class="value">[{isys type="f_text" name="C__OBJTYPE__TITLE" p_bNoTranslation="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__SYSID_PREFIX" ident="LC__CMDB__OBJTYPE__SYSID_PREFIX"}]</td>
        <td class="value">[{isys type="f_text" name="C__OBJTYPE__SYSID_PREFIX" p_bNoTranslation="1"}]</td>
    </tr>
    <tr>
        <td class="key vat">
            [{isys type="f_label" name="C__OBJTYPE__AUTOMATED_INVENTORY_NO" ident="LC__CMDB__OBJTYPE__AUTOMATIC_INVENTORY_NUMBER"}]
        </td>
        <td class="value">
            <div class="ml20 input-group input-size-normal">
                [{isys type="f_text" name="C__OBJTYPE__AUTOMATED_INVENTORY_NO" p_bNoTranslation="1" p_bInfoIconSpacer=0 disableInputGroup=true}]

                [{if $placeholders && isys_glob_is_edit_mode()}]
                    <div class="input-group-addon input-group-addon-clickable">
                        <img src="[{$dir_images}]axialis/basic/button-help.svg" onclick="Effect.toggle('placeholderHelper', 'slide', {duration:0.2});" />
                    </div>
                [{/if}]
            </div>
            [{if isys_glob_is_edit_mode()}]
                <br class="cb" />
                <div class="box ml20 mt5 mb5 overflow-auto input-size-normal" style="display:none;height:200px;" id="placeholderHelper">
                    <table class="contentTable m0 w100 listing hover">
                        [{foreach $placeholders as $plkey => $plholder}]
                            <tr class="mouse-pointer">
                                <td class="key"><code>[{$plkey}]</code></td>
                                <td>[{$plholder}]</td>
                            </tr>
                        [{/foreach}]
                    </table>
                </div>
            [{/if}]
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__POSITION_IN_TREE" ident="LC__CMDB__OBJTYPE__POSITION_IN_TREE"}]</td>
        <td class="value">[{isys type="f_text" name="C__OBJTYPE__POSITION_IN_TREE" p_bNoTranslation="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__COLOR" ident="LC__CMDB__OBJTYPE__COLOR"}]</td>
        <td class="value">[{isys type="f_colorpicker" name="C__OBJTYPE__COLOR"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__GROUP_ID" ident="LC__CMDB__OBJTYPE__GROUP"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__OBJTYPE__GROUP_ID" p_strTable="isys_obj_type_group" p_bDbFieldNN="1" tab="3"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__CATS_ID" ident="LC__CMDB__OBJTYPE__CATS"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__OBJTYPE__CATS_ID" tab="3" p_bEnableMetaMap=false}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__SELF_DEFINED" ident="LC__CMDB__OBJTYPE__SELFDEFINED"}]</td>
        <td class="value">[{isys type="f_dialog"  name="C__OBJTYPE__SELF_DEFINED" p_bDisabled=true p_bDbFieldNN="1" tab="4"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__IS_CONTAINER" ident="LC__CMDB__OBJTYPE__LOCATION"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__OBJTYPE__IS_CONTAINER" p_bDbFieldNN="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__RELATION_MASTER" ident="LC__CMDB__OBJTYPE__MASTER_RELATION"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__OBJTYPE__RELATION_MASTER" p_bDbFieldNN="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__INSERTION_OBJECT" ident="LC__CMDB__OBJTYPE__INSERTION_OBJECT"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__OBJTYPE__INSERTION_OBJECT" p_bDbFieldNN="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__SHOW_IN_TREE" ident="LC__CMDB__OBJTYPE__SHOW_IN_TREE"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__OBJTYPE__SHOW_IN_TREE"  p_bDbFieldNN="1"}]</td>
    </tr>

    <tr>
        <td class="key vat pt15">[{isys type="f_label" name="C__OBJTYPE__IMG_NAME" ident="LC__CMDB__OBJTYPE__IMG_NAME"}]</td>
        <td class="value">
            <div [{if $editmode}]class="hide"[{/if}]>
                [{isys type="f_dialog" name="C__OBJTYPE__IMG_NAME" id="C__OBJTYPE__IMG_NAME" p_bDbFieldNN=1 disableInputGroup=true p_strStyle='display:none;' p_bEnableMetaMap=false}]
            </div>
            [{if isys_glob_is_edit_mode()}]
                [{isys type="f_text" name="obj-type-image-search" p_strPlaceholder="Filter..."}]
                <br class="cb" />
                <div class="box ml20 mt5 overflow-auto input-size-normal" style="height:200px;" id="objTypeImagesHelp">
                    <table class="contentTable m0 w100 listing hover">
                        <tbody>
                        <tr class="no-hover">
                            <td><h2 class="ml5">[{isys type="lang" ident="LC_UNIVERSAL__CUSTOM_IMAGES"}]</h2></td>
                        </tr>
                        [{foreach $objTypeImages.custom as $url => $image}]
                            <tr>
                                <td class="mouse-pointer[{if $image == $objTypeImage}] selected[{/if}]" title="[{$url}]">
                                    <span><img src="[{isys_application::instance()->container->get('route_generator')->generate('cmdb.object-type.image-name', ['filename' => $image])}]" class="vam ml10 mr10" /> [{$image}]</span>
                                </td>
                            </tr>
                        [{/foreach}]
                        <tr class="no-hover">
                            <td><h2 class="ml5">[{isys type="lang" ident="LC_UNIVERSAL__OLD_IMAGES"}]</h2></td>
                        </tr>
                        [{foreach $objTypeImages.old as $image}]
                            <tr>
                                <td class="mouse-pointer[{if $image == $objTypeImage}] selected[{/if}]" title="[{$image}]">
                                    <span><img src="images/objecttypes/[{$image}]" class="vam ml10 mr10" /> [{$image}]</span>
                                </td>
                            </tr>
                        [{/foreach}]
                        </tbody>
                    </table>
                </div>
            [{/if}]
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__IMG_UPLOAD" ident="LC__CMDB__OBJTYPE__IMAGE_UPLOAD"}]</td>
        <td class="value pl20">[{isys type="f_file_ajax" name="C__OBJTYPE__IMG_UPLOAD" uploadType="cmdb.object-type-image"}]</td>
    </tr>
    <tr>
        <td class="key vat pt15">[{isys type="f_label" name="C__OBJTYPE__ICON" ident="LC__UNIVERSAL__ICON"}]</td>
        <td class="value">
            <div [{if $editmode}]class="hide"[{/if}]>
                [{isys type="f_dialog" name="C__OBJTYPE__ICON" id="C__OBJTYPE__ICON" p_bDbFieldNN=1 disableInputGroup=true p_bEnableMetaMap=false}]
            </div>
            [{if isys_glob_is_edit_mode()}]
            [{isys type="f_text" name="obj-type-icon-search" p_strPlaceholder="Filter..."}]
            <br class="cb" />
            <div class="box ml20 mt5 overflow-auto input-size-normal" style="height:200px;" id="objTypeIconsHelp">
                <table class="contentTable m0 w100 listing hover">
                    <tbody>
                    <tr class="no-hover">
                        <td><h2 class="ml5">[{isys type="lang" ident="LC_UNIVERSAL__CUSTOM_ICONS"}]</h2></td>
                    </tr>
                    [{foreach $objTypeIcons.custom as $url => $icon}]
                        <tr>
                            <td class="mouse-pointer[{if $icon == $objTypeIcon}] selected[{/if}]" title="[{$url}]">
                                <span><img src="[{isys_application::instance()->container->get('route_generator')->generate('cmdb.object-type.icon-name', ['filename' => $icon])}]" class="vam ml10 mr10" /> [{$icon}]</span>
                            </td>
                        </tr>
                    [{/foreach}]
                    <tr class="no-hover">
                        <td><h2 class="ml5">[{isys type="lang" ident="LC_UNIVERSAL__NEW_ICONS"}]</h2></td>
                    </tr>
                    [{foreach $objTypeIcons.new as $url => $icon}]
                        <tr>
                            <td class="mouse-pointer[{if $url == $objTypeIcon}] selected[{/if}]" title="[{$url}]">
                                <span><img src="[{$url}]" class="vam ml10 mr10" /> [{$icon}]</span>
                            </td>
                        </tr>
                    [{/foreach}]
                    <tr class="no-hover">
                        <td><h2 class="ml5">[{isys type="lang" ident="LC_UNIVERSAL__OLD_ICONS"}]</h2></td>
                    </tr>
                    [{foreach $objTypeIcons.old as $url => $icon}]
                    <tr>
                        <td class="mouse-pointer[{if $url == $objTypeIcon}] selected[{/if}]" title="[{$url}]">
                            <span><img src="[{$url}]" class="vam ml10 mr10" /> [{$icon}]</span>
                        </td>
                    </tr>
                    [{/foreach}]
                    </tbody>
                </table>
            </div>
            [{/if}]
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__ICON_UPLOAD" ident="LC__CMDB__OBJTYPE__ICON_UPLOAD"}]</td>
        <td class="value pl20">[{isys type="f_file_ajax" name="C__OBJTYPE__ICON_UPLOAD" uploadType="cmdb.object-type-icon"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__CONST" ident="LC__CMDB__OBJTYPE__CONST"}]</td>
        <td class="value">[{isys type="f_text" name="C__OBJTYPE__CONST"}]</td>
    </tr>
    <tr>
        <td class="key">Default Template</td>
        <td class="value">[{isys type="f_dialog" name="C__CMDB__OBJTYPE__DEFAULT_TEMPLATE" p_arData=$templates p_bDbFieldNN="0"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CMDB__OBJTYPE__USE_TEMPLATE_TITLE" ident="LC__CMDB__OBJTYPE__USE_TEMPLATE_TITLE"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CMDB__OBJTYPE__USE_TEMPLATE_TITLE" p_bDbFieldNN="1"}]</td>
    </tr>
    <tr>
        <td class="category-spacer" colspan="2"><hr /></td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CMDB__OVERVIEW__ENTRY_POINT" ident="LC__CMDB__OVERVIEW__ENTRY_POINT"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CMDB__OVERVIEW__ENTRY_POINT" p_bDbFieldNN="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__MODULE__SEARCH__CATG"}]</td>
        <td class="pl20">
            <table>
                <tr>
                    <td class="vat">
                        <div id="assigned_categories" class="box">
                            <h3 class="bg-neutral-200">
                                <input type="checkbox" [{if !$editmode}]disabled="disabled"[{/if}] title="[{isys type="lang" ident="LC__UNIVERSAL__MARK_ALL"}]" />

                                [{isys type="lang" ident="LC__CMDB__OBJTYPE__ASSIGNED_CATG"}]
                            </h3>
                            <div class="list">
                                <ul>
                                    [{foreach $categoryList as $row}]
                                    <li id="category_[{$row.constant}]" class="[{if $row.selected}]text-bold[{/if}] [{if $row.sticky}]opacity-30[{/if}]">
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="assigned_categories[]"
                                                value="[{$row.constant}]"
                                                data-overview="[{$row.overview}]"
                                                data-directories="[{$row.directory_categories|escape:"javascript"}]"
                                                [{if !$editmode}]disabled="disabled"[{/if}]
                                                [{if $row.sticky}]class="non-clickable"[{/if}]
                                                [{if $row.selected}]checked="checked"[{/if}] />
                                            <span>[{$row.title|escape:"html"}]</span>
                                        </label>
                                    </li>
                                    [{/foreach}]
                                </ul>
                            </div>
                        </div>
                    </td>
                    <td class="pl20 pr20">
                        <img alt=">" src="[{$dir_images}]rsaquo.png" />
                    </td>
                    <td class="vat">
                        <div id="overview_categories" class="box">
                            <h3 class="bg-neutral-200 p5" style="position: relative">
                                <input type="checkbox" [{if !$editmode}]disabled="disabled"[{/if}] title="[{isys type="lang" ident="LC__UNIVERSAL__MARK_ALL"}]" />

                                [{isys type="lang" ident="LC__CMDB__OBJTYPE__CATEGORIES_ON_THE_OVERVIEW"}]
                            </h3>
                            <div class="list">
                                <ul id="overview_categories_list">
                                    [{foreach $overviewCategories as $row}]
                                    <li id="category_ov_[{$row.constant}]" [{if $row.selected}]class="text-bold"[{/if}] [{if $row.specific}]data-specific-category="1"[{/if}]>
                                        [{if $editmode}]<div class="handle"></div>[{/if}]
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="assigned_cat_overview[]"
                                                value="[{$row.constant}]"
                                                [{if !$editmode || !$category_overview_is_active || $row.sticky }]disabled="disabled"[{/if}]
                                                [{if $row.selected}]checked="checked"[{/if}] />
                                            [{if $row.constant == 'C__CATG__GLOBAL' && $row.sticky}]
                                            <input type="hidden" name="assigned_cat_overview[]" value="[{$row.constant}]" checked="checked" />
                                            [{/if}]
                                            <span class>[{$row.title}]</span>
                                        </label>
                                    </li>
                                    [{/foreach}]
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__OBJTYPE__DESCRIPTION" ident="LC__CMDB__OBJTYPE__DESCRIPTION"}]</td>
        <td class="value">
            <span class="value" style="font-weight:normal; font-family:Fixedsys,Courier New,Sans-Serif,Serif,Monospace;">
            [{isys type="f_textarea" name="C__OBJTYPE__DESCRIPTION" p_nRows="6" p_bInfoIconDisabled="1" p_strStyle="font-weight:normal; font-family:Fixedsys,Courier,Sans-Serif,Serif,Monospace;"}]
            </span>
        </td>
    </tr>
</table>

<style type="text/css">
    #assigned_categories,
    #overview_categories {
        width: 350px;
    }

    #assigned_categories *,
    #overview_categories * {
        box-sizing: border-box;
    }

    #assigned_categories h3,
    #overview_categories h3 {
        position: relative;
        padding: 5px;
        border-bottom: 1px solid #c8c9c9; /* neutral-400 */
    }

    #assigned_categories .list,
    #overview_categories .list {
        overflow-x: hidden;
        overflow-y: scroll;
        min-height: 200px;
        max-height: 400px;
    }

    #assigned_categories .list ul,
    #overview_categories .list ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    #assigned_categories .list ul li,
    #overview_categories .list ul li {
        position: relative;
        height: 25px;
        padding: 5px;
        border-bottom: 1px solid #e6e7e7; /* neutral-200 */
        display: flex;
        align-items: center;
    }

    #assigned_categories .list ul li:hover,
    #overview_categories .list ul li:hover {
        background: #e6e7e7; /* neutral-200 */
    }

    #assigned_categories .list ul li label,
    #overview_categories .list ul li label {
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    #overview_categories .list ul li .handle {
        background: transparent url('[{$dir_images}]icons/hatch.gif');
        width: 5px;
        height: 15px;
        cursor: ns-resize;
        margin-right: 4px;
        padding: 4px;
    }

    #assigned_categories .list ul li input,
    #overview_categories .list ul li input {
        margin-right: 5px;
    }

    #assigned_categories .list ul li span,
    #overview_categories .list ul li span {
        display: block;
        width: 300px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>

[{if isys_glob_is_edit_mode()}]
    <script type="text/javascript">
        (function () {
            "use strict";

            var $displayOverviewPage   = $('C__CMDB__OVERVIEW__ENTRY_POINT'),
                $inventoryNumberField  = $('C__OBJTYPE__AUTOMATED_INVENTORY_NO'),
                $placeholderDiv        = $('placeholderHelper'),
                $objTypeImageSearch     = $('obj-type-image-search'),
                $objTypeImages         = $('objTypeImagesHelp').down('tbody'),
                $objTypeIconSearch     = $('obj-type-icon-search'),
                $objTypeIcons          = $('objTypeIconsHelp').down('tbody'),
                specificCategoriesMap  = JSON.parse('[{$specificCategoriesMap}]'),
                categoryHandling,
                i;

            // @see  ID-5238  Empty all "savedCheckbox" arrays - because we do a lot via AJAX this variables might still be set.
            for (i in window) {
                if (i.indexOf('tempObjList_') === 0) {
                    window[i] = [];
                }
            }

            $objTypeImageSearch.on('keyup', function () {
                delay(function () {
                    const query = $objTypeImageSearch.getValue().toLowerCase();

                    $objTypeImages.select('tr:not(.no-hover)')
                        .invoke('removeClassName', 'hide')
                        .filter(function($row) {
                            return $row.innerText.toLowerCase().indexOf(query) < 0;
                        })
                        .invoke('addClassName', 'hide');
                }, 250);
            });

            $objTypeImages.on('click', 'td', function(ev) {
                const $field = ev.findElement('td');

                if (!$field.up('tr.no-hover') && $('C__OBJTYPE__IMG_NAME')) {
                    $objTypeImages.select('td').invoke('removeClassName', 'selected');
                    $field.addClassName('selected');
                    $('C__OBJTYPE__IMG_NAME').setValue($field.readAttribute('title'));
                }
            });

            $objTypeIconSearch.on('keyup', function () {
                delay(function () {
                    const query = $objTypeIconSearch.getValue().toLowerCase();

                    $objTypeIcons.select('tr:not(.no-hover)')
                        .invoke('removeClassName', 'hide')
                        .filter(function($row) {
                            return $row.innerText.toLowerCase().indexOf(query) < 0;
                        })
                        .invoke('addClassName', 'hide');
                }, 250);
            });

            $objTypeIcons.on('click', 'td', function (ev) {
                const $field = ev.findElement('td');

                if (!$field.up('tr.no-hover') && $('C__OBJTYPE__ICON')) {
                    $objTypeIcons.select('td').invoke('removeClassName', 'selected');
                    $field.addClassName('selected');
                    $('C__OBJTYPE__ICON').setValue($field.readAttribute('title'));
                }
            });

            // @see  ID-7709  Because of a "not yet selected" icon or image, this will interrupt the JS.
            try {
                $objTypeIcons.down('.selected').scrollIntoView(true);
                $objTypeImages.down('.selected').scrollIntoView(true);
            } catch (e) {
                // We might not have any selected icons or images yet.
            }

            $('contentWrapper').scrollTop = 0;

            if ($inventoryNumberField && $placeholderDiv)
            {
                $placeholderDiv.on('click', 'td', function (ev) {
                    var $placeholder = ev.findElement('tr').down('code');

                    $inventoryNumberField.setValue($inventoryNumberField.getValue() + ($placeholder.textContent || $placeholder.innerText || $placeholder.innerHTML));
                });
            }

            window.ObjtypeCategories = Class.create({
                initialize: function () {
                    this.$assignAll =  $('assigned_categories').down('h3 input');
                    this.$assignList = $('assigned_categories').down('ul');

                    this.$overviewAll =  $('overview_categories').down('h3 input');
                    this.$overviewList = $('overview_categories').down('ul');

                    this.$specificCategorySelect = $('C__OBJTYPE__CATS_ID');

                    this.setObserver();
                },

                setObserver: function () {
                    var that = this;

                    this.$assignList.on('change', 'input', this.handle_objtype_category.bindAsEventListener(this));

                    this.$assignAll.on('change', function () {
                        var checked = that.$assignAll.checked;

                        that.$assignList.select('input:not(:disabled)').invoke('setValue', checked ? 1 : 0).invoke('simulate', 'change');
                        that.handle_objtype_overview(checked)
                    });

                    this.$overviewList.on('change', 'input', this.handle_objtype_overview_category.bindAsEventListener(this));

                    this.$overviewAll.on('change', function () {
                        that.$overviewList.select('input:not(:disabled)').invoke('setValue', that.$overviewAll.checked ? 1 : 0).invoke('simulate', 'change');
                    });

                    this.$specificCategorySelect.on('change', function (ev) {
                        that.removeSpecificCategories();
                        that.addSpecificCategories();
                        that.resetSortable();
                        return
                    })

                    this.resetSortable();
                },

                removeSpecificCategories: function () {
                    var list = this.$overviewList.select('[data-specific-category]');

                    if (list.length) {
                        list.map((child) => {
                            child.remove();
                        });
                    }
                },

                createSpecificOption: function (categoryId, categoryName) {
                    return new Element('li', {id: 'category_ov_' + categoryId, 'data-specific-category': 1})
                        .insert(new Element('div', {className: 'handle'}))
                        .insert(new Element('label')
                            .update(new Element('input', {
                                type:     'checkbox',
                                name:     'assigned_cat_overview[]',
                                value:    categoryId
                            }))
                            .insert(new Element('span').update(categoryName)));
                },

                addSpecificCategories: function () {
                    var categoryName = this.$specificCategorySelect.down(':selected').innerText,
                        categoryId = this.$specificCategorySelect.getValue(),
                        $currentData,
                        $entry,
                        item;

                    if (categoryId == -1) {
                        return;
                    }

                    if (isNaN(categoryId)) {
                        $currentData = Object.values(specificCategoriesMap).filter(data => {
                            return data.const === categoryId;
                        }).first();
                    } else {
                        $currentData = specificCategoriesMap[categoryId];
                    }

                    // Found during ID-9125: It seems like the 'specificCategoriesMap' variable contains only a very specific subset which will result in error if unchecked here.
                    if ($currentData) {
                        $entry = this.createSpecificOption($currentData['const'], categoryName)

                        this.$overviewList.insert($entry);

                        if ($currentData['children']) {
                            Object.values($currentData['children']).map((child) => {
                                this.$overviewList.insert(this.createSpecificOption(child['const'], child['title']));
                            });
                        }
                    }
                },

                resetSortable: function () {
                    Sortable.destroy('overview_categories_list');

                    Position.includeScrollOffsets = true;

                    Sortable.create('overview_categories_list', {
                        tag:    'li',
                        handle: 'handle'
                    });
                },

                handle_objtype_overview_category: function (ev) {
                    var $checkbox = ev.findElement();

                    if ($checkbox.checked)
                    {
                        $checkbox.up('li').addClassName('text-bold');
                    }
                    else
                    {
                        $checkbox.up('li').removeClassName('text-bold');
                    }
                },

                handle_objtype_overview: function (check) {
                    var $overviewCheckboxes = this.$overviewList.select('input'), i;

                    for (i in $overviewCheckboxes)
                    {
                        if (!$overviewCheckboxes.hasOwnProperty(i))
                        {
                            continue;
                        }

                        if (check)
                        {
                            $overviewCheckboxes[i].enable();
                        }
                        else
                        {
                            $overviewCheckboxes[i].disable();
                        }
                    }
                },

                handle_objtype_category: function (ev) {
                    var $checkbox            = ev.findElement('input'),
                        overview_category    = $checkbox.readAttribute('data-overview'),
                        directory_categories = $checkbox.readAttribute('data-directories'),
                        checkboxValue        = $checkbox.readAttribute('value'),
                        checked              = $checkbox.checked,
                        displayOverview      = $displayOverviewPage.getValue() === '1',
                        newElements          = [], i, $li;

                    if (directory_categories)
                    {
                        newElements = JSON.parse(directory_categories);
                    }

                    if ($checkbox.checked)
                    {
                        $checkbox.up('li').addClassName('text-bold');
                    }
                    else
                    {
                        $checkbox.up('li').removeClassName('text-bold');
                    }

                    if (overview_category == 1)
                    {
                        newElements.push({
                            id:    checkboxValue,
                            title: $checkbox.next('span').innerHTML
                        });
                    }

                    if (checked)
                    {
                        for (i in newElements)
                        {
                            if (!newElements.hasOwnProperty(i))
                            {
                                continue;
                            }

                            $li = new Element('li', {id: 'category_ov_' + newElements[i].id})
                                .insert(new Element('div', {className: 'handle'}))
                                .insert(new Element('label')
                                    .update(new Element('input', {
                                        type:     'checkbox',
                                        disabled: !displayOverview,
                                        name:     'assigned_cat_overview[]',
                                        value:    newElements[i].id
                                    }))
                                    .insert(new Element('span').update(newElements[i].title)));

                            this.$overviewList.insert($li);
                        }
                    }
                    else
                    {
                        if ($('category_ov_' + checkboxValue))
                        {
                            $('category_ov_' + checkboxValue).remove();
                        }

                        for (i in newElements)
                        {
                            if (!newElements.hasOwnProperty(i))
                            {
                                continue;
                            }

                            if ($('category_ov_' + newElements[i].id))
                            {
                                $('category_ov_' + newElements[i].id).remove();
                            }
                        }
                    }

                    this.resetSortable();
                }
            });

            categoryHandling = new window.ObjtypeCategories();

            if ($displayOverviewPage)
            {
                $displayOverviewPage.on('change', function () {
                    categoryHandling.handle_objtype_overview(!!parseInt(this.value));
                });
            }

            idoit.callbackManager.registerCallback('smarty-ajax-file-upload', function(json) {
                const tmpScrollTop = $('contentWrapper').scrollTop;

                switch (json.type) {
                    case 'cmdb.object-type-icon':
                        afterUploadHandle(json, $objTypeIcons, $('C__OBJTYPE__ICON'));
                        $objTypeIcons.down('.selected').scrollIntoView(true);
                        break;

                    case 'cmdb.object-type-image':
                        afterUploadHandle(json, $objTypeImages, $('C__OBJTYPE__IMG_NAME'));
                        $objTypeImages.down('.selected').scrollIntoView(true);
                        break;
                }

                $('contentWrapper').scrollTop = tmpScrollTop;
            });

            function afterUploadHandle(json, $table, $hiddenSelect) {
                const fileName = json.callbackResult.split(':').last();
                const $newRow = new Element('td', {title: fileName})
                    .update(new Element('span')
                        .update(new Element('img', {className: 'vam ml10 mr10', src: json.callbackResult}))
                        .insert(fileName));

                $hiddenSelect.insert(new Element('option', {value: fileName}).update(fileName));

                $table.down('tr').insert({ after: new Element('tr').update($newRow) });

                $newRow.simulate('click');
            }
        }());
    </script>
[{/if}]
