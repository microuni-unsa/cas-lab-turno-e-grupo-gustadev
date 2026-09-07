<input type="hidden" id="mydoitAction" name="mydoitAction" />

<button type="button" class="btn btn-secondary m5 fr" onclick="mydoit_trigger();">
    <img src="[{$dir_images}]axialis/user-interface/window-control-close.svg" alt="" />
</button>

[{if file_exists('src/themes/default/smarty/templates/content/status.tpl')}]
	[{include file="content/status.tpl"}]
[{/if}]

<h2 class="ml20 mt20 mb10">
    <span class="vam">[{isys type="lang" ident="LC__MYDOIT__BOOKMARKS"}]</span>
</h2>

<div class="ml20 mr20">
    <button type="button" class="btn" onClick="mydoit_addBookmark();">
        <img src="[{$dir_images}]axialis/basic/symbol-add.svg" alt="" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
    </button>

    <button type="button" class="btn" onClick="mydoit_deleteBookmark();">
        <img src="[{$dir_images}]axialis/industry-manufacturing/waste-bin.svg" alt="" /><span>[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DELETE"}]</span>
    </button>

    <table class="listing mt10">
        [{if $mydoit.bookmarkCount ne 0}]
        [{foreach from=$mydoit.bookmarkList key="id" item="bookmark"}]
        <tr>
            <td style="width:15px;">
                <input type="checkbox" name="mydoitSelection[[{$id}]]" />
            </td>
            <td>
                <a href="[{$bookmark.link}]">[{$bookmark.text}]</a>
            </td>
        </tr>
        [{/foreach}]
        [{else}]
        <tr>
            <td colspan="2" class="p5">
                <span>[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
            </td>
        </tr>
        [{/if}]
    </table>
</div>

<h2 class="ml20 mt20 mb10">
    <span class="vam">[{isys type="lang" ident="LC__WORKFLOWS__MY"}]</span>
</h2>

[{if isys_module_manager::instance()->is_active('workflow')}]
<div id="tasks" class="ml20 mr20">
    <h3 class="ml5 mt5">
        <span class="vam">[{isys type="lang" ident="LC__WORKFLOW__ASSIGNED_TASKS"}]</span>
    </h3>
    [{counter start=0 print=false}]

    [{if is_array($g_tasks__assigned) && count($g_tasks__assigned)>0}]
    [{foreach from=$g_tasks__assigned item=l_my_tasks}]
    <ul>
        <li><strong class="mr5">[{counter}].</strong> [{$l_my_tasks.date}]</li>
        <li><a href="[{$l_my_tasks.link}]">[{$l_my_tasks.title}]</a></li>
    </ul>
    [{/foreach}]
    [{else}]
    <ul><li>[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</li></ul>
    [{/if}]

    <h3 class="ml5 mt20">
        <span class="vam">[{isys type="lang" ident="LC__WORKFLOW__ACTION__TYPE__ACCEPT"}]</span>
    </h3>
    [{counter start=0 print=false}]

    [{if is_array($g_tasks__accepted) && count($g_tasks__accepted)>0}]
    [{foreach from=$g_tasks__accepted item=l_ac_tasks}]
    <ul>
        <li><strong class="mr5">[{counter}].</strong> [{$l_ac_tasks.date}]</li>
        <li><a href="[{$l_ac_tasks.link}]">[{$l_ac_tasks.title}]</a></li>
    </ul>
    [{/foreach}]
    [{else}]
    <ul><li>[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</li></ul>
    [{/if}]

    <h3 class="ml5 mt20">
        [{isys type="lang" ident="LC__WORKFLOW__CREATED_TASKS"}]
    </h3>
    [{if is_array($g_tasks__created) && count($g_tasks__created)>0}]
    [{foreach from=$g_tasks__created item=l_my_tasks}]
    <ul>
        <li><strong class="mr5">[{counter}].</strong> [{$l_my_tasks.date}]</li>
        <li><a href="[{$l_my_tasks.link}]">[{$l_my_tasks.title}]</a></li>
    </ul>
    [{/foreach}]
    [{else}]
    <ul><li>[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</li></ul>
    [{/if}]
</div>
[{/if}]
