[{extends file="./main.tpl"}]
[{block name="content"}]
    <div class="text-center mb-8">
        <img src="[{$www_path}]images/logo.png" alt="i-doit" class="logo" />

        <h1>Please log out before resetting your password.</h1>

        <p class="mt-6">To reset your password, you must first log out of your account. Once you’ve logged out, you can proceed with the password reset process.</p>
        <p class="mt-6">If you need further assistance, feel free to contact our support team.</p>
    </div>
    <div class="display-flex justify-content-center">
        <a class="link display-flex align-items-center" href="[{$www_path}]">
            <img src="[{$assets_dir}]images/arrow-right.svg" class="mr10" alt="" />
            <span>Go to i-doit</span>
        </a>
    </div>
    [{/block}]
