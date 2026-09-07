<head>
    [{if $title}]
        <title>i-doit - [{$title}]</title>
    [{else}]
        <title>i-doit - [{isys type="breadcrumb_navi" name="breadcrumb" plain=true}]</title>
    [{/if}]

    <!--
    This can not be used at the time, because all links (with href="#") will request the dashboard.
    <base href="[{isys_application::instance()->www_path}]">
    -->

    <meta name="author" content="synetics gmbh" />
    <meta name="description" content="i-doit" />
    <meta name="keywords" content="i-doit, CMDB, ITSM, ITIL, NMS, Netzwerk, Dokumentation, Documentation" />
    <meta http-equiv="content-type" content="text/html; charset=[{$html_encoding|default:"utf-8"}];" />
    <meta name="robots" content="noindex" />

    <link rel="icon" type="image/svg+xml" href="[{$dir_images}]favicon.svg" />

    <!-- This meta tag will force the internet explorer to disable the "compability" mode -->
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="[{$dir_images}]favicon.ico" />
    <![endif]-->

    <link rel="stylesheet" type="text/css" media="print" href="[{$dir_theme}]css/print.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="?load=css&theme=default&token=[{isys_tenantsettings::get('system.last-change',time())}]" />
    <link rel="stylesheet" type="text/css" media="screen" href="?load=mod-css&theme=default&token=[{isys_tenantsettings::get('system.last-change',time())}]" />

    <script type="text/javascript">
        var C__CMDB__GET__OBJECT      = "[{$object}]",
            C__CMDB__GET__TREEMODE    = "[{$treemode}]",
            C__CMDB__GET__VIEWMODE    = "[{$viewmode}]",
            C__CMDB__GET__OBJECTTYPE  = "[{$objtype}]",
            C__CMDB__GET__OBJECTGROUP = "[{$objgroup}]",
            C__GET__NAVMODE           = "[{$smarty.const.C__GET__NAVMODE}]",
            C__NAVMODE__JS_ACTION     = "[{$smarty.const.C__NAVMODE__JS_ACTION}]",
            C__AUTHORIZED             = "[{isys_application::instance()->container->get('session')->is_logged_in()}]",
            C__TENANT_ID              = "[{isys_application::instance()->container->get('session')->get_mandator_id()}]";

        window.dir_images = '[{$dir_images}]';
        window.www_dir = '[{isys_application::instance()->www_path}]';
        window.tree_size = '[{isys_application::instance()->container->get('settingsUser')->get('gui.tree.spacing', 'l')}]';
        window.allowedImageExtensions = JSON.parse('[{isys_application::ALLOWED_IMAGE_EXTENSIONS|json_encode}]');

        [{$errorTrackerCode}]
    </script>

	<script type="text/javascript" src="[{$dir_tools}]js/prototype/prototype.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/scriptaculous/src/scriptaculous.js?load=effects,dragdrop,controls"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/taborder/taborder.js"></script>
	<script type="text/javascript" src="[{$dir_tools}]js/ckeditor/ckeditor.js"></script>
    <!-- @see ID-9310 Refactor 'chosen' in order to re-use it in the admin-center -->
    <script type="text/javascript" src="[{$dir_tools}]js/chosen/chosen.js"></script>
    <script type="text/javascript" src="[{$dir_tools}]js/chosen/chosen_extension.js"></script>

    <script type="text/javascript" src="[{$dir_tools}]js/compressed/scripts.js?token=[{isys_tenantsettings::get('system.last-change',time())}]"></script>

    <!--suppress JSFileReferences -->
    <script type="text/javascript">
        /* <![CDATA[ */
        globalize(C__CMDB__GET__TREEMODE, '[{intval($smarty.get.$treemode)}]');
        globalize(C__CMDB__GET__OBJECT, '[{intval($smarty.get.$object)}]');
        globalize(C__CMDB__GET__OBJECTTYPE, '[{intval($smarty.get.$objtype)}]');

        [{include file="lang.js"}]

        Event.observe(window, 'load', function () {
            onload_process();
        });

        idoit.Require.config({
            lastChange: '[{isys_tenantsettings::get('system.last-change',time())}]',
            paths: {
                ace:                     "[{$dir_tools}]js/ace/ace.js",
                aceLanguageTools:        "[{$dir_tools}]js/ace/ext-language_tools.js",
                chassis:                 "[{$dir_tools}]js/chassis/chassis.js",
                coloris:                 "[{$dir_tools}]js/coloris/coloris.min.js",
                d3:                      "[{$dir_tools}]js/d3/d3-v5.16.0-min.js",
                d3v7:                    "[{$dir_tools}]js/d3/d3-v7.8.5-min.js",
                d3cola:                  "[{$dir_tools}]js/d3/cola-v3.3.8-min.js",
                d3CmdbExplorer:          "[{$dir_tools}]js/d3/visualization/cmdb-explorer.js",
                d3VisConnection:         "[{$dir_tools}]js/d3/visualization/connections.js",
                d3ChartPie:              "[{$dir_tools}]js/d3/charts/pie.js",
                d3ChartBar:              "[{$dir_tools}]js/d3/charts/bar.js",
                d3ChartStacked:          "[{$dir_tools}]js/d3/charts/stacked.js",
                svgToPng:                "[{$dir_tools}]js/svgToPng/saveSvgAsPng.js",
                rack:                    "[{$dir_tools}]js/rack/rack.js",
                rackAssignment:          "[{$dir_tools}]js/rack/rackAssignment.js",
                qcw:                     "[{$dir_tools}]js/qcw/quick_configuration_wizard.js",
                taborder:                "[{$dir_tools}]js/taborder/taborder.js",
                ckeditor:                "[{$dir_tools}]js/ckeditor/ckeditor.js",
                authConfiguration:       "[{$dir_tools}]js/auth/configuration.js",
                simpleAuthConfiguration: "[{$dir_tools}]js/auth/simple_configuration.js",
                fileUploader:            "[{$dir_tools}]js/ajax_upload/fileuploader.js",
                treeBase:                "[{$dir_tools}]js/tree/base.js",
                treeLocation:            "[{$dir_tools}]js/tree/location.js",
                treeLocationLinked:      "[{$dir_tools}]js/tree/locationLinked.js",
                reactBridge:             "[{$dir_tools}]js/react/bridge.min.js",
                smartyNatcompare:        "[{$dir_tools}]js/smarty/natcompare.js",
                smartyProgress:          "[{$dir_tools}]js/smarty/progress.js",
                smartyRaid:              "[{$dir_tools}]js/smarty/raid.js",
                smartyTime:              "[{$dir_tools}]js/smarty/time.js",
                smartySuggestion:        "[{$dir_tools}]js/smarty/suggestion.js",
                smartyVWA:               "[{$dir_tools}]js/smarty/vwa.js",
                smartyFilesUpload:       "[{$dir_tools}]js/smarty/filesUpload.js",
                validation:              "[{$dir_tools}]js/validation/validation.js",
                attributeVisibility:     "[{$dir_tools}]js/attributeVisibility/attributeVisibility.js",
                fileBrowser:             "[{$dir_tools}]js/browser/FileBrowser.js",
            }
        });

        // @see ID-10343 Set default Coloris configuration.
        idoit.Require.require('coloris', () => {
            Coloris({
                el:           '[data-colorpicker]',
                wrap:         false,
                theme:        'default',
                themeMode:    'auto',
                format:       'hex',
                formatToggle: false,
                alpha:        false,
                defaultColor: '#ffffff'
            });
        });

        // Create 'idoit.Router' instance to simply fetch symfony routes in the frontend.
        idoit.Router = new window.Router(JSON.parse('[{$routes|json_encode|escape:'javascript'}]'));
        /* ]]> */
    </script>

    [{foreach from=$jsFiles item="jsFile"}]
        <script type="text/javascript" src="[{$jsFile}]"></script>
    [{/foreach}]
</head>

<body id="body" class="[{$bodyClassName|default:"bg-white"}]">
