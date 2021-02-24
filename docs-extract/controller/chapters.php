<?php

include_once 'config.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$action = $_GET['action'];


$isDelete = $_GET['isDelete'];

switch ($action) {

    case 1:
        $stat = $_GET['stat'];
        $ids = $_GET['id'];
        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        echo '<rows>';

        treeDir($ids,$stat);

        echo '</rows>';
        break;

    case 2:
        $ids = $_GET['id'];
        $query = "SELECT  id,toc_chapter, toc_name  FROM google_docs WHERE doc_id =" . $ids;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        header('Content-type:text/xml;charset=ISO-8859-1;');
        echo '<?xml version="1.0" encoding="iso-8859-1"?>';

        echo '<rows>';
        while ($row = mysqli_fetch_assoc($result)) {

            echo "<row id='" . $row["id"] . "'>";
            echo "<cell>" . $row['toc_chapter'] . " " . $row['toc_name'] . "</cell>";

            echo '</row>';

        }
        echo '</rows>';

        break;

    case 3:
        $ids = $_GET['id'];
        $chapter_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $doc_id = filter_input(INPUT_POST, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
        $chaptercontent = filter_input(INPUT_POST, 'notes');

        $update = 'UPDATE  toc SET toc.bUpdate=1, toc.content ="' . mysqli_real_escape_string($dbc, $chaptercontent) . '" WHERE toc.id=' . $chapter_id;
        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {

            $response = [
                'response' => true,
                'text' => 'Updated Successfully!!',
                'doc_id'=>$doc_id
            ];

        } else {
            $response = [
                'response' => false,
                'text' => 'Error Occured, Try Again!!',
            ];
        }
        echo json_encode($response);

        break;

    case 4:

        $ids = $_GET['id'];
        $query = 'SELECT  toc.*,document.moodle_courseI_ID  FROM toc JOIN document ON document.id=toc.doc_id WHERE toc.id ="' . $ids . '"';
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
        $ispage = false;
        $isLesson = false;
        $Lessonid = -1;
        $level="";
        $id = '';
        $respons = true;
        $doc_id = '';
        $name = '';
        $section_id = '';
        $parent_id = '';
        $course_id = '';
        $content = '';
        $moodle_id = '';

        $module_id = 0;



        if ($row = mysqli_fetch_assoc($result)) {
            if ($row["type"] == "page") {
                $ispage = true;
                $module_id = 15;
            }
            if ($row["type"] == "lesson") {
                $isLesson = true;
                $module_id = 13;
            }
            $moodle_id = $row["moodle_id"];
            $level = $row["level_id"];
            $id = $row["id"];
            $doc_id = $row["doc_id"];
            $name = $row["chapter_id"]." ".$row["chapter"];
            $section_id = $row["section_id"];
            $parent_id = $row["parent_id"];
            $course_id = $row["moodle_courseI_ID"];
            $content = $row["uppercss"] . $row["content"] . $row["lowercss"];


        }


        if(isset($moodle_id)) {

            $respons = true;

            $response = [
                'id' => $id,
                'isL3' => false,
                'response' => $respons,
                'isPage' => $ispage,
                'doc_id' => $doc_id,
                'name'=> $name,
                'section_id'=>$section_id,
                'parent_id'=>$parent_id,
                'isLesson' => $isLesson,
                'course_id' => $course_id,
                'content' => $content,
                'lessonid' => $Lessonid
            ];

            echo json_encode($response);
            break;

        }


        else {

            $level = explode("_", $level);
            $level = $level[0];


            if ($level == "L3" || $level == "L4" || $level == "L5") {

                $respons = false;

                $response = [
                    'id' => $id,
                    'response' => $respons,
                    'isL3' => true,
                    'lessonid' => $id
                ];

                echo json_encode($response);

                break;
            }

            $respons = false;

            $response = [
                'id' => $id,
                'isL3' => false,
                'response' => $respons,
                'isPage' => $ispage,
                'doc_id' => $doc_id,
                'name' => $name,
                'section_id' => $section_id,
                'parent_id' => $parent_id,
                'isLesson' => $isLesson,
                'course_id' => $course_id,
                'content' => $content,
                'lessonid' => $Lessonid
            ];

            echo json_encode($response);
            break;
        }


//

        break;

    case 5:
        $ids = $_GET['id'];
        $dateTime = date("d.m.Y") . " " . date("h:i:sa");
        $content = filter_input(INPUT_POST, 'notes');

        $insert='UPDATE status SET Date_time="'.$dateTime.'",Content="'.mysqli_real_escape_string($dbc, $content).'"';
        $insertResult = mysqli_query($dbc, $insert)or die(mysqli_error($dbc));


        if ($insertResult) {

            $response = [
                'response' => true,
                'text' => 'Status Updated Successfully!!',
            ];

        } else {
            $response = [
                'response' => false,
                'text' => 'Error Occured, Try Again!!',
            ];
        }
        echo json_encode($response);

        break;
    case 6:
        $ids = $_GET['id'];
        $querys = 'SELECT  toc.lesson_id  FROM toc WHERE toc.id < "' . $ids . '" ORDER BY id desc LIMIT 1';
        $results = mysqli_query($dbc, $querys) or die(mysqli_error($dbc));

        if ($rows = mysqli_fetch_assoc($results)) {

            $Lessonid = $rows["lesson_id"];

            $respons = false;

            $response = [

                'lessonid' => $Lessonid
            ];

        }

        echo json_encode($response);

        break;

    case 7:
        $ids = $_GET['id'];
        $query = "SELECT  *  FROM moodle_servers ";
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        header('Content-type:text/xml;charset=ISO-8859-1;');
        echo '<?xml version="1.0" encoding="iso-8859-1"?>';

        echo '<rows>';
        while ($row = mysqli_fetch_assoc($result)) {

            echo "<row id='" . $row["id"] . "'>";
            echo '<cell></cell>';
            echo '<cell><![CDATA[' . $row["name"] . ']]></cell>';
            echo '<cell><![CDATA[' . $row["domain"] . ']]></cell>';
            echo '<cell><![CDATA[' . $row["token"] . ']]></cell>';
            echo '<cell><![CDATA[' . $row["path"] . ']]></cell>';

            echo '</row>';

        }
        echo '</rows>';

        break;
    case 8:
        $ids = $_GET['id'];
        $query = "SELECT
            project_course_question.* FROM project_course_question
            JOIN project_course_question_to_page ON project_course_question.id = project_course_question_to_page.question_id
            WHERE project_course_question_to_page.page_id =". $ids;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        header('Content-type:text/xml;charset=ISO-8859-1;');
        echo '<?xml version="1.0" encoding="iso-8859-1"?>';

        echo '<rows>';
        while ($row = mysqli_fetch_assoc($result)) {

            echo "<row id='" . $row["id"] . "'>";
            echo '<cell></cell>';

            $title = $row["title"];
            $title = explode("(", $title);
            $title_1 = $title[0];

            $type = $title[1];

            $type = str_replace(')',"" ,$type);


            echo '<cell><![CDATA[' . $title_1. ']]></cell>';
            echo '<cell><![CDATA[' . $type . ']]></cell>';

            echo '</row>';

        }
        echo '</rows>';
        break;

    case 9:
        $ids = $_GET['id'];
        $query = "SELECT  *  FROM project_course_choices WHERE question_id =". $ids;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        header('Content-type:text/xml;charset=ISO-8859-1;');
        echo '<?xml version="1.0" encoding="iso-8859-1"?>';

        echo '<rows>';
        while ($row = mysqli_fetch_assoc($result)) {

            echo "<row id='" . $row["id"] . "'>";
            echo '<cell></cell>';

            $text = $row["text"];
            $text = explode("(", $text);
            $text = $text[0];

            echo '<cell><![CDATA[' . $text. ']]></cell>';

            echo '</row>';

        }
        echo '</rows>';
        break;


    case 10:
        $ids = $_GET['id'];
        $query = "SELECT  *  FROM project_course_choices WHERE id =". $ids;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));




        while ($row = mysqli_fetch_assoc($result)) {

            $text = $row["text"];
            $text = explode("(", $text);
            $text = $text[0];
            $jump = $row["jumpto"];

            if(!$jump)
                $jump=2;

            $response = [
                'answer' => $text,
                'response' => $row["response"],
                'score' => $row["score"],
                'jumpto' => $jump,
            ];

        }

        echo json_encode($response);



        break;

    case 11:
        $ids = $_GET['id'];
        $query = "SELECT  *  FROM project_course_question WHERE id =". $ids;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));




        while ($row = mysqli_fetch_assoc($result)) {

            $title = $row["title"];
            $title = explode("(", $title);
            $title = $title[0];

            $response = [

                'title' => $title,
                'text' => strip_tags($row["text"]),
                'type' => $row["type"],
            ];

        }

        echo json_encode($response);



        break;

    case 12:
        $ids = $_GET['id'];
        $name = filter_input(INPUT_POST, 'name');
        $domain =  filter_input(INPUT_POST, 'domain');
        $token= filter_input(INPUT_POST, 'token');
        $path = filter_input(INPUT_POST, 'path');
        $location = filter_input(INPUT_POST, 'location');




        $query = "INSERT INTO moodle_servers (name,domain, token,path,location ) VALUES ('". $name."','".$domain."','".$token."','".$path."','".$location."') ON DUPLICATE KEY UPDATE name=values(name),token=values(token),location = values(location)";
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
        $response='';
        if($result) {
            $response = [
                'response' => true,
                'text' => "Saved"
            ];
        }
        else{
            $response = [
                'response' => true,
                'text' => "Error Occured"
            ];
        }

        echo json_encode($response);

        break;
    case 13:
        $id = $_GET['id'];


        $query = "DELETE FROM moodle_servers where id=".$id;
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
        $response='';
        if($result) {
            $response = [
                'response' => true,
                'text' => "Sever Deleted"
            ];
        }
        else{
            $response = [
                'response' => true,
                'text' => "Error Occured"
            ];
        }

        echo json_encode($response);



        break;


    case 14:
        $ids = $_GET['id'];
        $id = 0;

        if($ids){
            $query_1 = "SELECT moodle_servers.id
                      FROM moodle_doc_db.document
                      LEFT JOIN moodle_doc_db.course_server ON document.id = course_server.document_id
                      LEFT JOIN moodle_doc_db.moodle_servers ON moodle_servers.id = course_server.server_id WHERE document.id=".$ids;
            $result_1 = mysqli_query($dbc, $query_1) or die(mysqli_error($dbc));




           if ($row = mysqli_fetch_assoc($result_1)) {
                 $id = $row["id"];
            }

        }

        $query = "SELECT  *  FROM moodle_servers ";
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        header('Content-type:text/xml;charset=ISO-8859-1;');
        echo '<?xml version="1.0" ?>';

        echo '<complete>';
        while ($row = mysqli_fetch_assoc($result)) {

              if($id>0) {
                  if ($row["id"] === $id) {
                      echo '<option value="' . $row["id"] . '" selected ="1" ><![CDATA[' . $row["name"] . ']]></option>';
                  }
                  else {
                      echo '<option value="' . $row["id"] . '" ><![CDATA[' . $row["name"] . ']]></option>';
                  }
              }
              else if( $row["name"] =="education.nts.nl") {
                echo '<option value="'.$row["id"].'" selected ="1" ><![CDATA['.$row["name"].']]></option>';
            }
            else {
                echo '<option value="' . $row["id"] . '" ><![CDATA[' . $row["name"] . ']]></option>';
            }
        }
        echo '</complete>';



        break;
}



function treeDir($id,$stat)
{
    global $dbc;




    $query = "SELECT  document.moodle_courseI_ID, toc.*  FROM toc JOIN document ON toc.doc_id=document.id WHERE toc.doc_id =".$id." ORDER BY toc.parent_id = 0 DESC, toc.sort_id ASC";
    $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

    $objects = array();
    $roots = array();
    while ($row = mysqli_fetch_assoc($result)) {

        if (!isset($objects[$row['id']])) {
            $objects[$row['id']] = new stdClass;
            $objects[$row['id']]->children = array();
        }

        $obj = $objects[$row['id']];
        $obj->id = $row['id'];
        $obj->name = $row['chapter'];
        $obj->chapter_id = $row['chapter_id'];
        $obj->update = $row['bUpdate'];
        $obj->changed = $row['bChanged'];
        $obj->delete = $row['bDelete'];
        $obj->insert = $row['binsert'];
        $obj->inMoodle = $row['moodle_id'];

        if ($row['parent_id'] == 0) {
            $roots[] = $obj;
        } else {
            if (!isset($object[$row['parent_id']])) {
                $object[$row['parent_id']] = new stdClass;
                $object[$row['parent_id']]->children = array();
            }

            $objects[$row['parent_id']]->children[$row['id']] = $obj;
        }
    }

    foreach ($roots as $obj) {
        printXML($obj, true);
    }
}

function printXML(stdClass $obj, $isRoot = false)
{
    echo '<row id="' . $obj->id . '">';
    echo '<cell><![CDATA[' . $obj->chapter_id . " " . $obj->name . ']]></cell>';

    if($obj->inMoodle <= 0||$obj->insert||$obj->changed||$obj->update)
        echo '<cell>1</cell>';
    else
        echo '<cell>0</cell>';

    if($obj->update==1)
        echo '<cell>check-square-o</cell>';
    else
        echo '<cell >square-o</cell>';

    if($obj->changed==1)
        echo '<cell>check-square-o</cell>';
    else
        echo '<cell >square-o</cell>';

    if($obj->insert==1) {
        echo '<cell>check-square-o</cell>';
    }
    else {
        echo '<cell >square-o</cell>';
    }
    foreach ($obj->children as $child) {
        printXML($child);
    }

    echo '</row>';
}
