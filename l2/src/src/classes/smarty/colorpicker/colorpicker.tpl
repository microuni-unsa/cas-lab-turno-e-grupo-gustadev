<div class="input-group [{$containerClass}] [{$size}]">
    <input
        type="text"
        id="[{$id}]"
        name="[{$name}]"
        placeholder="[{$placeholder|escape}]"
        value="[{$value|escape}]"
        class="input input-block"
        [{foreach $data as $key => $value}]
        data-[{$key}]="[{$value|escape}]"
        [{/foreach}]
        [{if $disabled}]disabled[{/if}]
        data-colorpicker />

    <div class="input-group-addon">
        <div id="[{$id}]_preview" class="colorpicker-pill" style="background-color: [{$value|escape}];"></div>
    </div>
</div>

<script>
    // Implement a timeout to prevent multiple loadings (resulting in multiple instances).
    setTimeout(() => {
        Coloris.setInstance('#[{$id}]', {
            parent:   '[{$parent}]',
            onChange: function (color, $input) {
                $('[{$id}]_preview').setStyle({backgroundColor: color});
            }
        });
    }, 150);

    // Necessary for changes from the outside.
    $('[{$id}]').on('change', () => $('[{$id}]_preview').setStyle({backgroundColor: $F('[{$id}]')}));
</script>
