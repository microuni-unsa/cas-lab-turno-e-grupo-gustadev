<div class="suggestion-container [{$size}]">
    <input type="text" id="[{$name}]" placeholder="[{$placeholder|escape}]" value="[{$value|escape}]" class="input input-block [{$additionalCssClass}]" [{$additionalAttributes}] />
    <div id="[{$name}]-choices" class="suggestion-choices hide">CHOICES</div>
    <img id="[{$name}]-indicator" class="suggestion-indicator animation-rotate" src="[{$dir_images}]axialis/user-interface/loading.svg" style="display:none" alt="" />
</div>

<script>
    (function () {
        const $field = $('[{$name}]');
        const $choicesContainer = $('[{$name}]-choices');
        const parameters = JSON.parse('[{$urlParameters|json_encode|escape:"javascript"}]');

        idoit.Require.require('smartySuggestion', function () {
            new window.Suggestion($field, $choicesContainer, '[{$url}]', {
                paramName:  'search',
                parameters: Object.keys(parameters).length ? Object.toQueryString(parameters) : '',
                minChars:   3,
                indicator:  '[{$name}]-indicator'
            });
        });

        $field.on('suggestion:afterUpdateElement', (ev) => {
            try {
                // @see ID-10914 Apply callback code.
                [{$callbackOnSelect}]
            } catch (e) {
                idoit.Notify.error(e);
            }
        });
    })();
</script>
