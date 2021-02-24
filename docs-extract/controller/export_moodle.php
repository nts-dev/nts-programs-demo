<?php
header("Access-Control-Allow-Origin: *");
//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

define('CREATE_COURSE', 1);
define('CREATE_MODULES', 2);
define('UPDATE_COURSE', 3);
define('RESTORE_COURSE', 4);
define('ADD_COURSE', 5);
define('CHECK_BOXES', 6);
define('DELETE_MODULES', 13);
define('ADD_MODULES', 14);

include_once 'config.php';

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');
require_once 'curl.php';
$responses = [];
$responsesModules = [];
$updateModules = [];
//create connection to remote
$remoteDbc = @mysqli_connect('83.98.243.187', 'root', 'kenya1234', 'moodle_doc_db');
if (!$remoteDbc) {
    $response = [
        'response' => true,
        'text' => 'Can not connect to remote server',
    ];
    $responses[] = $response;
    //echo json_encode($response);
} else {
    $response = [
        'response' => true,
        'text' => 'Remote server found',
    ];
    $responses[] = $response;
}


$document_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$document_id)
    $document_id = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
if (!$document_id)
    $document_id = filter_input(INPUT_GET, 'document_id', FILTER_SANITIZE_NUMBER_INT);
if (!$document_id)
    $document_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);


list($token, $domain) = getToken($document_id);
//

$domainname = $domain;//https://education.nts.nl'; //paste your domain here
$wstoken = $token;//'d59c0678332d86f0e78e16d523acbe6e'; //here paste your enrol token
$restformat = 'json';
$doc_name = '';

$moodle_ids = [];

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);

function getToken($doc_id)
{
    global $dbc;
    $query = "SELECT moodle_servers.path,moodle_servers.token
              FROM document
              LEFT JOIN course_server ON document.id = course_server.document_id
              LEFT JOIN moodle_servers ON moodle_servers.id = course_server.server_id WHERE document.id=" . $doc_id;
    $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
    $token = '';
    $domain = '';

    if ($row = mysqli_fetch_assoc($result)) {

        $token = $row["token"];
        $domain = $row["path"];

    }
    return array($token, $domain);
}

switch ($action) {

    case CREATE_COURSE:

        $document_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $Update = filter_input(INPUT_POST, 'update', FILTER_SANITIZE_NUMBER_INT);
        $file_name = filter_input(INPUT_POST, 'file_name');
        $doc_url = filter_input(INPUT_POST, 'url');
        $details = filter_input(INPUT_POST, 'details');

        $documents_query = "SELECT document.doc_name,course_server.moodle_course_id,course_server.server_id,moodle_servers.name
              FROM document
              LEFT JOIN course_server ON document.id = course_server.document_id
              LEFT JOIN moodle_servers ON moodle_servers.id = course_server.server_id WHERE document.id=" . $document_id;

        $documents_result = mysqli_query($dbc, $documents_query);
        $documents_num_rows = mysqli_num_rows($documents_result);

        if ($documents_num_rows == 0) {

            $response = [
                'response' => false,
                'text' => 'document does not exist!',
            ];

            echo json_encode($response);

            break;
        }

        $documents = mysqli_fetch_array($documents_result);
        $doc_name = $documents['doc_name'];

        if ($documents['moodle_course_id'] > 0 && !$Update) {

            $response = [
                'response' => true,
                'hasCourseid' => true,
                'course_id' => $documents['moodle_course_id'],
                'course_name' => $doc_name,
            ];
            echo json_encode($response);
            break;
        }


        if ($documents['moodle_course_id'] <= 0 && $Update) {

            $response = [
                'response' => false,
                'text' => $doc_name . ' Is not yet in moodle, export it then proceed with update!',
            ];
            echo json_encode($response);
            break;
        }
        if ($documents['moodle_course_id'] > 0 && $Update) {

            $response = backUpCourse($documents['moodle_course_id'], $doc_name, $document_id);

            echo $response;


            break;
        }
        if ($documents['server_id'] <= 0 && !$Update && $documents['moodle_course_id'] <= 0) {

            $response = [
                'response' => true,
                'hasServer' => true,
                'course_id' => $documents['moodle_course_id'],
                'course_name' => $doc_name,
            ];

            echo json_encode($response);

            break;
        }
        $server_id = $documents['server_id'];

        addCourse($document_id);


        echo json_encode($responses);

        break;


        break;

    case CREATE_MODULES:

        $document_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $course_id = filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT);

        $section_id = 1;
        $section_name = '';

        addModules($document_id);
        updateMoodle_id($moodle_ids, 0, true);

        checkQuestions($document_id);
        echo json_encode($responsesModules);


        break;

    case UPDATE_COURSE:

        $document_id = filter_input(INPUT_POST, 'document_id', FILTER_SANITIZE_NUMBER_INT);
        $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $doc_name = filter_input(INPUT_POST, 'doc_name');


        backUpCourse($course_id, $doc_name, $document_id);

        $deleteResult = deleteCourseModules($course_id);

        if (!$deleteResult) {
            $response = [
                'response' => false,
                'text' => 'Failed Deleting!',
            ];
            echo json_encode($response);
            break;
        }

        $addCourseResult = addCourse($document_id, true);

        if (!$addCourseResult['response']) {
            echo json_encode($addCourseResult);
            break;
        }


        $course_id = $addCourseResult['course_id'];
        $section_id = 1;
        $section_name = '';
        $response = addModules($document_id, true);


        echo json_encode($response);

        updateMoodle_id($moodle_ids, 0, true);


        break;

    case RESTORE_COURSE:

        $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $doc_name = filter_input(INPUT_POST, 'doc_name');

        $data = restoreCourse($course_id, $doc_name);
        echo $data;
        break;


    case CHECK_BOXES:

        $document_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);


        $documents_query = "SELECT document.doc_name,course_server.moodle_course_id,course_server.server_id
              FROM document
              LEFT JOIN course_server ON document.id = course_server.document_id
              LEFT JOIN server ON server.id = course_server.server_id WHERE document.id=" . $document_id;
        $documents_result = mysqli_query($dbc, $documents_query);

        $documents = mysqli_fetch_array($documents_result);
        $doc_name = $documents['doc_name'];

        if ($documents['moodle_course_id'] > 0) {

            $response = [
                'response' => true,
                'hasCourseid' => true,
                'course_id' => $documents['moodle_course_id'],
                // 'text' => $doc_name.' is not Yet in moodle, export it in order to update! '
            ];
            echo json_encode($response);
            break;
        } else {
            $response = [
                'response' => false,
                'inMoodle' => false,
                'text' => $doc_name . ' is not Yet in moodle, export it in order to update! ',

            ];
            echo json_encode($response);

        }
        break;


    case EXPORT_COURSE:
        $document_id = filter_input(INPUT_POST, 'doc_id');

        $response = addCourse($document_id);
        echo json_encode($response);
        break;


    case DELETE_MODULES:

        $doc_id = filter_input(INPUT_GET, 'doc_id');
        $id_arr = filter_input(INPUT_GET, 'ids');

        $id_arr = explode(', ', $id_arr);
        sort($id_arr);

        list($token, $domain) = getToken($doc_id);
        $domainname = $domain;
        $wstoken = $token;
        $restformat = 'json';
        $doc_name = '';

        deleteModules($id_arr);

        updateArchivedToc($doc_id);

        echo json_encode($updateModules);
        break;


    case ADD_MODULES:
        $doc_id = filter_input(INPUT_GET, 'doc_id');
        $id_arr = filter_input(INPUT_GET, 'dids');

        if ($id_arr) {
            $id_arr = explode(', ', $id_arr);
            sort($id_arr);
        }
        $id = filter_input(INPUT_GET, 'ids');
        if ($id) {
            $id = explode(',', $id);
            sort($id);
        }

        list($token, $domain) = getToken($doc_id);
        $domainname = $domain;
        $wstoken = $token;
        $restformat = 'json';
        $doc_name = '';

        if (!empty($id)) {
            addModulePageLessonSection($id, $doc_id);
        }
        if (!empty($id_arr)) {
            deleteModules($id_arr);
        }

        updateArchivedToc($doc_id);

        echo json_encode($updateModules);

        break;
    case ADD_COURSE:

        $doc_id = filter_input(INPUT_GET, 'doc_id');
        $name = filter_input(INPUT_GET, 'name');
        $doc_name = $name;
        $response = addCourse($doc_id);
        echo json_encode($response);

        break;
    default:
        break;
}


function addModulePageLessonSection($ids, $doc_id)
{
    global $dbc, $updateModules;
    $query = "SELECT  toc.* ,course_server.moodle_course_id  FROM toc  JOIN course_server ON course_server.document_id = toc.doc_id WHERE toc.id IN (" . implode(',', $ids) . ") ORDER BY sort_id ASC ";
    $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
    $module_id = 0;
    $toAddLessonPages = [];
    $toAddUpdateLessonPos = [];
    $id = '';
    $content = '';
    $name = '';

    while ($row = mysqli_fetch_assoc($result)) {
        $ispage = false;
        $isLesson = false;
        $islessonPage = false;
        if ($row["type"] == "page") {
            $ispage = true;
            $module_id = 15;
        } else if ($row["type"] == "lesson") {

            $isLesson = true;
            $module_id = 13;
        } else if ($row["type"] == "lessonpage")
            $islessonPage = true;

        $parent_id = $row['parent_id'];
        $id = $row['id'];
        $name = $row['chapter_id'] . " " . $row['chapter'];
        $content = $row["uppercss"] . $row["content"] . $row["lowercss"];
        $moodle_id = $row['moodle_id'];
        $module_id = $row['module_id'];
        $lesson = $row['lesson_id'];
        $section_id = $row['section_id'];
        $Mdl_section_id = $row['lesson_id'];
        $course_id = $row["moodle_course_id"];


        $dataObject = [
            'page_name' => $name,
            'section_id' => $section_id,
            'parent_id' => $row['parent_id'],
            'content' => $row["uppercss"] . $row["content"] . $row["lowercss"],
            'course_id' => $row["moodle_course_id"],
        ];

        if ($moodle_id > 0) {

            if ($ispage) {


                if ($row['bChanged'] > 0 || $row['bUpdate'] > 0) {
                    $sectionname = $row['chapter_id'] ." ". $row['chapter'];

                    updateTopicName($Mdl_section_id, $sectionname, $moodle_id, $id, true);

                    UpdatePage($moodle_id, $content,$sectionname);

                    UpdatePageNameContent($moodle_id, $name, $content);
                }


                updateMoodle_idonInsert($moodle_id, $Mdl_section_id, $id, $module_id);
            }

            if ($isLesson) {

                if ($row['bUpdate'] > 0 || $row['bChanged'] > 0) {
                    UpdateLesson($lesson, $name, $course_id);
                    UpdateLessonPage($moodle_id, $content,$name, true);
                    UpdateLessonPageName($moodle_id, $name, $course_id, true);
                }
                updateMoodle_idonInsert($moodle_id, $lesson, $id, $module_id);
            }
            if ($islessonPage) {

                if ($row['bUpdate'])
                    UpdateLessonPage($moodle_id, $content,$name, false);

                if ($row['bChanged'] > 0) {
                    UpdateLessonPageName($moodle_id, $name, $course_id, false);

                }
                updateMoodle_idonInsert($moodle_id, $lesson, $id, $module_id);
            }


        } else {

            if ($ispage) {

                $pageid = insertPage($dataObject, $name, $id);


                if ($pageid) {
                    $response = [
                        'response' => true,
                        'text' => $name.' Page inserted successfully Updated',
                    ];
                    $updateModules[] = $response;
                } else {
                    $response = [
                        'response' => false,
                        'text' => 'An Error Occured While Saving '.$name,
                    ];

                    $updateModules[] = $response;
                    return;
                }


            } else if ($isLesson) {

                list($lesson_id, $moduleid) = insertLessons($dataObject);

                if ($lesson_id > 0) {

                    $lessonObject = [
                        'lessonid' => $lesson_id,
                        'title' => $row['chapter_id'] ." ". $row['chapter'],
                        'contents' => $row["uppercss"] . $row["content"] . $row["lowercss"],
                    ];

                    $response = [
                        'response' => true,
                        'text' => $row['chapter_id'] ." ". $row['chapter'].' Lesson Inserted',
                    ];
                    $updateModules[] = $response;

                    $lessonPageId = insertLessonPage($lessonObject, $id, $module_id);

                    if ($lessonPageId) {
                        updateMoodle_idonInsert($lessonPageId, $lesson_id, $id, $moduleid);
                        $response = [
                            'response' => true,
                            'text' => $row['chapter_id'] ." ". $row['chapter'].' LessonPage Inserted',
                        ];
                        $updateModules[] = $response;
                    } else {
                        $response = [
                            'response' => true,
                            'text' => 'problem Occured while inserting a lesson page',
                        ];
                        $updateModules[] = $response;
                    }

                    $modules = new stdClass;
                    $modules->module_id = $moduleid;
                    $modules->sort_id = $row['sort_id'];
                    $modules->section = $section_id;
                    $modules->doc_id = $row['doc_id'];
                    $modules->chapter_id = $row['chapter_id'];
                    $modules->type = $row['type'];

                    $toAddUpdateLessonPos[] = $modules;


                } else {

                    $response = [
                        'response' => true,
                        'text' => 'problem Occured while inserting  lesson '.$row['chapter_id'] . $row['chapter'],
                    ];
                    $updateModules[] = $response;
                    return;

                }

            } //add id for lessonPages to array for later insert
            else if ($islessonPage) {

                $toAddLessonPages[] = $row['id'];

                $moodle_object = new stdClass;
                $moodle_object->sort_id = $row['sort_id'];
                $moodle_object->doc_id = $row['doc_id'];
                $moodle_object->chapter_id = $row['chapter_id'];
                $moodle_object->name = $name;
                $moodle_object->id = $id;
                $moodle_object->section = $section_id;
                $moodle_object->content = $content;


                $toAddLessonPages[] = $moodle_object;

            }
        }
    }

    if (count($toAddLessonPages) > 0)
        searchLessonids($toAddLessonPages);

    if (count($toAddUpdateLessonPos) > 0)
        searchmodules($toAddUpdateLessonPos, $course_id);


}

function searchmodules($toAddUpdateLessonPos, $course_id)
{

    global $dbc;
    $toAddmodule_ids = [];
    foreach ($toAddUpdateLessonPos as $obj) {

        $query = 'SELECT  toc.module_id  FROM toc  WHERE toc.doc_id ="' . $obj->doc_id . '" AND type = "' . $obj->type . '" AND section_id="' . $obj->section . '" AND chapter_id >"' . $obj->chapter_id . '" ORDER BY toc.chapter_id ASC LIMIT 1 ';
        $results = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($rows = mysqli_fetch_assoc($results)) {


            $moodle_object = new stdClass;
            $moodle_object->prevModuleId = $rows["module_id"];
            $moodle_object->module_id = $obj->module_id;
            $moodle_object->section = $obj->section;


            $toAddmodule_ids[] = $moodle_object;
        }

    }
//    echo "<pre>";
//
//    print_r($toAddmodule_ids);
    moveToCorrectLocation($toAddmodule_ids, $course_id);
}

function moveToCorrectLocation($toAddmodule_ids, $course_id)
{

    foreach ($toAddmodule_ids as $module_id) {

        global $domainname, $moodle_ids;

        $obj = [
            'module_id_to' => $module_id->prevModuleId,
            'module_id' => $module_id->module_id,
            'section' => $module_id->section,
            'course_id' => $course_id,
        ];
        $curl = new curl;
        $serverurl = $domainname . "/moosh.php?action=9";

        $resp = $curl->post($serverurl, $obj);
        $lessondata = json_decode($resp);


    }

}

function checkQuestions($doc_id)
{
    global $dbc;
    $id_array = [];
    $query = "SELECT id, lesson_id FROM toc where hasQuestion = 1 and doc_id= " . $doc_id;

    $results = mysqli_query($dbc, $query);

    while ($row = mysqli_fetch_array($results)) {

        $ids = new stdClass;
        $ids->id = $row['id'];
        $ids->lesson = $row['lesson_id'];

        $id_array[] = $ids;
    }

    foreach ($id_array as $id) {
        addQuestions($id->id, $id->lesson);
    }
}

function addQuestions($page_id, $lesson_id)
{

    global $domainname, $dbc;
    $objects = [];
    $prevpageid = $page_id;

    $query = "
            SELECT
                question.id,
                question.title,
                question.text,
                question.type,
                question.qoption,
                choices.id choice_id,
                choices.text choice_text,
                choices.score,
                choices.response,
                choices.responseformat,
                choices.jumpto
            FROM
               project_course_question_to_page question_page
            JOIN project_course_question question ON question.id = question_page.question_id
            LEFT JOIN project_course_choices choices ON choices.question_id = question.id
            WHERE
                question_page.page_id = " . $page_id . "
            ORDER BY
                question.id";

    $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

    while ($row = mysqli_fetch_array($result)) {

        if (!isset($objects[$row['id']])) {
            $objects[$row['id']] = new stdClass;
            $objects[$row['id']]->choices = [];
        }

        $title = $row["title"];
        $title = explode("(", $title);
        $title = $title[0];

        $question = $objects[$row['id']];
        $question->id = $row['id'];
        $question->qtype = $row['type'];
        $question->title = $title;
        $question->contents = $row['text'];
        $question->qoption = $row['qoption'];


        if ($row['choice_id']) {
            $choice_text = $row["choice_text"];
            $choice_text = explode("(", $choice_text);
            $choice_text = $choice_text[0];

            $choice = new stdClass;
            $choice->id = $row['choice_id'];
            $choice->score = $row['score'];
            $choice->answer = $choice_text;
            $choice->response = $row['response'];
            $choice->responseformat = $row['responseformat'];
            $choice->jumpto = $row['jumpto'];

            $question->choices[$row['choice_id']] = $choice;
        }
    }

    $serverurl = $domainname . "/data_content.php";

    $params = array(
        'lessonid' => $lesson_id,
        'prevpageid' => $prevpageid,
        'question' => serialize($objects)
    );

    $curl = new curl;
    $resp = $curl->post($serverurl . "?action=13", $params);
    $response = json_decode($resp);


    if ($response->success) {

        foreach ($response->page_ids as $pid => $mid) {
            $updatePages = "UPDATE project_course_question_to_page SET moodle_id = " . $mid . ",is_updated=0 WHERE question_id = " . $pid . " AND page_id = " . $page_id;
            mysqli_query($dbc, $updatePages);
        }

        foreach ($response->choice_ids as $pid => $mid) {
            $updateAnswers = "UPDATE project_course_choices SET moodle_id = " . $mid . ",is_updated=0 WHERE id = " . $pid;
            mysqli_query($dbc, $updateAnswers);
        }


    } else {
        $response = [
            'response' => false,
            'text' => 'Error Occured!',
        ];

        echo json_encode($response);
    }

}

function searchLessonids($toAddLessonPages)
{
    global $dbc;
    $toAddLesson_ids = [];


    foreach ($toAddLessonPages as $obj) {

        //get id from the seconf dot
        $lesson = preg_split("/(.*?\..*?)\./", $obj->chapter_id, NULL,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $lesson = $lesson[0];

        $query = 'SELECT  toc.lesson_id  FROM toc WHERE toc.sort_id < "' . $obj->sort_id . '" AND toc.doc_id="' . $obj->doc_id . '"AND toc.section_id="' . $obj->section . '" AND chapter_id = "' . $lesson . '"';
        $results = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

        if ($rows = mysqli_fetch_assoc($results)) {

            $toAddLesson_ids[] = $rows["lesson_id"];

            $moodle_object = new stdClass;
            $moodle_object->id = $obj->id;
            $moodle_object->lesson_id = $rows["lesson_id"];
            $moodle_object->name = $obj->name;
            $moodle_object->content = $obj->content;
            $moodle_object->module_id = $rows["module_id"];

            $toAddLesson_ids[] = $moodle_object;


        }
    }

    addlessonpages($toAddLesson_ids);
}

function addlessonpages($toAddLesson_ids)
{

    $lesson_id = 0;

    $toAddLesson_ids = array_filter($toAddLesson_ids);
    sort($toAddLesson_ids);
    foreach ($toAddLesson_ids as $obj) {

        if ($obj->lesson_id != '') {
            $lessonObject = [
                'lessonid' => $obj->lesson_id,
                'title' => $obj->name,
                'contents' => $obj->content,
            ];
            $response = insertLessonPage($lessonObject, $obj->id);
            updateMoodle_idonInsert($response, $obj->lesson_id, $obj->id, $obj->module_id);

        } else {
            if ($lesson_id) {
                // echo $lesson_id . "=>" . $obj->name;
                $lessonObject = [
                    'lessonid' => $lesson_id,
                    'title' => $obj->name,
                    'contents' => $obj->content,
                ];
                $response = insertLessonPage($lessonObject, $obj->id);
                updateMoodle_idonInsert($response, $lesson_id, $obj->id, $obj->module_id);
            }
        }
        $responses['data'] = array('response' => false, 'text' => 'LessonPage inserted!');
        $lesson_id = $obj->lesson_id;

    }

}

function deleteModules($id_arr)
{
    global $dbc,$updateModules;

    $query = "SELECT  archived_toc.* ,course_server.moodle_course_id  FROM archived_toc JOIN course_server ON course_server.document_id = archived_toc.doc_id WHERE archived_toc.id IN(" . implode(',', $id_arr) . ")";
    $result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
    $module_id = 0;
    $toDeletemoodle_ids = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $ispage = false;
        $isLesson = false;
        if ($row["type"] == "page") {
            $ispage = true;
            $module_id = 15;
        } else if ($row["type"] == "lesson") {
            $isLesson = true;
            $module_id = 13;
        }


        $toDeletemoodle_ids[] = $row['id'];


        $instance_id = $row['moodle_id'];
        $course_id = $row["moodle_course_id"];
        $lesson_id = $row['lesson_id'];
        $parent_id = $row['parent_id'];
        $sectionid = $row['lesson_id'];
        $name =   $row['chapter_id']." ".$row['chapter'];

        if ($parent_id == 0) {

            $page = deletePages($module_id, $instance_id, $course_id,$name);
            if ($page)
                deleteSection($sectionid, $course_id);


        }
        if ($ispage) {


            // echo json_encode($response);
        }
        if ($isLesson) {
            deleteLesson($lesson_id, $course_id,$name);

        }
        if (!$ispage && !$isLesson) {
            deleteLessonPages($instance_id, $course_id,$name);

        }
    }
//delete chapters in archive toc after updating
    deleteChapters($toDeletemoodle_ids);

}

function updatesectionName($obj)
{

    global $domainname;
    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=17";
    $resp = $curl->post($serverurl, $obj);
    $pagedata = json_decode($resp);


}

function addToArchive($doc_id)
{

    global $dbc;
    $query_delete_prev = 'DELETE FROM archived_toc WHERE doc_id=' . $doc_id;

    $result = mysqli_query($dbc, $query_delete_prev) or die(mysqli_error($dbc));


    if ($result) {

        $export_query = 'INSERT INTO archived_toc
                        (id,doc_id,sort_id,doc_name,date_time,chapter_id,chapter,parent_id,content,`type`,charVal,level_id,uppercss,lowercss,moodle_id,binsert,bUpdate,bDelete,bContent_update,bChanged,toUpdate)
                        SELECT id,doc_id,sort_id,doc_name,date_time,chapter_id,chapter,parent_id,content,`type`,charVal,level_id,uppercss,lowercss,moodle_id,binsert,bUpdate,bDelete,bContent_update,bChanged,toUpdate FROM toc
                        WHERE toc.doc_id= ' . $doc_id . ' ON DUPLICATE KEY UPDATE id=values(id),date_time=values(date_time),parent_id=values(parent_id),chapter_id=values(chapter_id),chapter=values(chapter)
                        ,content=values(content),sort_id=values(sort_id),type =values(type),bUpdate =values(bUpdate),bChanged =values(bChanged),uppercss =values(uppercss),lowercss =values(lowercss)
                        ,binsert =values(binsert),charVal =values(charVal),level_id =values(level_id),bDelete =values(bDelete),toUpdate = values(toUpdate)';


        $result = mysqli_query($dbc, $export_query) or die(mysqli_error($dbc));
    }

    if (!$result) {
        echo "Problem insert to archive";
    }
    return $result;
}

function deleteChapters($toDeletemoodle_ids)
{
    global $dbc;
    foreach ($toDeletemoodle_ids as $id) {

        $delete = "DELETE FROM archived_toc WHERE id=" . $id;

        $result = mysqli_query($dbc, $delete) or die(mysqli_error($dbc));


    }
}

//delete section
function deleteSection($sectionid, $course_id)
{

    global $domainname, $wstoken;

    $modulesObject = [
        'section_id' => $sectionid,
        'course_id' => $course_id
    ];


    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=15";
    $pageData = $curl->post($serverurl, $modulesObject);

    return $pageData;
}

function deletePages($module_id, $instance_id, $course_id,$name)
{

    global $domainname, $wstoken, $updateModules;

    $modulesObject = [
        'instance_id' => $instance_id,
        'module_id' => $module_id,
        'course_id' => $course_id
    ];

    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=9";
    $pageData = $curl->post($serverurl, $modulesObject);


    $response = [
        'response' => true,
        'text' => $name. " Page Deleted!"
    ];
    $updateModules[] = $response;
    return $pageData;
}

//delete lesson
function deleteLesson($lesson_id, $course_id,$name)
{

    global $domainname, $wstoken, $updateModules;

    $modulesObject = [
        'lessonid' => $lesson_id,
        'course_id' => $course_id
    ];

    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=14";
    $lessonData = $curl->post($serverurl, $modulesObject);

    if ($lessonData) {
        $response = [
            'response' => true,
            'text' => $name." Lesson Deleted!"
        ];
        $updateModules[] = $response;
    } else {
        $response = [
            'response' => false,
            'text' => "Unable to delete Lesson".$name
        ];
        $updateModules[] = $response;
    }
    return $lessonData;
}

function deleteLessonPages($instance_id, $course_id,$name)
{
    global $domainname, $wstoken, $updateModules;
    $modulesObject = [
        'instance_id' => $instance_id,
        'course_id' => $course_id
    ];
    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=10";
    $pageData = $curl->post($serverurl, $modulesObject);
    if ($pageData) {
        $response = [ 'response' => true,'text' => $name." LessonPage Deleted!"];
        $updateModules[] = $response;
    } else {
        $response = ['response' => false,'text' => "Unable to delete LessonPage ".$name ];
        $updateModules[] = $response;
    }
    return $pageData;
}

function updateCourse($document_id, $course_id)
{
    $deleteResult = deleteCourseModules($course_id);
    if (!$deleteResult) {
        $response = ['response' => false,'text' => 'Failed Deleting!',];
        return $response;
    }

    $addCourseResult = addCourse($document_id, true);

    if (!$addCourseResult['response']) {
        return $addCourseResult;
    }

    $course_id = $addCourseResult['course_id'];
    $section_id = 1;
    $section_name = '';
    $response = addModules($document_id, true);

    return $response;
}

function addModules($document_id, $isUpdate = false)
{
    global $moodle_ids, $responsesModules;
    $objectsCreated = createObjects($document_id);

    if ($objectsCreated) {
        if (!$isUpdate)
            addToArchive($document_id);
        $response = [
            'response' => true,
            'text' => $isUpdate ? 'Modules Updated' : 'Course Created with all Modules Added Successfully!',
        ];
        $responsesModules[] = $response;
    } else {
        $response = ['response' => false,'text' => $isUpdate ? 'Failed Updating Modules' : 'Failed Adding Modules!',];
        $responsesModules[] = $response;
    }

    return $response;
}

function addCourse($document_id, $isUpdate = false)
{
    global $responses;
    $createCourseResult = createCourse($document_id);
    if (!$createCourseResult['response']) {
        return $createCourseResult;
    }
    $course_id = $createCourseResult['course_id'];

    if ($course_id) {

        $response = [
            'response' => true,
            'toExport' => true,
            'course_id' => $course_id,
            'id' => $document_id,
            'text' => $isUpdate ? 'Updating...' : 'Course Created Successfuly, Adding Modules...',
        ];
        $responses[] = $response;
    } else {
        $response = [ 'response' => false,'text' => $isUpdate ? 'Updating Failed' : 'Failed Creating Course, Try Again!',];
        $responses[] = $response;
        return;
    }
}

function deleteCourseModules($course_id)
{

    global $domainname, $wstoken, $restformat;
    $response = true;

    $wsfunctionname = 'core_course_delete_courses';

    $params = ['courseids' => [$course_id],];
    $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

    $curl = new curl;
    $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
    $resp = $curl->post($serverurl . $restformat, $params);

    if (count($resp->warnings) > 0) {
        $response = false;
    }

    return $response;
}

//functions
function createCourse($document_id)
{
    global $domainname, $wstoken, $doc_name, $dbc, $remoteDbc, $doc_url, $details, $server_id, $responses;

    $restformat = 'json';

    $wsfunctionname = 'core_course_create_courses';

    $params = [
        'courses' => [
            [
                'fullname' => $doc_name,
                'shortname' => $doc_name,
                'categoryid' => 1,
                'format' => 'topics',
                'courseformatoptions' => [
                    ['name' => 'numsections', 'value' => '0'],
                ],
            ],
        ],
    ];
    $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
    $curl = new curl;
    $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
    $resp = $curl->post($serverurl . $restformat, $params);
    $coursedata = json_decode($resp);

    if (!$coursedata) {
        $response = ['response' => false,'text' => $resp,];
        $responses[] = $response;

        return;
    }
    if ($coursedata->errorcode) {
        $response = ['response' => false,'text' => $coursedata->message,
        ];
        $responses[] = $response;
        return;
    }
    if (count($coursedata) == 0) {
        $response = ['response' => false,'text' => $coursedata,];
        $responses[] = $response;
        return;
    }

    $course_id = $coursedata[0]->id;

    $update = "UPDATE `course_server` SET moodle_course_id =" . $course_id . " WHERE document_id =" . $document_id;
    $updateResult = mysqli_query($dbc, $update);

    if (!$updateResult) {
        $response = ['response' => true,'text' => 'Failed to update moodle course id!',];
        $responses[] = $response;
        return;
    }

    if ($remoteDbc != false) {
        if ($domainname != "https://education.nts.nl") {

            $dateTime = date("d.m.Y") . " " . date("h:i:sa");
            $query_insert_document = 'INSERT INTO document (doc_name,document_url,local_course_id,date_time,details) VALUES ("' . $doc_name . '", "' . $doc_url . '", "' . $course_id . '", "' . $dateTime . '","' . $details . '")
    ON DUPLICATE KEY UPDATE local_course_id=values(local_course_id)';

            $result = mysqli_query($remoteDbc, $query_insert_document) or die(mysqli_error($remoteDbc));
            $docid = mysqli_insert_id($remoteDbc);
            if ($docid) {
                $updateLocal = 'INSERT INTO course_server (document_id,server_id) VALUES (' . $docid . ',' . $server_id . ')
            ON DUPLICATE KEY UPDATE document_id=values(document_id)';
                $updateLocalResult = mysqli_query($remoteDbc, $updateLocal);

                if (!$updateLocalResult) {
                    $response = ['response' => true,'text' => 'Failed to update local course id!',];
                    $responses[] = $response;
                    return;
                }
            } else {
                $response = ['response' => true,'text' => 'Can not connect to remote server!',];
                $responses[] = $response;
            }
        }
    }
    $response = [
        'response' => true,'course_id' => $course_id,];
    return $response;
}

function createObjects($id)
{

    global $dbc;
    $response = true;

    $query = "SELECT * FROM toc WHERE doc_id =" . $id . " ORDER BY parent_id = 0 DESC, id ASC";
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
        $obj->content = $row['uppercss'] . $row['content'] . $row['lowercss'];
        $obj->raw_content = $row['content'];

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

    return $response;
}

function printXML(stdClass $obj, $isRoot = false)
{

    global $section_id, $course_id, $section_name, $response, $moodle_ids;

    $dataObject = [
        'page_name' => $obj->chapter_id . " " . $obj->name,
        'section_id' => $section_id,
        'parent_id' => $obj->parent_id,
        'content' => $obj->content,
        'course_id' => $course_id,
        'id' => $obj->id
    ];

    if ($isRoot) {

        $section_name = $obj->chapter_id . ' ' . $obj->name;
        $response = createPage($obj->id, $dataObject, $section_name);

        foreach ($obj->children as $child) {
            printXML($child, false);
        }
        $section_id++;
    } else {

        list($lessonid, $module_id) = createLesson($obj->id, $dataObject, false);
        $has_children = count($obj->children) > 0;
        $lessondataObject = [
            'title' => $obj->chapter_id . " " . $obj->name,
            'lessonid' => $lessonid,
            'contents' => $obj->content,

        ];
        $lessonPageId = insertLessonPage($lessondataObject, $lessonid, $module_id);

        if ($lessonPageId > 0) {

            $moodle_object = new stdClass;
            $moodle_object->id = $obj->id;
            $moodle_object->moodle_id = $lessonPageId;
            $moodle_object->lessonid = $lessonid;
            $moodle_object->module_id = $module_id;
            $moodle_object->type = 'Lessonpage';

            $moodle_ids[] = $moodle_object;
        }

        if ($has_children) {
            if ($lessonid) {

                foreach ($obj->children as $child) {
                    createLessonPage($child, $lessonid, $module_id);
                }
            }
        }

    }

    return $response;
}

function createPage($id, $obj, $name)
{

    global $domainname, $wstoken, $dbc,$responsesModules;
    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=3";
    $resp = $curl->post($serverurl, $obj);
    $pagedata = json_decode($resp);


    if ($pagedata->is_section) {
        updateTopicName($pagedata->section, $name, $pagedata->page_id, $id, false);

        $response = ['response' => true,'text' => $name . " Added successfully",];
        $responsesModules[] = $response;



    }

    return false;
}

function insertPage($obj, $name, $id)
{

    global $domainname, $wstoken, $moodle_ids;
    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=3";
    $resp = $curl->post($serverurl, $obj);
    $pagedata = json_decode($resp);

    if ($pagedata->is_section) {


        updateTopicName($pagedata->section, $name, $pagedata->page_id, $id, true);

    }
    return $pagedata->page_id;
}

function createLesson($id, $obj, $checked)
{


    global $domainname, $moodle_ids;
    $curl = new curl;
    $serverurl = $domainname . "/moosh.php?action=5";
    $resp = $curl->post($serverurl, $obj);
    $lessondata = json_decode($resp);

    if ($lessondata->data->response) {
        $lesson_id = $lessondata->data->row_id;

        $moodle_object = new stdClass;
        $moodle_object->id = $id;
        $moodle_object->moodle_id = $lesson_id;
        $moodle_object->lessonid = $lesson_id;
        $moodle_object->module_id = $lessondata->data->module_id;
        $moodle_object->type = 'lesson';

        if (!$checked)
            $moodle_ids[] = $moodle_object;

        return array($lesson_id, $lessondata->data->module_id);
    }

    return false;
}

function insertLessons($obj)
{


    global $domainname, $moodle_ids;
    $curl = new curl;
    $serverurl = $domainname . "/moosh.php?action=5";
    $resp = $curl->post($serverurl, $obj);
    $lessondata = json_decode($resp);

    $lesson_id = $lessondata->data->row_id;
    $module_id = $lessondata->data->module_id;


    return array($lesson_id, $module_id);


}


function insertLessonPage($obj, $id)
{

    global $domainname, $moodle_ids;

    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=5";
    $resp = $curl->post($serverurl, $obj);
    $res = json_decode($resp);

    return $res->data->response;
}

function createLessonPage(stdClass $obj, $lessonid, $module_id)
{

    global $domainname, $moodle_ids;

    $pageObject = [
        'lessonid' => $lessonid,
        'title' => $obj->chapter_id . ' ' . $obj->name,
        'contents' => $obj->content,
    ];

    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=5";
    $resp = $curl->post($serverurl, $pageObject);
    $res = json_decode($resp);

    $moodle_object = new stdClass;
    $moodle_object->id = $obj->id;
    $moodle_object->moodle_id = $res->data->response;
    $moodle_object->lessonid = $lessonid;
    $moodle_object->module_id = $module_id;
    $moodle_object->type = 'Lessonpage';

    $moodle_ids[] = $moodle_object;

    foreach ($obj->children as $child) {

        createLessonPage($child, $lessonid, $module_id);
    }
}

function updateTopicName($section_id, $name, $pageid, $id, $check)
{
    global $domainname, $wstoken, $restformat, $dbc, $updateModules,$responsesModules;
    $wsfunctionname = 'core_update_inplace_editable';

    $params = [
        'component' => 'format_topics',
        'itemtype' => 'sectionname',
        'itemid' => $section_id,
        'value' => $name,
    ];
    $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

    $curl = new curl;
    $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
    $resp = $curl->post($serverurl . $restformat, $params);
    $pagedata = json_decode($resp);

    $response = ['response' => true,'text' => $name . " Updated successfully",];

    if ($check) {
        $updateModules[] = $response;
    }

    updateMoodle_idonInsert($pageid, $section_id, $id, $section_id);
}

function updateMoodle_id($moodle_ids, $id, $check)
{
    global $dbc, $responsesModules;

    if ($check) {
        foreach ($moodle_ids as $obj) {
            if ($obj->id > 0) {
                $update = "UPDATE `toc` SET moodle_id ='" . $obj->moodle_id . "',lesson_id='" . $obj->lessonid . "',module_id=" . $obj->module_id . " , binsert =0,bUpdate=0,bChanged=0 WHERE id =" . $obj->id;
                $updateResult = mysqli_query($dbc, $update);

                if (!$updateResult) {
                    $response = [
                        'response' => true,
                        'text' => "Unable to update module " . $obj->id,
                    ];
                    $responsesModules[] = $response;
                    return;
                }

            }
        }
    } else {
        $update = "UPDATE `toc` SET binsert =0,bUpdate=0,bChanged=0 WHERE id =" . $id;
        $updateResult = mysqli_query($dbc, $update);

        if (!$updateResult) {
            $response = [
                'response' => true,
                'text' => "Unable to refresh " . $id,
            ];
            $responsesModules[] = $response;
            return;
        }
    }
    return $updateResult;

}

function updateMoodle_idonInsert($moodle_id, $lessonid, $id, $module_id)
{
    global $dbc, $responsesModules;


    $update = "UPDATE `toc` SET module_id ='" . $module_id . "',lesson_id='" . $lessonid . "', moodle_id ='" . $moodle_id . "' , binsert =0,bUpdate=0,bChanged=0 WHERE id =" . $id;
    $updateResult = mysqli_query($dbc, $update);

    if (!$updateResult) {

        $response = [
            'response' => true,
            'text' => "Unable to insert " . $id,
        ];
        $responsesModules[] = $response;
        return;
    }
}

//update archived toc
function updateArchivedToc($doc_id)
{
    global $dbc;
    $query_delete_prev = 'DELETE FROM archived_toc WHERE doc_id=' . $doc_id;

    $result = mysqli_query($dbc, $query_delete_prev) or die(mysqli_error($dbc));


    if ($result) {
        $export_query = 'INSERT INTO archived_toc
 (id,doc_id,sort_id,doc_name,date_time,chapter_id,chapter,parent_id,content,`type`,charVal,level_id,uppercss,lowercss,section_id,moodle_id,lesson_id,binsert,bUpdate,bDelete,bContent_update,bChanged,toUpdate)
SELECT id,doc_id,sort_id,doc_name,date_time,chapter_id,chapter,parent_id,content,`type`,charVal,level_id,uppercss,lowercss,section_id,moodle_id,lesson_id,binsert,bUpdate,bDelete,bContent_update,bChanged,toUpdate FROM toc
WHERE toc.doc_id= ' . $doc_id . ' ON DUPLICATE KEY UPDATE id=values(id),date_time=values(date_time),parent_id=values(parent_id),chapter_id=values(chapter_id),chapter=values(chapter)
,content=values(content),sort_id=values(sort_id),type =values(type),bUpdate =values(bUpdate),bChanged =values(bChanged),lesson_id =values(lesson_id),uppercss =values(uppercss),section_id =values(section_id),lowercss =values(lowercss),binsert =values(binsert),section_id =values(section_id),charVal =values(charVal),level_id =values(level_id),bDelete =values(bDelete),toUpdate = values(toUpdate)';


        $result = mysqli_query($dbc, $export_query) or die(mysqli_error($dbc));
    }
}

function backUpCourse($courseid, $name, $document_id)
{

    global $domainname, $wstoken;

    $courseObject = [
        'course_id' => $courseid,
        'doc_name' => $name,
        'docid' => $document_id

    ];

    $curl = new curl;
    $serverurl = $domainname . "/moosh.php?action=6";
    $course_stat = $curl->post($serverurl, $courseObject);
    return $course_stat;
}

function restoreCourse($course_id, $name)
{

    global $domainname, $wstoken;

    $courseObject = [

        'course_id' => $course_id,
        'doc_name' => $name

    ];

    $curl = new curl;
    $serverurl = $domainname . "/moosh.php?action=7";
    $course_stat = $curl->post($serverurl, $courseObject);
    return $course_stat;
}

function UpdatePage($pageid, $pageContent,$sectionname)
{

    global $domainname, $wstoken, $updateModules;

    $courseObject = [
        'page_id' => $pageid,
        'content' => $pageContent,

    ];

    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=7";
    $pageData = $curl->post($serverurl, $courseObject);

    if ($pageData) {

        $response = [
            'response' => true,
            'text' => $sectionname. ' Page Updated Successfully!',
        ];
        $updateModules[] = $response;

    } else {
        $response = [
            'response' => false,
            'text' => 'Failed Updating '.$sectionname.', try again!',
        ];
        $updateModules[] = $response;
        return;
    }

    return $response;

}

function UpdatePageNameContent($pageid, $name, $content)
{
    global $domainname, $wstoken, $updateModules;
    $courseObject = [
        'page_id' => $pageid,
        'name' => $name,
        'content' => $content,
    ];
    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=18";
    $pageData = $curl->post($serverurl, $courseObject);
    if ($pageData) {
        $response = [
            'response' => true,
            'text' => $name.' Page Contents Updated Successfully!',
        ];
        $updateModules[] = $response;
    } else {
        $response = ['response' => false,'text' => 'Failed Updating '.$name.', try again!',];
        $updateModules[] = $response;
        return;
    }
    return $response;
}

function UpdateLesson($lesson_id, $name, $course_id)
{
    global $domainname, $wstoken, $responses, $updateModules;

    $courseObject = [
        'lesson_id' => $lesson_id,
        'name' => $name,
        'course_id' => $course_id
    ];
    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=16";
    $pageData = $curl->post($serverurl, $courseObject);

    if ($pageData) {
        $response = [
            'response' => true,
            'text' => $name.' Lesson Updated Successfully!',
        ];
        $updateModules[] = $response;

    } else {
        $response = [
            'response' => false,
            'text' => 'Failed Updating the Lesson '.$name.', try again!',
        ];
        $updateModules[] = $response;
        return;
    }
    return $pageData;
}
function UpdateLessonPage($pageid, $pageContent,$name, $check)
{

    global $domainname, $wstoken, $updateModules;

    $courseObject = [
        'page_id' => $pageid,
        'content' => $pageContent,

    ];

    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=8";
    $pageData = $curl->post($serverurl, $courseObject);

    if ($pageData) {

        $response = [
            'response' => true,
            'text' => $name.' Lesson Page Updated Successfully!',
        ];
        if (!$check)
            $updateModules[] = $response;
    } else {
        $response = [
            'response' => false,
            'text' => 'Failed Updating the Lesson page '.$name.' , try again!',
        ];
        if (!$check)
            $updateModules[] = $response;
        return;
    }
    return $response;

}

function UpdateLessonPageName($id, $name, $course_id, $check)
{
    global $domainname, $wstoken, $updateModules;

    $courseObject = [
        'id' => $id,
        'name' => $name,
        'course_id' => $course_id

    ];

    $curl = new curl;
    $serverurl = $domainname . "/data_content.php?action=17";
    $pageData = $curl->post($serverurl, $courseObject);

    if ($pageData) {

        $response = [
            'response' => true,
            'text' => $name. ' Lesson Page Name Updated Successfully!',
        ];
        if (!$check)
            $updateModules[] = $response;

    } else {
        $response = [
            'response' => false,
            'text' => 'Failed Updating the Lesson page Name '.$name.', try again!',
        ];
        if (!$check)
            $updateModules[] = $response;

        return;
    }
    return $response;

}


