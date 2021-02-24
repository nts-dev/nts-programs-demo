<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');

//require_once 'export_moodle.php';
require_once 'curl.php';

define('DATABASE', 'moodle_doc_db');
require 'nts_repl_msqli_config.php';



$course_id = $_GET['course_id'];

$id = $_GET['id'];
$section_id = 1;
$section_name = '';
$response = '';

$domainname = 'https://education.nts.nl'; //paste your domain here
$wstoken = 'd59c0678332d86f0e78e16d523acbe6e'; //here paste your enrol token
$restformat = 'json';

$objectsCreated = createObjects($id);

if ($objectsCreated) {
    echo json_encode(array('response' => true, 'text' =>  'Course Created with all Modules Added Successfully!'));


} else {
    echo json_encode(array('response' => false, 'text' => 'Failed Adding Modules!'));
}

function createObjects($id)
{
global $dbc;
    $createdObjects = '';
    $query = "SELECT * FROM moodle_doc_db.toc WHERE doc_id =" . $id . " ORDER BY parent_id = 0 DESC, id ASC";
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
        $obj->parent_id = $row['parent_id'];
        $obj->content = $row['content'];

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
        $createdObjects = printXML($obj, true);
    }

    return $createdObjects;
}

function printXML(stdClass $obj, $isRoot = false)
{

    global $section_id, $course_id, $section_name, $response;

    $dataObject = [
        'page_name' => $obj->chapter_id . $obj->name,
        'section_id' => $section_id,
        'parent_id' => $obj->parent_id,
        'content' => $obj->content,
        'course_id' => $course_id,
    ];

    if ($isRoot) {

        $section_name = $obj->chapter_id . $obj->name;
        $response = createPage($dataObject);

        foreach ($obj->children as $child) {
            printXML($child);
        }
        $section_id++;
    } else {

        $lessonid = createLesson($dataObject);
        $has_children = count($obj->children) > 0;

        if ($has_children) {

//            $lessonid = createLesson($dataObject);

            if ($lessonid) {

                foreach ($obj->children as $child) {
                    createLessonPage($child, $lessonid);
                }
            }
        } else {
//            createPage($dataObject);
            createLessonPage($obj, $lessonid);
        }
    }

    return $response;
}

function createPage($obj)
{

    global $domainname, $wstoken;
    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=3";
    $resp = $curl->post($serverurl, $obj);
    $pagedata = json_decode($resp);

    if ($pagedata->is_section) {
        updateTopicName($pagedata->section);
    }

    return $pagedata;
}

function createLesson($obj)
{

    global $domainname;
    $curl = new curl;
    $serverurl = $domainname . "/moosh.php?action=5";
    $resp = $curl->post($serverurl, $obj);
    $lessondata = json_decode($resp);

    if ($lessondata->data->response) {
        return $lessondata->data->row_id;
    }

    return false;
}

function createLessonPage(stdClass $obj, $lessonid)
{

    global $domainname;

    $pageObject = [
        'lessonid' => $lessonid,
        'title' => $obj->name,
        'contents' => $obj->content,
    ];

    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=5";
    $resp = $curl->post($serverurl, $pageObject);
//    echo json_decode($resp);

    foreach ($obj->children as $child) {
        createLessonPage($child, $lessonid);
    }
}

function updateTopicName($section_id)
{

    global $domainname, $wstoken, $section_name,$restformat;

    $wsfunctionname = 'core_update_inplace_editable';

    $params = [
        'component' => 'format_topics',
        'itemtype' => 'sectionname',
        'itemid' => $section_id,
        'value' => $section_name,
    ];

    $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

    $curl = new curl;
    $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
    $resp = $curl->post($serverurl . $restformat, $params);
}


