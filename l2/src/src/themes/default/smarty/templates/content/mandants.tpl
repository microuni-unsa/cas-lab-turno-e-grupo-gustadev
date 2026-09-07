[{if is_array($mandant_options) && count($mandant_options)}]
    <label class="display-block text-bold mb5" for="login_mandant_id">[{isys type="lang" ident="Tenant"}]</label>

    <select class="input input-block" name="login_mandant_id">
        [{html_options options=$mandant_options selected=$requiredTenantId}]
    </select>

    <input type="hidden" name="mode" value="hypergate" />

    <div class="display-flex" style="margin-top: 32px; margin-bottom: 42px">
        <button name="login_submit" type="button" id="login_submit" class="ml-auto btn">
            <img src="[{$dir_images}]axialis/basic/login.svg"><span>Login</span>
        </button>
    </div>

	<script type="text/javascript">
		(function () {
			'use strict';

			var $login_submit = $('login_submit');

			$login_submit.on('click', function () {
				$('login_username').enable();
				$('login_password').enable();
				setTimeout(function(){
                    $('isys_form').writeAttribute('action', '').submit();
                }, 100)
			});

			// Focus Next button.
			setTimeout(function () {
				$login_submit.focus();
			}, 250);
		})();
	</script>
[{else}]
	[{if !empty($login_error)}]
		<script type="text/javascript">
			 [{if isset($login_header)}]$('login_error_header').update('[{$login_header}]');[{/if}]

			 $('login_error_message').update('[{$login_error|nl2br}]');
			 $('login_error').show();

			 // Make input fields writable.
			 $('login_username').enable();
			 $('login_password').enable();
			 if ($('login_submit')) {
				 $('login_submit').show();
			 }
		</script>
	[{/if}]
[{/if}]

[{if $directlogin}]
<script type="text/javascript">
	$('login_username').enable();
	$('login_password').enable();
	$('isys_form').submit();
</script>
[{/if}]
