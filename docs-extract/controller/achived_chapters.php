<?php

include_once 'config.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$action = $_GET['action'];

$id = $_GET['id'];
$isDelete = $_GET['isDelete'];
$id_arr = $_GET['ids'];

//$arr = join(",",$id_arr);
//
$id_arr = explode(', ', $id_arr);
sort($id_arr);

switch ($action) {

    case 1:

        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        echo '<rows>';

        treeDir($id);

        echo '</rows>';
        break;

    case 2:

        $query = "SELECT  id,toc_chapter, toc_name  FROM google_docs WHERE doc_id =" . $id;
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
        $chapter_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $chaptercontent = filter_input(INPUT_POST, 'notes');

        $update = 'UPDATE  toc SET toc.content ="' . mysqli_real_escape_string($dbc, $chaptercontent) . '" WHERE toc.id=' . $chapter_id;
        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {

            $response = [
                'response' => true,
                'text' => 'Updated Successfully!!',
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

        $query = "SELECT  archived_toc.* ,document.moodle_courseI_ID  FROM archived_toc JOIN document ON document.id= archived_toc.doc_id WHERE archived_toc.id IN (".implode(',',$id_arr).")";
        $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
        $ispage = false;
        $isLesson = false;
        $module_id = 0;
        $moodle_ids = [];

            while ($row = mysqli_fetch_assoc($result)) {
                if ($row["type"] == "page") {
                    $ispage = true;
                    $module_id = 15;
                }

                if ($row["type"] == "lesson") {
                    $isLesson = true;
                    $module_id = 13;
                }

                $moodle_object = new stdClass;
                $moodle_object->id = $row["moodle_id"];
                $moodle_object->isLesson = $isLesson;
                $moodle_object->name = $row['chapter_id'].$row['chapter'];
                $moodle_object->module_id = $row['moodle_id'];
                $moodle_object->isLesson = $ispage;
                $moodle_object->course_id = $row["moodle_courseI_ID"];
                $moodle_object->content = $row["uppercss"].$row["content"].$row["lowercss"];

                $moodle_ids[] = $moodle_object;


            }
            echo "<pre>";
            print_r($moodle_ids);

//

        break;

    case 5:

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


        $id = filter_input(INPUT_GET, 'id');

        $delete='DELETE FROM archived_toc WHERE doc_id='.$id;
        $insertResult = mysqli_query($dbc, $delete)or die(mysqli_error($dbc));


        if ($insertResult) {

            $response = [
                'response' => true,
                'text' => 'Deleted!',
            ];

        } else {
            $response = [
                'response' => false,
                'text' => 'Error Occured, Try Again!!',
            ];
        }
        echo json_encode($response);

        break;

}

function treeDir($id)
{
    global $dbc;




    $query = "SELECT  *  FROM archived_toc WHERE doc_id =" . $id . " ORDER BY parent_id = 0 DESC, sort_id ASC";
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

    if($obj->delete==1)
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

    if($obj->delete==1)
        echo '<cell>check-square-o</cell>';
    else
        echo '<cell >square-o</cell>';
    foreach ($obj->children as $child) {
        printXML($child);
    }

    echo '</row>';
}
