<?php

if (!file_exists('project-api/.env')) {
    header("Location: http://{$_SERVER['HTTP_HOST']}/" . basename(dirname(__FILE__)) . "/install.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>NTS Programs</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no"/>

	<!-- DHTMLX Library -->
	<link rel="stylesheet" type="text/css" href="./codebase/fonts/font_awesome/css/font-awesome.min.css">
	<link href="./codebase/skyblue/dhtmlx.css" rel="stylesheet">
	<link href="./codebase/web/dhtmlx.css" rel="stylesheet">
	<link href="./codebase/terrace/dhtmlx.css" rel="stylesheet">

	<link rel="stylesheet" type="text/css" href="./codebase/scheduler/dhtmlxscheduler_material.css">
	<script src="//cdn.dhtmlx.com/site/dhtmlx.js"></script>
	<script src="./codebase/scheduler/dhtmlxscheduler_2.js"></script>
	<script src="./codebase/scheduler/dhtmlxscheduler_units.js"></script>
	<script src="./codebase/tinymce/4/tinymce.min.js" referrerpolicy="origin"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.4.11/ace.js"></script>


	<!-- App -->
	<link href="codebase/app.css" rel="stylesheet"></link>
	<script type="text/javascript" src="codebase/app.js"></script>
</head>
<body>
	<script type="text/javascript">
		 new MyApp({}).render();
	</script>
</body>
</html>