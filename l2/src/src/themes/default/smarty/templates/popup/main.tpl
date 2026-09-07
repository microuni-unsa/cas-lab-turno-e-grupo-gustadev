[{isys_group name="tom"}]
<!doctype html>
<html lang="[{$activeLanguage.short|lower|default:"en"}]">
<head>
	<title>i-doit - [{$title}]</title>

	<meta name="author" content="synetics gmbh" />
	<meta name="description" content="i-doit" />
	<meta name="keywords" content="i-doit, CMDB, NMS, Netzwerk, Dokumentation" />
	<meta http-equiv="content-type" content="text/html; charset=[{$html_encoding|default:"utf-8"}]" />
	<meta http-equiv="content-language" content="de" />
	<meta name="robots" content="noindex" />

	<link rel="icon" type="image/svg+xml" href="[{$dir_images}]favicon.svg" />
	<link rel="stylesheet" type="text/css" media="screen" href="?load=css" />

	<script type="text/javascript" language="JavaScript" src="[{$dir_tools}]js/prototype/prototype.js"></script>
	<script type="text/javascript" language="JavaScript" src="[{$dir_tools}]js/scriptaculous/src/scriptaculous.js"></script>
	<script type="text/javascript" language="JavaScript" src="[{$dir_tools}]js/taborder/taborder.js"></script>
	<script type="text/javascript" language="JavaScript" src="[{$dir_tools}]js/compressed/scripts.js"></script>
</head>

[{include file="$file_body"}]
</html>
[{/isys_group}]
