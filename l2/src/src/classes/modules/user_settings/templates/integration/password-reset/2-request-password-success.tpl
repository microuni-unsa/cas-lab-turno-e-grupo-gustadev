[{extends file="./main.tpl"}]
[{block name="content"}]
    <div class="text-center mb-8">
        <img src="[{$www_path}]images/logo.png" alt="i-doit" class="logo" />

        <div class="display-flex justify-content-center">
            <h1 class="display-flex align-items-center"><img src="[{$assets_dir}]images/check-green.svg" class="mr10" alt="" /> Check your e-mails.</h1>
        </div>

        <p class="mt-6">We sent a link to reset your password to the e-mail address provided for your username. Follow the instructions in the e-mail to reset your
            password.</p>
    </div>
    <div class="display-flex justify-content-center">
        <a class="link display-flex align-items-center" href="[{$www_path}]">
            <img src="[{$assets_dir}]images/arrow-right.svg" class="mr10" alt="" />
            <span>Go to Login</span>
        </a>
    </div>
[{/block}]
