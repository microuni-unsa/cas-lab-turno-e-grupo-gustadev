/**
 * i-doit extension for the "chosen" plugin
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.ChosenExtension = Class.create({
    $field:      null,
    $chosen_div: null,
    
    initialize: function ($field, options) {
        var tmp;
        
        this.$field = $field;
        // @see ID-9054 Due to the redesign changes, the chosen container is now located BEFORE the field.
        this.$chosen_div = $($field.id + '_chosen') || this.$field.previous('.chosen-container') || this.$field.next('.chosen-container');
        this.$chosen_div_buttons = new Element('div', {className: 'border-top p5 bg-neutral-200'});
        this.options = {
            disable_button_all:     false,
            disable_button_invert:  false,
            disable_button_none:    false,
            chosenMaxHeight:        null,
            additional_value_field: false
        };
        
        this.$chosen_div.down('.chosen-drop').insert(this.$chosen_div_buttons);
        
        Object.extend(this.options, options || {});
        
        if (!this.options.disable_button_all) {
            this.add_btn_all();
        }
        
        if (!this.options.disable_button_invert) {
            this.add_btn_invert();
        }
        
        if (!this.options.disable_button_none) {
            this.add_btn_none();
        }
        
        tmp = $(this.options.additional_value_field);
        this.options.additional_value_field = null;
        
        if (Object.isElement(tmp)) {
            this.options.$additional_value_field = tmp;
        } else {
            this.options.$additional_value_field = false;
        }
        
        if (this.options.chosenMaxHeight !== null) {
            this.$chosen_div.down('.chosen-choices')
                .setStyle({maxHeight: this.options.chosenMaxHeight, overflow: 'auto'});
        }
        
        if (this.options.disable_button_all && this.options.disable_button_invert && this.options.disable_button_none) {
            this.$chosen_div_buttons.remove();
        }
    },
    
    add_btn_all: function () {
		var $btn_all = new Element("button", {type: "button", className: "btn mr5"})
			.update(new Element("img", {src: window.dir_images + "axialis/basic/symbol-ok.svg", alt: ""}))
			.insert(new Element("span").update(this.options['chosen-btn-all']));
        
        $btn_all.on('click', function () {
            this.$field.setValue(this.$field.select('option').invoke('readAttribute', 'value')).fire('chosen:updated');
            this.$field.simulate('change');
            
            if (this.options.$additional_value_field) {
                this.options.$additional_value_field.setValue(this.$field.getValue().join())
            }
        }.bind(this));
        this.$chosen_div_buttons.insert($btn_all);
        
    },
    
    add_btn_invert: function () {
		var $btn_invert = new Element("button", {type: "button", className: "btn mr5"})
			.update(new Element("img", {src: window.dir_images + "axialis/basic/sort.svg", alt: ""}))
			.insert(new Element("span").update(this.options['chosen-btn-inverted']));
        
        $btn_invert.on('click', function () {
            var selection = this.$field.getValue();
            this.$field.setValue(this.$field.select('option').filter(function ($el) {
                return !selection.in_array($el.readAttribute('value'))
            }).invoke('readAttribute', 'value')).fire('chosen:updated');
            this.$field.simulate('change');
            
            if (this.options.$additional_value_field) {
                this.options.$additional_value_field.setValue(this.$field.getValue().join())
            }
        }.bind(this));
        
        this.$chosen_div_buttons.insert($btn_invert);
    },
    
    add_btn_none: function () {
		var $btn_none = new Element("button", {type: "button", className: "btn"})
			.update(new Element("img", {src: window.dir_images + "axialis/basic/symbol-cancel.svg", alt: ""}))
			.insert(new Element("span").update(this.options['chosen-btn-none']));
        
        $btn_none.on('click', function () {
            this.$field.setValue([]).fire('chosen:updated');
            this.$field.simulate('change');
            
            if (this.options.$additional_value_field) {
                this.options.$additional_value_field.setValue('')
            }
        }.bind(this));
        
        this.$chosen_div_buttons.insert($btn_none);
    }
});
