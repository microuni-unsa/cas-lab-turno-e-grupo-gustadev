/**
 * i-doit Suggestion.
 *
 * This is a new implementation of idoit.Suggest that should be easier to use.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.Suggestion = Class.create(Ajax.Autocompleter, {
    initialize: function ($super, element, update, url, options) {
        // Overwrite the passed options with some hardcoded ones for this class:
        options = Object.extend(options || {}, {
            afterUpdateElement: this.afterUpdateElement.bind(this),
            onShow:             this.onShow.bind(this),
            onHide:             this.onHide.bind(this)
        });

        $super(element, update, url, options);
    },

    getUpdatedChoices: function ($super) {
        this.element.fire('suggestion:beforeGetUpdatedChoices', {
            'suggestion': this
        });

        $super();
    },

    onComplete: function ($super, xhr) {
        const $list = new Element('ul');

        if (!is_json_response(xhr, true)) {
            this.hide();
            return;
        }

        const json = xhr.responseJSON;

        if (!json.success) {
            this.hide();
            idoit.Notify.error(json.message, {sticky: true});
            return;
        }

        if (!Array.isArray(json.data) && typeof json.data !== 'object') {
            this.hide();
            idoit.Notify.error('JSON response needs to be of type array or object', {sticky: true});
            return;
        }

        for (let key in json.data) {
            if (!json.data.hasOwnProperty(key)) {
                continue;
            }

            $list.insert(this.renderItem(key, json.data[key]));
        }

        $super({'responseText': $list.outerHTML});
    },

    /**
     * Extend the class and overwrite this method in order to render items in a different way.
     *
     * @param key
     * @param value
     * @returns {*}
     */
    renderItem: function (key, value) {
        return new Element('li', {'data-key': key}).update(value);
    },

    afterUpdateElement: function ($suggestionInput, $selectedChoice) {
        this.element.fire('suggestion:afterUpdateElement', {
            'suggestionInput': $suggestionInput,
            'selectedChoice':  $selectedChoice,
            'suggestion':      this
        });
    },

    onShow: function ($suggestionInput, $choicesContainer) {
        const $parent = $choicesContainer.getOffsetParent();
        let topPosition = $suggestionInput.offsetHeight;

        if ($parent) {
            topPosition += $parent.scrollTop;
        }

        Position.clone($suggestionInput, $choicesContainer, {
            setHeight: false,
            setTop: false,
            offsetTop: topPosition
        });

        // The callback is the same as the original, aside from this next line.
        $choicesContainer.style.width = $suggestionInput.getWidth() + 'px';
        $choicesContainer.style.maxHeight = this.options.maxHeight || '320px';
        $choicesContainer.style.display = null;
        $choicesContainer.removeClassName('hide');

        this.element.fire('suggestion:afterShow', {
            'suggestionInput':  $suggestionInput,
            'choicesContainer': $choicesContainer,
            'suggestion':       this
        });
    },

    onHide: function ($suggestionInput, $choicesContainer) {
        $choicesContainer.addClassName('hide');

        this.element.fire('suggestion:afterHide', {
            'suggestionInput':  $suggestionInput,
            'choicesContainer': $choicesContainer,
            'suggestion':       this
        });
    }
});
