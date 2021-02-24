<?php

include_once 'config.php';

$action = $_GET['action'];


switch ($action) {

    case 1:

        $query = "SELECT document.id,document.doc_name, moodle_servers.name,document.document_url,document.local_course_id
FROM document
left JOIN course_server ON document.id = course_server.document_id
LEFT JOIN moodle_servers ON moodle_servers.id = course_server.server_id ORDER BY id asc";
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
        header('Content-type:text/xml;charset=ISO-8859-1;');
        echo '<?xml version="1.0"?>';

        echo '<rows>';
        $server = "N/A";
        $url = "Extracted from zip file";

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<row id="' . $row['id'] . '">';
            echo "<cell><![CDATA[" . $row["id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["doc_name"] . "]]></cell>";

//            if ($row["name"])
//                echo "<cell><![CDATA[" . $row["name"] . "]]></cell>";
//            else
//                echo "<cell><![CDATA[" . $server . "]]></cell>";
//
//            if ($row["document_url"])
//                echo "<cell><![CDATA[" . $row["document_url"] . "]]></cell>";
//            else
//                echo "<cell><![CDATA[" . $url . "]]></cell>";

            if ($row["local_course_id"])
                echo "<cell><![CDATA[" . $row["local_course_id"] . "]]></cell>";

            echo '</row>';
        }
        echo '</rows>';

        break;

    case 2:
        $id = $_GET['id'];
        $query = "SELECT content FROM document WHERE id=" . $id;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode($row["content"]);
        }

        break;

    case 3:
        $id = $_GET['id'];
        $query = "SELECT content,uppercss,lowercss FROM toc WHERE id=" . $id;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($row = mysqli_fetch_assoc($result)) {
            $content = str_replace("\/", "/", $row["uppercss"]) . str_replace("\/", "/", $row["content"]) . str_replace("\/", "/", $row["lowercss"]);

            echo json_encode($content);
        }

        break;

    case 4:
        header('Content-type:text/xml;charset=ISO-8859-1;');
        $data = '<?xml version="1.0"?>
        <items>
            <item type="settings" position="label-left" labelWidth="150" inputWidth="480" noteWidth="480"/>
            <item type="fieldset" name="data" inputWidth="370" label="Google Document">

                    <item type="input" name ="url" label="Input Document URL" info="false">
                        <note>Please Enter your document URL here</note>
                    </item>


                <item type="button" id="extract" value="Extract"/>
            </item>
        </items>';

        echo $data;

        break;

    case 5:
        $id = $_GET['id'];
        $name = $_GET['doc_name'];

        $query_quiz = "Delete question, question_Page, choices
                    FROM document document
                     JOIN toc ON document.id = toc.doc_id
                     JOIN project_course_question_to_page
                     question_Page ON
                     question_Page.page_id = toc.id
                    JOIN project_course_question question ON question.id = question_Page.question_id
                     left JOIN project_course_choices choices ON choices.question_id=question.id
                        WHERE document.id =" . $id;
        $result_quiz = mysqli_query($dbc, $query_quiz) or die(mysqli_error($dbc));

        if ($result_quiz) {

            $query = "DELETE  document, toc FROM document  INNER JOIN toc ON document.id=toc.doc_id  WHERE document.id =" . $id;
            $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

            if ($result) {
                $query = "DELETE FROM course_server WHERE document_id  =" . $id;
                $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

                if ($result) {

                    $query = "DELETE FROM document WHERE id  =" . $id;
                    $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
                    //delete course files
                    $path = $_SERVER["DOCUMENT_ROOT"] . "/CourseFiles/documentFiles/" . $name;
                    if (file_exists($path))
                        xrmdir($path);
                         $response = [
                        'response' => true,
                        'text' => 'Document Deleted!!',
                    ];
                    echo json_encode($response);
                }
            }

        }
        break;

    case 6:

        include_once 'header.php';
        try {
            require_once $_SERVER["DOCUMENT_ROOT"] . '/GoogleDocsAPI/vendor/autoload.php';
            header("Access-Control-Allow-Origin: *");
            $doc_url = $_POST['url'];
            $insertRecords = [];
            $content_raw = [];

            $levels = 0;
            $parent_id = 0;

            $toc_raw = [];
            $content_image = [];
            $service = "";
            $documentId = "";
            $doc = "";
            $inlineObjects = "";
            $subArray = "";
            $headline = "";
            $doc_code = "";
            $topics = [];
            $chapter_id = '';
            $chapter_name = '';
            $content = "";
            $sortId = 0;
            $tables = "";
            $docName = "";
            $html = getHeader();
            getBody($doc_url);

            $html .= '</body></html>';

            if ($docName != "" && $html != "") {
                $query_insert_document = 'INSERT INTO document (doc_name, content) VALUES ("' . $docName . '","' . mysqli_real_escape_string($dbc, $html) . '")
ON DUPLICATE KEY UPDATE content=values(content)';

                $result = mysqli_query($dbc, $query_insert_document) or die(mysqli_error($dbc));

                if ($result) {
                    $documentId = mysqli_insert_id($dbc);

                    $insertString = implode(',', $insertRecords);

                    if (count($insertRecords) > 0) {
                        $query_insert_toc = "INSERT INTO google_docs (doc_id, doc_name,sort_id,last_revised, parent_id, level, toc_chapter, toc_name,content_html) VALUES " . str_replace("doc_id", $documentId, $insertString)
                            . "ON DUPLICATE KEY UPDATE last_revised=values(last_revised),parent_id=values(parent_id),level=values(level),toc_chapter=values(toc_chapter),toc_name=values(toc_name),content_html=values(content_html)";

                        $result = mysqli_query($dbc, $query_insert_toc) or die(mysqli_error($dbc));

                        if ($result) {
                            $data['data'] = array('response' => true, 'text' => 'Successfully Added');
                        } else {
                            $data['data'] = array('response' => false, 'text' => 'An Error Occured While Saving');
                        }
                    }
                }
            } else {
                $data['data'] = array('response' => false, 'text' => 'An Error Occured While Saving');
            }

            echo json_encode($data);

        } catch (Exception $e) {

            $data['data'] = array('error' => true, 'text' => 'An Error Occured while extracting your document, Please clear your site data and try Again!');

            echo json_encode($data);
        }
        break;

    case 7:

        $version = $_GET['version'];
        $file_name = $_GET['file_name'];
        $docid = $_GET['docid'];
        $dateTime = date("d.m.Y") . " " . date("h:i:sa");

        $query = 'INSERT INTO course_versions (version_name, file_names,Date_Time,docid) VALUES ("' . $version . '","' . $file_name . '","' . $dateTime . '","' . $docid . '")';

        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($result) {

            $data = [
                'response' => true,
                'text' => 'Backed Up Successfully',
            ];
        } else {
            $data = [
                'response' => false,
                'text' => 'An error Occured, try again!',
            ];

        }

        echo json_encode($data);
        break;

    case 8:
        $version = $_GET['docid'];

        $query = "SELECT * FROM course_versions WHERE docid=" . $version;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
        header('Content-type:text/xml;charset=ISO-8859-1;');
        echo '<?xml version="1.0"?>';

        echo '<rows>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<row id="' . $row['file_names'] . '">';
            echo "<cell><![CDATA[" . $row["version_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Date_Time"] . "]]></cell>";
            // echo "<cell><![CDATA[" . $row["file_names"] . "]]></cell>";

            echo '</row>';
        }
        echo '</rows>';

        break;

    case 9:

        $query = "SELECT * FROM status";

        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        $contents = "";
        while ($row = mysqli_fetch_assoc($result)) {

            $contents = $contents . $row["Content"];


        }

        echo json_encode($contents);


        break;

    case 10:
        $id = $_GET['id'];
        $query = "SELECT content,uppercss,lowercss FROM archived_toc WHERE id=" . $id;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($row = mysqli_fetch_assoc($result)) {
            $content = $content = str_replace("\/", "/", $row["uppercss"]) . str_replace("\/", "/", $row["content"]) . str_replace("\/", "/", $row["lowercss"]);


            echo json_encode($content);
        }

        break;
    case 11:
        // $version = $_GET['docid'];

        $query = "SELECT * FROM moodle_servers";
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
        header('Content-type:text/xml;charset=ISO-8859-1;');
        echo '<?xml version="1.0"?>';

        echo '<rows>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<row id="' . $row['id'] . '">';
            echo "<cell><![CDATA[" . $row["name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["location"] . "]]></cell>";
            // echo "<cell><![CDATA[" . $row["file_names"] . "]]></cell>";

            echo '</row>';
        }
        echo '</rows>';

        break;
    case 12:
        $id = $_GET['id'];
        $doc_id = $_GET['doc_id'];
        $data = "";
        $query = "UPDATE course_server SET server_id='" . $id . "' WHERE document_id= " . $doc_id;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($result) {
            $data = [
                'response' => true,
                'text' => 'Server Selected, Proceeding with export...!',
            ];
        } else {
            $data = [
                'response' => false,
                'text' => 'An error Occured, try again!',
            ];
        }
        echo json_encode($data);

        break;

    case 13:
        $server = $_GET['server'];
        $data = "";
        $query = "insert into course_server(server_id) values('" . $server . "')";
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($result) {
            $data = [
                'response' => true,
                'text' => 'Server Selected, Proceeding with export...!',
            ];
        } else {
            $data = [
                'response' => false,
                'text' => 'An error Occured, try again!',
            ];
        }
        echo json_encode($data);

        break;

    case 14:
        $id = $_GET['id'];

        $query = "SELECT document.doc_name, moodle_servers.name,document.document_url,
                    document.local_course_id,document.details,document.date_time,document.emp_id
                           FROM document
                            LEFT JOIN course_server ON document.id = course_server.document_id
                            LEFT JOIN moodle_servers ON moodle_servers.id = course_server.server_id
                            WHERE document.id=" . $id;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($row = mysqli_fetch_assoc($result)) {
            $data = [
                'response' => true,
                'title' => $row['doc_name'],
                'details' => $row['details'],
                'url' => $row['document_url'],
                'local_id' => $row['local_course_id'],
                'emp_id' => $row['emp_id'],
                'date_time' => $row['date_time'],
                'server' => $row['name'],

            ];
        }
        echo json_encode($data);

        break;


}
function xrmdir($dir)
{
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            xrmdir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}
