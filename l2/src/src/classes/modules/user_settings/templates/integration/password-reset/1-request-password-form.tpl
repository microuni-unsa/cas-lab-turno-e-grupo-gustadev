[{extends file="./main.tpl"}]
[{block name="content"}]
    <div class="text-center mb-8">
        <img src="[{$www_path}]images/logo.png" alt="i-doit" class="logo" />
        [{if !$isExpired}]
        <h1>Reset your password.</h1>
        <p class="mt-6">We’ll send a link to reset your password to the e-mail address provided for your username.</p>
        [{else}]
        <h1>This link has expired.</h1>
        <p class="mt-6">Your password reset link has expired. If you are still in need of resetting your password, enter your e-mail address below and we’ll send you a new
            link.</p>
        [{/if}]
    </div>

    <form action="?" method="post">
        <label class="display-block pl10 text-bold" for="email">E-mail address</label>

        <div class="mt5 mb20">
            <input type="text" class="input input-size-block mb5 [{if $emailInvalid}]has-error[{/if}]" name="email" id="email" placeholder="Enter e-mail address" value="[{$email|escape}]" />
            [{if $emailInvalid}]
            <label class="error-label" for="email">Please provide a valid e-mail address.</label>
            [{/if}]
        </div>

        <div class="display-flex align-items-center justify-content-between">
            <a class="link" href="[{$www_path}]">Go to Login</a>
            <button type="submit" class="btn">Confirm</button>
        </div>
    </form>
[{/block}]
