window.AttributeVisibility = Class.create({
    config: {},

    initialize: function(config, options) {
        this.config = config;

        this.options = {
            parentElementType: 'tr',
            searchPattern: 'input[name="%s"]'
        };

        Object.extend(this.options, options || {});
    },

    hideAttributes: function() {
        // @see ID-9257 In case of description fields 'C__CMDB__CAT__COMMENTARY_01' is ambiguous, we use a different search pattern before getting fuzzy.
        // @see ID-9026 Only use a 'fuzzy' search that matches part of the name, if no exact match was found.
        const searchPattern = [
            this.options.searchPattern,
            'input[name*="SM2__%s["]',
            'input[name*="%s"]'
        ];

        this.config.forEach((item) => {
            let itemElement;

            for (let i in searchPattern) {
                if (!searchPattern.hasOwnProperty(i)) {
                    continue;
                }

                itemElement = $('scroller').down(searchPattern[i].replace('%s', item));

                if (itemElement) {
                    if (itemElement.up(this.options.parentElementType)) {
                        itemElement.up(this.options.parentElementType).hide();
                    } else {
                        itemElement.up().hide();
                    }

                    return;
                }
            }
        });
    },
});
