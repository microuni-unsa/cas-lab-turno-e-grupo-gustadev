const $top = $('top');
const $mainMenu = $('mainMenu');
const topHeight = $top.getHeight();
const $addOnsMenu = $mainMenu.down('.add-ons');
const $addOnsMenuList = $('add-ons-dropdown');
const $extrasMenu = $mainMenu.down('.extras');
const $extrasMenuList = $('module-dropdown');
const $languageSwitcher = $top.down('.language-switch');
const $helpMenu = $top.down('.help-menu');
const $avatarPicture = $top.down('.user-avatar');
const $objectTypeGroup = $mainMenu.down('.object-type-group');
const $objectTypeGroupList = $('object-type-group-dropdown');
const $globalSearch = $('globalSearch');
// 0 = Always show all object type groups, 1 = always show the 'object type group' dropdown, 2 = responsive.
const menuType = +'[{$objectTypeGroupsAsDropDown}]';
let objectTypeGroupDropdown;

if ($languageSwitcher) {
    new DropDown($languageSwitcher, $languageSwitcher.down('ul'));
}

if ($helpMenu) {
    new DropDown($helpMenu, $helpMenu.down('ul'));
}

new DropDown($avatarPicture, $avatarPicture.down('ul'), {keepX: true, onClose: () => {
    const $notificationBubble = $avatarPicture.down('.notification-bubble');

    // @see ID-10803 Take care of notification bubble, as soon as the user clicks the avatar.
    if ($notificationBubble) {
        new Ajax.Request(idoit.Router.getRoute('system.set-user-setting'), {
            method:     'POST',
            parameters: {
                key: 'gui.hide-survey-notification',
                value: 1
            },
            onComplete: (xhr) => {
                $notificationBubble.remove();

                if ($avatarPicture.down('.highlighted')) {
                    $avatarPicture.down('.highlighted').removeClassName('highlighted');
                }
            }
        });
    }
}});

if ($objectTypeGroup && $objectTypeGroupList) {
    objectTypeGroupDropdown = new DropDown($objectTypeGroup, $objectTypeGroupList);

    $objectTypeGroupList.on('click', 'a', function () {
        $objectTypeGroup.fire('popup:close');
    });

    if ($objectTypeGroupList.down('.active')) {
        $objectTypeGroup.addClassName('active');
    }
}

const openDropDown = function ($dropDown) {
    $dropDown
        .setStyle({
            top:     (topHeight - 8) + 'px',
            opacity: 0,
            display: null
        })
        .removeClassName('hide');

    new Effect.Morph($dropDown, {
        style:    'opacity: 1; top: ' + (topHeight - 4) + 'px',
        duration: 0.2
    });
};

const closeDropDown = function ($dropDown) {
    new Effect.Morph($dropDown, {
        style:       'opacity: 0; top: ' + (topHeight - 8) + 'px',
        duration:    0.2,
        afterFinish: function () {
            $dropDown.addClassName('hide');
        }
    });
};

$mainMenu.select('li a').invoke('on', 'click', function (ev) {
    var $li = ev.findElement('a').up('li');
    $mainMenu.select('li').invoke('removeClassName', 'active');
    $li.addClassName('active');

    if (!$li.hasClassName('extras') && $extrasMenuList.visible()) {
        $extrasMenuList.fire('menu:close');
    }

    if (!$li.hasClassName('add-ons') && !$addOnsMenuList.hasClassName('hide')) {
        $addOnsMenuList.fire('menu:close');
    }
});

const renderDropdown = ($container, items) => {
    if (items.length === 0) {
        return;
    }

    const $entries = new Element('ul', {className: 'menu-dropdown'});

    for (let i in items) {
        if (!items.hasOwnProperty(i)) {
            continue;
        }

        $entries.insert(new Element('li')
            .update(new Element('a', {href: items[i].url})
                .update(new Element('img', { src: items[i].icon, className: 'prefix-icon', alt: '' }))
                .insert(new Element('span').update(items[i].title))));
    }

    $container.insert($entries);
}

if ($extrasMenu && $extrasMenuList) {
    $extrasMenu.on('click', function (e) {
        e.preventDefault();

        if (!$extrasMenuList.hasClassName('hide')) {
            $extrasMenuList.fire('menu:close');
            return;
        }

        if (!$extrasMenuList.down('ul')) {
            new Ajax.Request(window.www_dir + 'ajax/load-modules', {
                method:     'GET',
                onComplete: (xhr) => {
                    if (!is_json_response(xhr, true)) {
                        return;
                    }

                    const json = xhr.responseJSON;

                    if (!json.success) {
                        idoit.Notify.error('An error occured while loading the modules:' + json.message, {sticky: true});
                        return;
                    }

                    renderDropdown($extrasMenuList, json.data);
                    $extrasMenuList.fire('menu:open');
                }
            });
        } else {
            $extrasMenuList.fire('menu:open');
        }
    });

    $extrasMenuList.on('menu:open', function () {
        openDropDown($extrasMenuList.setStyle('left:' + $extrasMenu.offsetLeft + 'px'));
    });

    $extrasMenuList.on('menu:close', function () {
        closeDropDown($extrasMenuList);
    });
}

if ($addOnsMenu && $addOnsMenuList) {
    $addOnsMenu.on('click', function (e) {
        e.preventDefault();

        if (!$addOnsMenuList.hasClassName('hide')) {
            $addOnsMenuList.fire('menu:close');
            return;
        }

        if (!$addOnsMenuList.down('ul')) {
            new Ajax.Request(window.www_dir + 'ajax/load-addons', {
                method:     'GET',
                onComplete: (xhr) => {
                    if (!is_json_response(xhr, true)) {
                        return;
                    }

                    const json = xhr.responseJSON;

                    if (!json.success) {
                        idoit.Notify.error('An error occured while loading the add-ons:' + json.message, {sticky: true});
                        return;
                    }

                    renderDropdown($addOnsMenuList, json.data)

                    let $list = $addOnsMenuList.down('ul');

                    if ($list) {
                        $list.insert(new Element('li').update(new Element('hr')));
                    } else {
                        $list = new Element('ul', { className: 'menu-dropdown' });

                        $addOnsMenuList.update($list);
                    }

                    $list.insert(new Element('li')
                        .update(new Element('a', { href: window.www_dir + 'center/services', target: '_blank' })
                            .update(new Element('img', { src: window.www_dir + 'src/classes/modules/synetics_admin/templates/img/subscription-and-add-ons.svg', className: 'prefix-icon', alt: '' }))
                        .insert(new Element('span').update('[{isys type="lang" ident="LC__SYNETICS_ADMIN"}]'))));

                    $addOnsMenuList.update($list);
                    $addOnsMenuList.fire('menu:open');
                }
            });
        } else {
            $addOnsMenuList.fire('menu:open');
        }
    });

    $addOnsMenuList.on('menu:open', function () {
        openDropDown($addOnsMenuList.setStyle('left:' + $addOnsMenu.offsetLeft + 'px'));
    });

    $addOnsMenuList.on('menu:close', function () {
        closeDropDown($addOnsMenuList);
    });
}

if (dragBar) {
    const dragBarObj = new dragBar({
        dragContainer:  'draggableBar',
        leftContainer:  'menuTreeOn',
        rightContainer: 'contentArea',
        moveInfoBox:    true,
        defaultWidth:   '[{$menu_width}]'
    });

    dragBarObj.callback_save = function () {
        new Ajax.Request('?call=menu&ajax=1&func=save_menu_width', {
            parameters: {
                menu_width: $('menuTreeOn').getWidth()
            },
            method:     'post'
        });
    };
}

// Hide various dropdowns if the user clicks anywhere else.
document.body.on('click', function (ev) {
    if ($extrasMenuList && !Element.up(ev.target, '#module-dropdown') && !Element.up(ev.target, '.extras') && !ev.target.matches('.extras')) {
        $extrasMenuList.fire('menu:close');
    }

    if ($addOnsMenuList && !Element.up(ev.target, '#add-ons-dropdown') && !Element.up(ev.target, '.add-ons') && !ev.target.matches('.add-ons')) {
        $addOnsMenuList.fire('menu:close');
    }
});

// Create a global listener to set tooltips to all icon buttons.
$('body').on('update:tooltips', function () {
    Tips.hideAll();

    $$('[data-tooltip]').each(function ($el) {
        new Tip($el, $el.readAttribute('title').replaceAll(/\n/g, '<br />'), {
            stem:              false,
            style:             'darkgrey',
            positionByElement: true,
            offsetX:           0,
            offsetY:           4,
            onBeforeShow:      function () {
                $el.writeAttribute('data-title', $el.readAttribute('title'));
                $el.removeAttribute('title');
            },
            onAfterHide:       function () {
                if (!$el) {
                    return;
                }

                const hasTitle = $el.hasAttribute('title') && !$el.readAttribute('title').blank();
                const hasDataTitle = $el.hasAttribute('data-title') && !$el.readAttribute('data-title').blank();

                if (!hasTitle && hasDataTitle) {
                    $el.writeAttribute('title', $el.readAttribute('data-title'));
                }
            }
        });
    });
});

// @see ID-9008 Implement responsive menu.
const menuResize = function (initial) {
    // If the object type group should never be collapsed, we only need to remove the 'object type group' menu item.
    if (menuType === 0) {
        $objectTypeGroup.addClassName('hide');
        return;
    }

    // If the object type group should be collapsed, we'll simply do that.
    if (menuType === 1) {
        // @see ID-9266 Un-hide the object-type-group dropdown.
        $objectTypeGroup.removeClassName('hide');
        $mainMenu.addClassName('collapsed');
        return;
    }

    const isSmall = $globalSearch.getWidth() < 150;
    const currentWidth = $top.getWidth();

    // Here we save the 'collapse threshold' - we use the size of the global size to identify the switch.
    if ($mainMenu.retrieve('collapse-threshold', 0) === 0 && isSmall) {
        // We calculate a custom threshold by using the right client rect of the avatar picture + the global search width delta (because it was probably squished).
        let calculatedThreshold = $avatarPicture.getBoundingClientRect().right + (150 - $globalSearch.getWidth());

        // The small calculation should help when i-doit was opened on a small screen and then expanded.
        $mainMenu.store('collapse-threshold', Math.max(currentWidth, calculatedThreshold));
    }

    const collapseThreshold = $mainMenu.retrieve('collapse-threshold', 0);

    // If the 'collapse threshold' is set and it the current screen width drops below it, we 'collapse' the main menu.
    if (collapseThreshold && currentWidth < collapseThreshold) {
        $mainMenu.addClassName('collapsed');

        if ($objectTypeGroup) {
            $objectTypeGroup.removeClassName('hide');
        }
    } else {
        $mainMenu.removeClassName('collapsed');

        if ($objectTypeGroup) {
            $objectTypeGroup.addClassName('hide');
        }

        if (objectTypeGroupDropdown) {
            objectTypeGroupDropdown.closeDropDown();
        }
    }
}

Event.observe(window, 'resize', menuResize);

// Write tooltips after 0.5 seconds (just in case of some slow code or ajax).
setTimeout(function () {
    $('body').fire('update:tooltips');
}, 500);

// @see ID-9153 Trigger the 'menu resize' callback with a delay, if the 'automatic' option is selected.
setTimeout(function () {
    menuResize();
}, menuType === 2 ? 250 : 0);
