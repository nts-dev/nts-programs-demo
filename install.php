<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

//Database

$db = array( 	'table_list' => array (
  'archive_documents' => 6,
  'archived_toc' => 26,
  'chapter_media' => 5,
  'chapters' => 9,
  'choices' => 12,
  'course_choices' => 10,
  'course_question' => 7,
  'course_question_to_page' => 6,
  'course_server' => 4,
  'course_versions' => 5,
  'day_event' => 5,
  'days' => 2,
  'document' => 12,
  'document_media' => 5,
  'document_project' => 6,
  'document_questions' => 7,
  'documents' => 8,
  'event_user' => 5,
  'events' => 17,
  'failed_jobs' => 6,
  'files' => 11,
  'google_docs' => 10,
  'jobs' => 7,
  'media' => 15,
  'migrations' => 3,
  'model_has_permissions' => 3,
  'model_has_roles' => 3,
  'moodle_content' => 5,
  'moodle_documents' => 9,
  'moodle_servers' => 6,
  'moodle_toc' => 7,
  'oauth_access_tokens' => 9,
  'oauth_auth_codes' => 6,
  'oauth_clients' => 11,
  'oauth_personal_access_clients' => 4,
  'oauth_refresh_tokens' => 4,
  'page_question' => 8,
  'password_resets' => 3,
  'permissions' => 5,
  'project_course_choices' => 10,
  'project_course_question' => 6,
  'project_course_question_to_page' => 6,
  'project_type' => 5,
  'project_user' => 5,
  'projects' => 12,
  'question_responces' => 11,
  'questions' => 8,
  'role_has_permissions' => 2,
  'roles' => 5,
  'server' => 8,
  'servers' => 9,
  'status' => 3,
  'toc' => 25,
  'types' => 4,
  'users' => 8,
), 
'structure' =>'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', 
'contents' =>'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', 
'database' =>'projects_api', 
); 



//Settings
$settings=array();
$settings['execute'] = '';

?>
<style>
	html			{ background: #336699; }
	body			{ padding: 10px; background: #fff; font: 12px "Lucida Grande"; color: #333; }
	#title			{ font-size: 18px; }
	#layout			{ width: 500px; border: 1px solid #ccc; border-bottom: 0px; background: #eee; margin-top: 10px; }
	#layout .title	{ background: #ff9900; color: #fff; font-size: 16px; }
	#layout div		{ padding: 10px;  border: 1px solid #fff; border-bottom: 1px solid #ccc; min-height: 20px; }
	#layout span	{ width: 200px; display: block; float: left; }
	
	#doc			{ float: right; margin-right: 30px; width: 300px; border: 1px solid #ccc; background: #eee; padding: 10px; color: #999; }
	
	#list			{ background: #fff; border: 1px solid #ccc; }
	#list th		{ background: #ff9900; color: #fff; font-size: 13px; }
	#list td		{ background: #eee; border-top: 1px solid #fff; border-bottom: 1px solid #ccc; border-right: 1px solid #ccc;  border-left: 1px solid #fff; padding: 5px; font-size: 11px; }
	#copyright		{ margin-top: 20px; font-size: 11px; }
	
	fieldset		{ margin-top: 20px; border: 0px; padding: 0px; border:1px solid #999; background:#ddd; }
	input[type='text'], 
	textarea	{ border: 1px solid #aaa; margin:1px; padding: 5px; font-family: Helvetica; font-size: 14px; width: 250px; color: #333; }
	input[type='text']:focus,
	textarea:focus	{ border: 1px solid #6699cc; }
	
	button			{ display:block; margin:0 7px 0 0; background-color:#f5f5f5; border:1px solid #d3d3d3; border-top:1px solid #e9e9e9; border-left:1px solid #e9e9e9; font-family:"Lucida Grande", Tahoma, Arial, Verdana, sans-serif; font-size:100%; line-height:130%; text-decoration:none; font-weight:bold; color:#565656; cursor:pointer; padding:5px 10px 6px 7px; }
	button:hover	{ border: 1px solid #ccc; color: #ff6600; background: white; }
	
	.error			{ border: 1px solid #990000; background: #ffeeee; padding: 5px; margin: 5px; }
</style>
<?php
set_time_limit(300); // 5 minutes
ob_start();

$GLOBALS['HTTP_VARS'] = $_GET + $_POST;

function get($key)
{
    if (isset($GLOBALS['HTTP_VARS'][$key]))
        return $GLOBALS['HTTP_VARS'][$key];
}

function byte($size)
{
    $unim = array("B", "KB", "MB", "GB", "TB", "PB");
    $i = 0;
    while ($size >= 1024) {
        $i++;
        $size = $size / 1024;
    }
    return number_format($size, ($i ? 2 : 0), ",", ".") . " " . $unim[$i];
}

function myFlush()
{
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
}

if (get('dump') && isset($db)) {

    $structure = base64_decode($db['structure']);
    $structure = gzuncompress($structure);

    $sql = base64_decode($db['contents']);
    $sql = gzuncompress($sql);
    echo "Dump database <b>{$db['database']}</b><br/></br><textarea style=\"width:100%;padding:10px;\" rows=\"30\">$structure\n$sql</textarea>";
    exit;
}

$dir = get('dir');
$execute = isset($settings['execute']) ? $dir . "/" . $settings['execute'] : $dir;
$overwrite = get('overwrite');
$install = get('install');

//se install
if ($install) {

//    if ($dir != "" && substr($dir, strlen($dir) - 1, 1) != "/")
//        $dir .= "/";
//
//    if ($dir != '' && !file_exists($dir))
//        mkdir($dir);


    if (get('db')) {

        $db_hostname = get('db_hostname');
        $db_username = get('db_username');
        $db_password = get('db_password');
        $db_database = get('db_database');

        $dbc = mysqli_connect($db_hostname, $db_username, $db_password);

        // Check connection
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        }

        // create database if not exist
        mysqli_query($dbc, "CREATE DATABASE IF NOT EXISTS {$db_database}") or die(mysqli_error($dbc));

        // Change db to provided db
        mysqli_select_db($dbc, $db_database) or die(mysqli_error($dbc));

        $db_installed = false;
        $error = false;

        $sql = base64_decode($db['structure']);
        $sql = gzuncompress($sql);
        $query = explode(";\r", $sql);

        echo "<br/><br/><h3>DATABASE: CREATE STRUCTURE</h3>";

        for ($i = 0; $i < count($query); $i++) {
            list($table, $rows) = each($db['table_list']);
            echo "<div>$table ($rows rows)";
            if ($query[$i]) {
                if (!mysqli_query($dbc, $query[$i])) {
                    $error = true;
                    echo ' - <font color="red">ERROR</font>' . mysqli_error($dbc);
                } else
                    echo " - OK";
            }
            echo "</div>";
            myFlush();
        }
        if ($error)
            echo "<div>Some errors encountered</div>";
        $error = false;

        $sql = base64_decode($db['contents']);
        $sql = gzuncompress($sql);
        $query = explode(";\n", $sql);

        echo "<br/><br/><hr><h3>DATABASE: INSERT CONTENT</h3>";
        for ($i = 0; $i < count($query); $i++) {
            if ($query[$i]) {
                if (!mysqli_query($dbc, $query[$i])) {
                    $error = true;
                    echo ' - <font color="red">ERROR</font> ' . mysqli_error($dbc) . " <div class=\"error\">" . $query[$i] . "</div>";
                }
            }
            echo "</div>";
            myFlush();
        }
        if ($error)
            echo "<div>Some errors encountered</div>";

        mysqli_close($dbc);
        $db_installed = true;

        if ($db_installed) {

            echo "Database Installed";
            //------------------------------------------
            // Open config file
            //------------------------------------------
            $fp = fopen("project-api/.env", "w");
            $settings_file = "APP_NAME=Laravel" . "\n";
            $settings_file .= "APP_KEY=base64:PkJ2/CMJ2XO23CInA6R2If6+qUQ10AY4YrFMZHKtTv4=" . "\n";
            $settings_file .= "APP_URL=$dir";
            $settings_file .= "\n\n" . "LOG_CHANNEL=stack";
            $settings_file .= "\n\n" . "DB_CONNECTION=mysql";
            $settings_file .= "\n" . "DB_HOST=$db_hostname";
            $settings_file .= "\n" . "DB_PORT=3306";
            $settings_file .= "\n" . "DB_USERNAME=$db_username";
            $settings_file .= "\n" . "DB_PASSWORD=$db_password";
            $settings_file .= "\n" . "DB_DATABASE=$db_database";
            fwrite($fp, $settings_file, strlen($settings_file));
            fclose($fp);

            $fp_config = fopen("config.php", "w");
            $config_file = "<?php" . "\n";
            $config_file .= "\n" . "//Settings" . "\n";
            $config_file .= "unset(\$NTS_CFG);";
            $config_file .= "\n" . "global \$NTS_CFG;";
            $config_file .= "\n" . "\$NTS_CFG = new stdClass();";
            $config_file .= "\n" . "\$NTS_CFG->dbhost = '$db_hostname';";
            $config_file .= "\n" . "\$NTS_CFG->dbuser = '$db_username';";
            $config_file .= "\n" . "\$NTS_CFG->dbpass = '$db_password';";
            $config_file .= "\n" . "\$NTS_CFG->dbname = '$db_database';";
            $config_file .= "\n" . "\$NTS_CFG->wwwroot = '$dir';" . "\n";
            fwrite($fp_config, $config_file, strlen($config_file));
            fclose($fp_config);
            myFlush();
        }
    }

    echo " <br><hr><h1>Installation Complete!";

    if ($execute)
        echo "<a href=\"{$execute}\">Click here to go {$execute}</a>.";
} else {
    ?>
    <body>
    <!-- created with PHP Installer -->
    <div id="title">NTS Programs Demo</div>

    <div id="layout">
        <form action="" method="POST">
            <div class="title">Installation</div>
            <div>
                <span>Install path</span>
                <input type="text" name="dir" value="http://localhost/nts-programs-demo">
            </div>
            <input type="hidden" name="db" value="1"> 
            <div>
                Database settings
                <fieldset id="db_fieldset">
                    <div><span>Database Hostname</span>
                        <input type="text" name="db_hostname" value="localhost">
                    </div>
                    <div><span>Database Username</span>
                        <input type="text" name="db_username" value="root">
                    </div>
                    <div><span>Database Password</span>
                        <input type="text" name="db_password" value="">
                    </div>
                    <div><span>Database Name</span>
                        <input type="text" name="db_database" value="<?php echo $db['database']; ?>">
                    </div>
                    <div>
                        <a href="?dump=1" target="_blank">Click here to download the database for manual installing.</a>
                    </div>
                </fieldset>
            </div>
            <div>
                <input type="checkbox" name="install" id="install"> <label for="install">Confirm installation.</label>
            </div>
            <div><button>INSTALL</button></div>
        </form>
    </div>
    <br/>
    Database in the package
    <table cellspacing="0" cellpadding="5" id="list">
        <thead>
        <th>Table</th>
        <th>Rows</th>
    </thead>
    <?php
    foreach( $db['table_list'] as $table => $n )
    echo "<tr><td>$table</td><td>{$n}</td></tr>";
    ?>
</table>
</body>
    <?php
}
?>