[{extends file="./main.tpl"}]
[{block name="content"}]
    <div class="text-center mb-8">
        <img src="[{$www_path}]images/logo.png" alt="i-doit" class="logo" />

        <div class="display-flex justify-content-center">
            <h1 class="display-flex align-items-center"><img src="[{$assets_dir}]images/check-green.svg" class="mr10" alt="" /> Password changed.</h1>
        </div>

        <p class="mt-6">Your password was changed successfully. Login to your your instance and enjoy the experience.</p>
    </div>
    <div class="display-flex justify-content-center">
        <a class="link display-flex align-items-center" href="[{$www_path}]">
            <img src="[{$assets_dir}]images/arrow-right.svg" class="mr10" alt="" />
            <span>Go to Login</span>
        </a>
    </div>
    [{/block}]
