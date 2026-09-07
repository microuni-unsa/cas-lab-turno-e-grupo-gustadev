<div class="logo-top-left">
    <a href="[{$config.www_dir}]"><img src="[{$mainLogo}]" alt="i-doit" /></a>
</div>

<div class="main-menu">
    <ul id="mainMenu">
        [{foreach $mainMenu as $id => $link}]
        <li id="menuItem_[{$id}]" class="[{$link.3}][{if $activeMainMenuItem == $id}] active[{/if}]">
            <a href="[{$link.0}]" onclick="[{$link.2}]" title="[{isys type="lang" ident=$link.1}]">[{isys type="lang" ident=$link.1}]</a>
        </li>
        [{/foreach}]
    </ul>

    [{if $objectTypeGroupsAsDropDown}]
    <ul id="object-type-group-dropdown" class="menu-dropdown hide">
        [{foreach $objectTypeGroups as $id => $link}]
        <li id="menuItem_[{$id}]" class="[{$link.3}][{if $activeMainMenuItem == $id}] active[{/if}]">
            <a href="[{$link.0}]" onclick="[{$link.2}]" title="[{isys type="lang" ident=$link.1}]">[{isys type="lang" ident=$link.1}]</a>
        </li>
        [{/foreach}]
    </ul>
    [{/if}]
    <div id="module-dropdown" class="hide"></div>
    <div id="add-ons-dropdown" class="hide"></div>
</div>

<div id="searchBar">
    [{if isys_auth_search::instance()->is_allowed_to(isys_auth::VIEW, 'search')}]
    <input
        class="input input-block"
        type="text"
        name="q"
        id="globalSearch"
        placeholder="[{isys type="lang" ident="LC__UNIVERSAL__SEARCH"}]"
        autocapitalize="off"
        autocomplete="off"
        autosave="idoit_search"
        spellcheck="false"
        value="[{$searchTerm|escape}]" />

    <img src="[{$dir_images}]axialis/zoom-light.svg" />

    <script type="text/javascript">
        {
            'use strict';

            var cachedBackend = new Autocompleter.Cache(
                function (searchString, suggest, options) {
                    new Ajax.Request(www_dir + 'search', {
                        method:     'get',
                        parameters: {
                            q:    searchString.trim(),
                            rand: (new Date()).getTime()
                        },
                        onSuccess:  function (response) {
                            suggest(response.responseJSON);
                        }
                    });
                },
                {
                    minChars:                 3,
                    choices:                  125
                }
            );
            idoit.cachedLookup = cachedBackend.lookup.bind(cachedBackend);

            var rnd        = Math.floor(Math.random() * 9999),
                el_choices = 'theChoices' + rnd;

            document.body.insert(
                new Element('div', {
                    'id':        el_choices,
                    'className': 'autocomplete global-search'
                })
            );

            var _completer = new Autocompleter.Json(
                $('globalSearch'), el_choices, idoit.cachedLookup, {
                    frequency:         .3,
                    choices:           50,
                    minChars:          parseInt('[{isys_tenantsettings::get('search.minlength.search-string', 3)}]'),
                    searchPlaceHolder: '[{isys type="lang" ident="LC__MODULE__SEARCH__FOR"}]',
                    updateElement:     function (li) {
                        // override default behaviour
                        var link = li.getAttribute('data-link'), search = li.getAttribute('data-search');

                        if (link) {
                            document.location.href = encodeURI(link);
                            this.selectedItem = li;
                        }

                        if (search == '1') {
                            document.location.href = www_dir + 'search?q=' + this.element.value;
                            return true;
                        }
                    }
                }
            );
        }

        $('globalSearch').on('keydown', function (event) {
            var el = event.findElement('input');

            if ((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13)) {
                event.preventDefault();

                if (el.value.search('#') == 0) {
                    window.location.href = www_dir + '?objID=' + el.value.replace('#', '');
                } else {
                    // Submit search only if there is no item selected
                    if (_completer.selectedItem == null) {
                        window.location.href = www_dir + 'search?q=' + encodeURIComponent(el.value);
                    }
                }
            }
        });
    </script>
    [{else}]
    <!-- Fix JS error if a user has no 'search' auth -->
    <div id="globalSearch"></div>
    [{/if}]
</div>

<div class="help-menu">
    <button type="button" class="btn">
        <img src="[{$dir_images}]axialis/button-help-dark.svg" />
    </button>

    <ul class="menu-dropdown hide">
        <li>
            <a href="[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__OPEN_KB_URL"}]" target="_blank">
                <img class="prefix-icon" src="[{$dir_images}]axialis/web-email/external-link.svg" />
                <span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__OPEN_KB"}]</span>
            </a>
        </li>
        [{if !isys_application::isPro()}]
        <li>
            <a href="https://community.i-doit.com/" target="_blank">
                <img class="prefix-icon" src="[{$dir_images}]axialis/web-email/external-link.svg" />
                <span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__OPEN_COMMUNITY"}]</span>
            </a>
        </li>
        [{/if}]
        [{if isys_application::isPro()}]
        <li>
            <a href="[{$g_link__administration}]/help">
                <img class="prefix-icon" src="[{$dir_images}]axialis/basic/button-help.svg" />
                <span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__OPEN_HELP"}]</span>
            </a>
        </li>
        <li>
            <a href="[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__CONTACT_SALES_URL"}]" target="_blank">
                <img class="prefix-icon" src="[{$dir_images}]axialis/web-email/external-link.svg" />
                <span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__CONTACT_SALES"}]</span>
            </a>
        </li>
        <li>
            <a href="tel:+4921169931185">
                <img class="prefix-icon" src="[{$dir_images}]axialis/phone-filled-dark.svg" />
                <span>+49 211 699 31-185</span>
            </a>
        </li>
        [{/if}]

        [{foreach $additionalHelpItems as $item}]
        [{$item}]
        [{/foreach}]
    </ul>
</div>

[{if is_array($languages) && count($languages)}]
<div class="language-switch">
    <button type="button" class="btn">
        <img src="[{$dir_images}][{$activeLanguage.icon}]" />
        <span>[{$activeLanguage.short}]</span>
        <img src="[{$dir_images}]axialis/user-interface/angle-down-small-outline.svg" alt="toggle" />
    </button>

    <ul class="menu-dropdown hide">
        [{foreach $languages as $language}]
        <li>
            <a href="[{$language.url}]">
                <img class="prefix-icon" src="[{$dir_images}][{$language.icon}]" />
                <span>[{$language.short}]</span>
            </a>
        </li>
        [{/foreach}]
    </ul>
</div>
[{/if}]

<div class="pr5 nowrap">[{$user_name|default:"Unknown"}] @</div>

[{if count($tenantList) > 1}]
<select id="mandator_id" class="input input-block" style="max-width: 130px">
    [{foreach $tenantList as $tenant}]
    <option value="[{$tenant->getId()}]" [{if $activeTenant == $tenant->getId()}]selected[{/if}]>[{$tenant->getTitle()}]</option>
    [{/foreach}]
</select>

<script>
    (function () {
        $('mandator_id').on('change', function () {
            new Ajax.Call('?call=fetch_mandators', {
                parameters: { mandator_id: $F('mandator_id') },
                onComplete: function () { document.location = window.www_dir; }
            });
        })
    })()
</script>
[{else}]
<strong class="nowrap">[{$g_mandant_name}]</strong>
[{/if}]

<div class="user-avatar">
    [{if !$hide_survey_notification}]
    <div class="notification-bubble">1</div>
    [{/if}]
    <img class="avatar" src="[{$user_image_url}]" alt="" />
    <ul class="menu-dropdown hide">
        <li>
            <a href="[{$g_link__settings}]">
                <img class="prefix-icon" src="[{$dir_images}]axialis/basic/gear.svg" alt="" />
                <span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__TITLE_ADMINISTRATION"}]</span>
            </a>
        </li>
        [{if isys_application::isPro()}]
        <li>
            <a href="[{$g_link__administration}]">
                <img class="prefix-icon" src="[{isys_application::instance()->www_path}]src/classes/modules/synetics_admin/templates/img/subscription-and-add-ons.svg" alt="" />
                <span>[{isys type="lang" ident="LC__SYNETICS_ADMIN"}]</span>
            </a>
        </li>
        [{/if}]
        <li [{if !$hide_survey_notification}]class="highlighted"[{/if}]>
            <a href="[{if $isTrial}]https://survey-trial.i-doit.com/[{else}]https://survey.i-doit.com/[{/if}]?lang=[{$languageAbbreviationWithoutCustom}]" target="_blank">
                <img class="prefix-icon" src="[{$dir_images}]axialis/multimedia/talk.svg" alt="" />
                <span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__CUSTOMER_SURVEY"}]</span>
            </a>
        </li>
        <li>
            <a href="[{$g_link__logout}]">
                <img class="prefix-icon" src="[{$dir_images}]axialis/basic/logout.svg" alt="" />
                <span>[{isys type="lang" ident="LC__NAVIGATION__MAINMENU__TITLE_LOGOUT"}]</span>
            </a>
        </li>
    </ul>
</div>
