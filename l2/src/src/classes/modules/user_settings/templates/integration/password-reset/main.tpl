<!doctype html>
<html lang="en">
<head>
    <title>i-doit - Passwort reset</title>

    <meta name="author" content="synetics gmbh" />
    <meta name="description" content="i-doit" />
    <meta name="keywords" content="i-doit, CMDB, ITSM, ITIL, NMS, Netzwerk, Dokumentation, Documentation" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8;" />
    <meta name="robots" content="noindex" />

    <link rel="icon" type="image/png" href="/images/favicon.png">

    <!-- This meta tag will force the internet explorer to disable the "compability" mode -->
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="[{$www_path}]images/favicon.ico" />
    <![endif]-->

    <link rel="stylesheet" type="text/css" media="screen" href="[{$www_path}]?load=css" />
    <link rel="stylesheet" type="text/css" media="screen" href="[{$assets_dir}]css/password-reset.css" />
</head>

<body id="body">
<div id="wrapper">

    <div class="password-reset-container">
        [{block name="content"}]
        <div class="text-center mb-8">
            <img src="[{$www_path}]images/logo.png" alt="i-doit" class="logo" />

            <h1>Something went wrong.</h1>

            <p class="mt-6">Unfortunately there was an error while processing your action.</p>
            <p class="mt15">Please try again or contact your system administrator, if this keeps happening.</p>
        </div>
        <div class="display-flex justify-content-center">
            <a class="link display-flex align-items-center" href="[{$www_path}]">
                <img src="[{$assets_dir}]images/arrow-right.svg" class="mr10" alt="" />
                <span>Go to Login</span>
            </a>
        </div>
        [{/block}]
    </div>

    <div class="password-reset-footer">
        <a href="https://www.i-doit.com/en" target="_blank">i-doit.com - &copy; synetics gmbh</a>
        <a href="https://kb.i-doit.com" target="_blank">i-doit Knowledge Base</a>
        <a href="https://help.i-doit.com/hc/en-us" target="_blank">Support</a>
        <a href="https://www.i-doit.com/en/imprint" target="_blank">Imprint</a>
        <a href="https://www.i-doit.com/en/privacy-policy/" target="_blank">Data privacy</a>
    </div>
</div>

<script type="text/javascript">
    (function () {
        const inputs = document.body.getElementsByTagName('input');

        for (const input of inputs) {
            input.addEventListener('keydown', e => {
                e.srcElement.classList.remove('has-error');

                if (e.srcElement.nextElementSibling &&
                    e.srcElement.nextElementSibling.tagName === 'LABEL' &&
                    e.srcElement.nextElementSibling.classList.contains('error-label')
                ) {
                    e.srcElement.nextElementSibling.remove()
                }
            });
        }
    })()
</script>
</body>
</html>
