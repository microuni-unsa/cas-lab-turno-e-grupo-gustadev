[{extends file="./main.tpl"}]
[{block name="content"}]
    <div class="text-center mb-8">
        <img src="[{$www_path}]images/logo.png" alt="i-doit" class="logo" />

        <div class="display-flex justify-content-center">
            <h1 class="display-flex align-items-center">Set your new password.</h1>
        </div>
    </div>
    <div>
        <h2>Security Hints</h2>
        <ul class="securityHints">
            <li>Your password must have at least 8, max. 64 characters</li>
            <li>Your password must have at least one number and a mix of upper- and lowercase letters</li>
            <li>Add special characters for an even stronger password</li>
        </ul>
    </div>

    <form action="?token=[{$smarty.get.token}]" method="post">
        <label class="display-block pl10 text-bold" for="password">New password</label>
        <div class="mt5 mb20">
            <input type="password" class="input input-size-block mb5 [{if $passwordInvalid}]has-error[{/if}]" name="password" id="password" placeholder="Enter new password"
                   value="[{$password|escape}]" />
            [{if $passwordError}]
            <label class="error-label" for="password">[{$passwordError}]</label>
            [{/if}]
        </div>

        <label class="display-block pl10 text-bold" for="passwordRepeat">Repeat password</label>
        <div class="mt5 mb20">
            <input type="password" class="input input-size-block mb5 [{if $passwordRepeatInvalid}]has-error[{/if}]" name="passwordRepeat" id="passwordRepeat"
                   placeholder="Repeat password" value="[{$passwordRepeat|escape}]" />
            [{if $passwordRepeatError}]
            <label class="error-label" for="passwordRepeat">[{$passwordRepeatError}]</label>
            [{/if}]
        </div>

        <div style="display: flex; align-items: center; margin-bottom: 40px;" class="ml5">
            <input type="checkbox" id="showPassword" name="showPassword" class="mr10" />
            <label for="showPassword">Show password</label>
        </div>

        <div class="display-flex align-items-center justify-content-between">
            <div></div>
            <button type="submit" class="btn">Confirm</button>
        </div>
    </form>
    <script type="text/javascript">
        (function () {
            document.getElementById('showPassword').addEventListener('change', (e) => {
                const targetType = e.target.checked ? 'text' : 'password';
                document.getElementById('password').setAttribute('type', targetType);
                document.getElementById('passwordRepeat').setAttribute('type', targetType);
            });
        })();
    </script>
    [{/block}]
