/**
 * Attribute selection class for the attribute-settings feature.
 */
window.FileBrowser = Class.create({
    initialize: function ($modal, options) {
        this.$modalRoot = $modal;

        this.$leftPanel = this.$modalRoot.down('.left-panel');
        this.$leftPanelLoadingIndicator = this.$leftPanel.down('.loading-indicator');
        this.$categoryFilterContainer = this.$leftPanel.down('.search-bar');
        this.$categoryListContainer = this.$leftPanel.down('.category-list-content');

        this.$rightPanel = this.$modalRoot.down('.right-panel');
        this.$rightPanelHeadline = this.$rightPanel.down('strong');
        this.$rightPanelLoadingIndicator = this.$rightPanel.down('.loading-indicator');
        this.$fileListFilterContainer = this.$rightPanel.down('.search-bar');

        this.$fileListHeader = this.$rightPanel.down('table thead[data-table="table-header"]');
        this.$fileListBody = this.$rightPanel.down('table tbody[data-table="table-body"]');

        this.options = Object.extend({ selectedFile: null }, options || {});

        this.selectedCategory = 'all';
        this.categoryFilterTerm = null;
        this.fileFilterTerm = null;
        this.orderBy = 'fileName';
        this.orderDirection = 'asc';

        this.setObserver();
        this.loadCategories();
    },

    setObserver: function () {
        this.$categoryListContainer.on('click', 'li', (ev) => {
            const $selectedCategory = ev.findElement('li');

            this.$categoryListContainer.select('.selected').invoke('removeClassName', 'selected');

            this.selectedCategory = $selectedCategory.addClassName('selected').readAttribute('data-category');
            this.$rightPanelHeadline.update($selectedCategory.textContent);

            this.loadFiles();
        });

        this.$fileListBody.on('click', 'td', (ev) => {
            ev.findElement('tr').down('input').setValue(1).simulate('change');
        });

        this.$fileListBody.on('change', 'input', (ev) => {
            this.options.selectedFile = +ev.findElement().getValue();
        });

        this.$fileListHeader.on('click', 'th', (ev) => {
            const $th = ev.findElement('th');

            this.orderDirection = 'asc';

            if (this.orderBy === $th.readAttribute('data-order-field')) {
                this.orderDirection = $th.readAttribute('data-order-direction') === 'asc' ? 'desc' : 'asc';
            }

            this.orderBy = $th.readAttribute('data-order-field');

            this.loadFiles();
        })

        // Category filter.
        this.$categoryFilterContainer.on('keydown', (ev) => {
            if (ev.keyCode === Event.KEY_RETURN) {
                ev.preventDefault();
            }

            delay(this.categoryFilter.bind(this), 200);
        });

        this.$categoryFilterContainer.on('click', 'img', (ev) => {
            if (!ev.findElement('img').hasClassName('mouse-pointer')) {
                return;
            }

            this.$categoryFilterContainer.down('input').setValue('');
            this.categoryFilter();
        });

        // File filter.
        this.$fileListFilterContainer.on('keydown', (ev) => {
            if (ev.keyCode === Event.KEY_RETURN) {
                ev.preventDefault();
            }

            delay(this.fileFilter.bind(this), 200);
        });

        this.$fileListFilterContainer.on('click', 'img', (ev) => {
            if (!ev.findElement('img').hasClassName('mouse-pointer')) {
                return;
            }

            this.$fileListFilterContainer.down('input').setValue('');
            this.fileFilter();
        });
    },

    setSelectedFile: function (file) {
        this.options.selectedFile = file;
    },

    getSelectedFile: function () {
        const selectedFile = this.files.filter((file) => file.objectId === this.options.selectedFile);

        if (selectedFile.length === 0) {
            return null;
        }

        return selectedFile[0];
    },

    categoryFilter: function () {
        const searchTerm = this.$categoryFilterContainer.down('input').getValue().toLowerCase().trim();

        if (searchTerm.blank() || searchTerm.length < 3) {
            this.categoryFilterTerm = null;
            this.$categoryFilterContainer.down('img')
                .removeClassName('mouse-pointer')
                .writeAttribute('src', window.dir_images + 'axialis/basic/zoom.svg');

            this.processCategoryFilter();
            return;
        }

        this.categoryFilterTerm = searchTerm;
        this.$categoryFilterContainer.down('img')
            .addClassName('mouse-pointer')
            .writeAttribute('src', window.dir_images + 'axialis/basic/symbol-cancel-outline.svg');

        this.processCategoryFilter();
    },

    processCategoryFilter: function () {
        const $categories = this.$categoryListContainer.select('li:not([data-category="all"])');

        if (this.categoryFilterTerm === null) {
            $categories.invoke('removeClassName', 'hide');
            return;
        }

        $categories
            .invoke('addClassName', 'hide')
            .filter($li => $li.innerText.toLowerCase().trim().includes(this.categoryFilterTerm))
            .invoke('removeClassName', 'hide');
    },

    fileFilter: function () {
        const searchTerm = this.$fileListFilterContainer.down('input').getValue().toLowerCase().trim();

        if (searchTerm.blank() || searchTerm.length < 3) {
            this.fileFilterTerm = null;
            this.$fileListFilterContainer.down('img')
                .removeClassName('mouse-pointer')
                .writeAttribute('src', window.dir_images + 'axialis/basic/zoom.svg');

            this.processFileFilter();
            return;
        }

        this.fileFilterTerm = searchTerm;
        this.$fileListFilterContainer.down('img')
            .addClassName('mouse-pointer')
            .writeAttribute('src', window.dir_images + 'axialis/basic/symbol-cancel-outline.svg');

        this.processFileFilter();
    },

    processFileFilter: function () {
        const $rows = this.$fileListBody.select('tr');

        if (this.fileFilterTerm === null) {
            $rows.invoke('removeClassName', 'hide');
            return;
        }

        $rows
            .invoke('addClassName', 'hide')
            .filter(($row) => $row.select('td').filter(($td) => $td.innerText.toLowerCase().trim().includes(this.fileFilterTerm)).length > 0)
            .invoke('removeClassName', 'hide');
    },

    getSelectedCategory: function () {
        return this.selectedCategory;
    },

    setSelectedCategory: function (category) {
        const $selectedCategory = this.$categoryListContainer.down('li[data-category="' + category + '"]');

        this.$categoryListContainer.select('.selected').invoke('removeClassName', 'selected');

        if (!$selectedCategory) {
            return;
        }

        this.selectedCategory = $selectedCategory.addClassName('selected').readAttribute('data-category');
        this.$rightPanelHeadline.update($selectedCategory.textContent);

        this.loadFiles();
    },

    loadCategories: function (preselectCategory) {
        this.$leftPanelLoadingIndicator.removeClassName('hide');

        new Ajax.Request(idoit.Router.getRoute('cmdb.browse-files.categories'), {
            method:     'get',
            onComplete: function (xhr) {
                if (!is_json_response(xhr, true)) {
                    return;
                }

                const json = xhr.responseJSON;

                if (!json.success) {
                    idoit.Notify.error(json.message, {sticky: true});
                    return;
                }

                this.categories = json.data;
                this.$leftPanelLoadingIndicator.addClassName('hide');

                this.renderCategories(preselectCategory);
            }.bind(this)
        });
    },

    renderCategories: function (preselectCategory) {
        this.$categoryListContainer
            .update(new Element('li', { 'data-category': 'all' })
                .update(new Element('div').update(idoit.Translate.get('LC_FILEBROWSER__ALL_FILES'))));

        for (let i in this.categories) {
            if (!this.categories.hasOwnProperty(i)) {
                continue;
            }

            this.$categoryListContainer
                .insert(new Element('li', { 'data-category': this.categories[i].id })
                    .update(new Element('div').update(this.categories[i].title)));
        }

        if (this.$categoryListContainer.down('[data-category="' + (preselectCategory || this.selectedCategory) + '"]')) {
            this.$categoryListContainer.down('[data-category="' + (preselectCategory || this.selectedCategory) + '"]').simulate('click')
        } else {
            this.$categoryListContainer.down('[data-category="all"]').simulate('click');
        }
    },

    loadFiles: function () {
        this.$rightPanelLoadingIndicator.removeClassName('hide');

        new Ajax.Request(idoit.Router.getRoute('cmdb.browse-files.files', { category: this.selectedCategory }), {
            method:     'get',
            parameters: {
                'order-by': this.orderBy,
                'order-direction': this.orderDirection
            },
            onComplete: function (xhr) {
                if (!is_json_response(xhr, true)) {
                    return;
                }

                const json = xhr.responseJSON;

                if (!json.success) {
                    idoit.Notify.error(json.message, {sticky: true});
                    return;
                }

                this.files = json.data;
                this.$rightPanelLoadingIndicator.addClassName('hide');

                this.renderFiles();
            }.bind(this)
        });
    },

    renderFiles: function () {
        this.$fileListBody.update();

        for (let i in this.files) {
            if (!this.files.hasOwnProperty(i)) {
                continue;
            }

            const isSelected = this.files[i].objectId === this.options.selectedFile;

            this.$fileListBody
                .insert(new Element('tr')
                    .update(new Element('td').update(new Element('input', {
                        type:    'radio',
                        name:    'file-browser-selection',
                        value:   this.files[i].objectId,
                        checked: isSelected
                    })))
                    .insert(new Element('td').update(new Element('span', {className: 'overflowable'}).update(this.files[i].fileName)))
                    .insert(new Element('td').update(new Element('span', {className: 'overflowable'}).update(this.files[i].objectTitle)))
                    .insert(new Element('td').update(new Element('span', {className: 'overflowable'}).update(this.files[i].categoryTitle)))
                    .insert(new Element('td').update(new Element('span', {className: 'overflowable'}).update(this.files[i].uploadUserObjectTitle)))
                    .insert(new Element('td').update(new Element('span', {className: 'overflowable'}).update(this.files[i].revision)))
                    .insert(new Element('td').update(new Element('span', {className: 'overflowable'}).update(this.files[i].uploadDate))));
        }

        this.processFileFilter();
        this.updateHeader();
    },

    updateHeader: function () {
        const asc = idoit.Translate.get('LC__CMDB__LIST_ORDER_ASCENDING');
        const desc = idoit.Translate.get('LC__CMDB__LIST_ORDER_DESCENDING');

        this.$fileListHeader.select('th[data-order-field]').invoke('removeClassName', 'ordered').each(($th) => {
            const activeColumn = $th.readAttribute('data-order-field') === this.orderBy;
            const orderDirection = activeColumn ? this.orderDirection : 'asc';
            const orderImage = orderDirection === 'asc' ? 'arrow-up.svg' : 'arrow-down.svg';

            if (activeColumn) {
                $th.addClassName('ordered')
            }

            $th.writeAttribute('data-order-direction', orderDirection)
                .down('img')
                .writeAttribute('src', window.dir_images + 'axialis/user-interface/' + orderImage)
                .writeAttribute('title', orderDirection === 'asc' ? desc : asc);
        });
    }
});
