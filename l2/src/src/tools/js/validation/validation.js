/**
 * i-doit javascript base class for validation.
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
window.Validation = Class.create({
    $label: null,
    $field: null,
    
    initialize: function ($field, options) {
        this.$field = $field;
        this.options = { /* No options yet */ };
        
        // In case of 'chosen' fields, we have to apply changes to the chosen container, not the (hidden) select.
        if (this.$field.previous('.chosen-container')) {
            this.$field = this.$field.previous('.chosen-container').down('.chosen-choices,.chosen-single');
        }
        
        Object.extend(this.options, options || {});
        
        this.find_elements();
    },
    
    find_elements: function () {
        var id     = this.$field,
            $label = $$('label[for="' + id + '"]');
        
        // Find the label.
        if ($label.length > 0) {
            this.$label = $label[0];
        }
    },
    
    fail: function (message) {
        new Tip(this.$field, new Element('p', { className: 'p5', style: 'font-size:12px;' }).update(message), {
            stem:   false,
            showOn: 'mouseenter',
            hideOn: 'mouseleave',
            style:  'darkgrey'
        });
        
        this.$field.addClassName('input-error');
    },
    
    success: function () {
        // Just in case, we enable the save button.
        isys_glob_enable_save();
        
        Tips.remove(this.$field.removeClassName('input-error'));
    }
});
