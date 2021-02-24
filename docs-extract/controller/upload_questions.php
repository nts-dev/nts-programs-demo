<?php

header("Access-Control-Allow-Origin: *");
include 'config.php';
require_once '../vendor/autoload.php';

define('IMPORTZIP', 1);
define('IMPORTURL', 2);
define('SHORT_ANSWER', 1);
define('TRUE_FALSE', 2);
define('MULTICHOICE', 3);
define('NUMERICAL', 8);
define('ESSAY', 10);
define('MATCHING', 5);

$qtype = 0;
$documentId = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);
$pageId = filter_input(INPUT_GET, 'page_id', FILTER_SANITIZE_NUMBER_INT) ?: NULL;

$headings = array();
$upperCss = "";
$lowerCss = "";
$docName = "";
$sortId = 0;
$parent_id = 0;
$parent_id1 = 0;
$parent_id2 = 0;

$updatekey = -1;
$insertRecords = [];
$firstDelimiters = '';
$filename = "";
$counter_l1 = 0;
$counter_l2 = 0;
$counter_l3 = 0;
$counter_l4 = 0;
$qid = 0;


$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);

switch ($action) {

    case IMPORTZIP:
        if ($_FILES["file"]["name"]) {
            $filename = $_FILES["file"]["name"];
            $source = $_FILES["file"]["tmp_name"];
            $type = $_FILES["file"]["type"];

            $name = explode(".", $filename);
            $docName = $name[0];

            $accepted_types = array('application/zip', 'application/x-zip-compressed',
                'multipart/x-zip', 'application/x-compressed');
            foreach ($accepted_types as $mime_type) {
                if ($mime_type == $type) {
                    $okay = true;
                    break;
                }
            }

            $continue = strtolower($name[1]) == 'zip' ? true : false;
            if (!$continue) {
                $myMsg = "Please upload a valid .zip file.";
            }

            /* PHP current path */
            $path_html = dirname(__FILE__) . '/';
            $filenoext = basename($filename, '.zip');
            $filenoext = basename($filenoext, '.ZIP');
            $path = str_replace("Google_docs_extract\controller", "Google_docs", $path_html);
            $myDir = $path . $filenoext; // target directory
            $myFile = $path . $filename; // target zip file

            if (move_uploaded_file($source, $myFile)) {
                $zip = new ZipArchive();

                $x = $zip->open($myFile); // open the zip file to extract
                if ($x === true) {
                    $zip->extractTo($myDir); // place in the directory with same name

                    readGoogleDocHtml($myDir, $filenoext);

                    $zip->close();
                    unlink($myFile);
                }
                $myMsg = "Your .zip file uploaded and unziped.";
                $updateMsg = "Course Updated.";

                print_r("{state: true, name:'" . str_replace("'", "\\'", $filename) . "', extra: {info: '$myMsg '}}");
            } else {
                $myMsg = "There was a problem with the upload.";
                header("Content-Type: text/json");
                print_r("{state: false, name:" . $filename . "', extra: {info: '$myMsg '}}");
            }
        }
        break;

    case IMPORTURL:

        $fileId = filter_input(INPUT_GET, 'url');
        $url = $fileId;

        $fileId = explode("d/", $fileId);

        $fileId = $fileId[1];
        $fileId = explode("/", $fileId);
        $fileId = $fileId[0];

        try {
            $client = new Google_Client();
            $client->setAuthConfig('extract_credential.json');
            $client->addScope(Google_Service_Docs::DOCUMENTS);
            $client->addScope(Google_Service_Drive::DRIVE);

            $docService = new Google_Service_Docs($client);
            $doc = $docService->documents->get($fileId);
            $docName = $doc->getTitle();

            $driveService = new Google_Service_Drive($client);
            $response = $driveService->files->export($fileId, 'text/html', array(
                'alt' => 'media'));
            $content = $response->getBody()->getContents();

            if ($content) {

                readGoogleDocUrl($content);

                echo json_encode(array('response' => true));
            }
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
        break;
    default:
        break;
}

function readGoogleDocUrl($content) {
    readUrlHeaders($content);
}

function readUrlHeaders($contents) {

    global $headings;
    // echo $contents;
    $heading_arrays = array('h1,/h1', 'h2,/h2', 'h3,/h3', 'h4,/h4', 'h5,/h5', 'h6,/h6');

    foreach ($heading_arrays as $heading_array) {

        $heading = getUrlHeadings($contents, $heading_array);
        //$headings[] = $heading;
    }
    sort($headings, SORT_NATURAL | SORT_FLAG_CASE);

    readContents($headings, $contents);
}

function getUrlHeadings($str, $delimiters) {
    global $headings;

    $startEndDelimiters = explode(",", $delimiters);
    $startDelimiter = '<' . $startEndDelimiters[0];

    $endDelimiter = '<' . $startEndDelimiters[1] . '>';
    $startDelimiterLength = strlen($startDelimiter);
    $endDelimiterLength = strlen($endDelimiter);
    $startFrom = $contentStart = $contentEnd = 0;

    while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {

        $contentStart += $startDelimiterLength;
        $contentEnd = strpos($str, $endDelimiter, $contentStart);
        if (false === $contentEnd) {
            break;
        }
        if ($startDelimiter == '<h1') {
            $headings[] = $contentStart . '<=><h1' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h1>';
        }

        if ($startDelimiter == '<h2') {
            $headings[] = $contentStart . '<=><h2' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h2>';
        }

        if ($startDelimiter == '<h3') {
            $headings[] = $contentStart . '<=><h3' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h3>';
        }

        if ($startDelimiter == '<h4') {
            $headings[] = $contentStart . '<=><h4' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h4>';
        }

        if ($startDelimiter == '<h5') {
            $headings[] = $contentStart . '<=><h5' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h5>';
        }

        if ($startDelimiter == '<h6') {
            $headings[] = $contentStart . '<=><h6' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h6>';
        }
//        if ($startDelimiter == '<q') {
//            $headings[] = $contentStart . '<=><h4' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h6>';
//        }

        $startFrom = $contentEnd + $endDelimiterLength;
    }

    // return $contents;
}

function readGoogleDocHtml($path, $docFolder) {

    $files = glob($path . '/*html');
    if ($files) {

        $contents = file_get_contents($files[0]);
        $content = str_replace("images/image", "https://bo.nts.nl/Google_docs/" . $docFolder . "/images/image", $contents);
        readHeaders($content);
    }
}

function readHeaders($contents) {
    $heading_arrays = array('<h1,</h1>', '<h2,</h2>', '<h3,</h3>', '<h4,</h4>', '<h5,</h5>', '<h6,</h6>');
    global $headings;

    foreach ($heading_arrays as $heading_array) {

        getHeadings($contents, $heading_array);
    }
    sort($headings, SORT_NATURAL | SORT_FLAG_CASE);

    readContents($headings, $contents);
//    echo '<pre>';
//    print_r($headings);
//    exit;
}

function readContents($headings, $content) {
    global $upperCss;
    global $lowerCss;
    global $firstDelimiters;


    $Delimiter1s = $headings[0];

    $startDelimiters1s = explode("<h1", $Delimiter1s);

    $firstDelimiters = "<h1" . $startDelimiters1s[1];



    $bodyContent = array();

    $arraySize = count($headings);
    $count = 0;
    while ($count < $arraySize - 1) {


        $Delimiter1 = $headings[$count];
        $startDelimiters1 = explode("<=>", $Delimiter1);
        $firstDelimiter = $startDelimiters1[1];


        $Delimiter2 = $headings[$count + 1];
        $startDelimiters2 = explode("<=>", $Delimiter2);
        $secondDelimiter = $startDelimiters2[1];


        $bodyContent[$firstDelimiter] = getContents($content, $firstDelimiter, $secondDelimiter);
        $count++;
    }

    $Delimiter3 = $headings[$arraySize - 1];

    $startDelimiters3 = explode("<=>", $Delimiter3);
    $firstDelimiter3 = $startDelimiters3[1];

    $lastString = explode($firstDelimiter3, $content);
    $lastString2 = explode('</body>', $lastString[1]);
    $bodyContent[$firstDelimiter3] = $lastString2[0];



    getHeadingsWithContents($bodyContent);
}

//To archive table


function getHeadingsWithContents($bodyContent) {

    try {


        global $documentId;


        $size = count($bodyContent) - 1;
        $count = 0;


        foreach ($bodyContent as $key => $contents) {


            if ($count < $size) {
                list($chapter_id, $chapter_name) = getHeadlineInformations(strip_tags($key));

                $contentPerChapter = $contents[0];


                tableOfContents($key, $chapter_id, $chapter_name, $contentPerChapter, $documentId);
            } else {
                $lastkey_data = array_keys($bodyContent);
                $lastkey = end($lastkey_data);

                $last_content = end($bodyContent);


                list($last_chapter_id, $last_chapter_name) = getHeadlineInformations(strip_tags($lastkey));
                $last_contentPerChapter = $last_content;
                tableOfContents($lastkey, $last_chapter_id, $last_chapter_name, $last_contentPerChapter, $documentId);
            }
            $count++;
        }
    } catch (Exception $e) {

        echo "Problem!";
    }
}

function tableOfContents($key, $chapter_id, $chapter_name, $contentPerChapter, $documentId) {

    global $qid;

    $dateTime = date("d.m.Y") . " " . date("h:i:sa");

    $chapter1 = str_replace('nbsp;', '', $chapter_name);
    $chapter = str_replace('&', '', $chapter1);

    $chapter_num = str_replace('nbsp', '', $chapter_id);
    $chapter_nums = str_replace('&', '', $chapter_num);



    if ($chapter != "" || trim($chapter_nums)) {

        if (strpos($key, '</h5>') !== false) {

            $qid = checkQuestion($chapter, $documentId, $chapter_nums, $contentPerChapter);
        }
        if (strpos($key, '</h6>') !== false) {

            checkResponse($contentPerChapter, $qid);
        }
    }
}

function checkQuestion($chapter, $id, $chapter_nums, $contentPerChapter) {

    global $qtype;
    if (strpos($chapter, 'multi') !== false) {
        $qtype = 3;
    }
    if (strpos($chapter, 'essay') !== false) {
        $qtype = 10;
    }
    if (strpos($chapter, 'matching') !== false) {
        $qtype = 5;
    }
    if (strpos($chapter, 'numerical') !== false) {
        $qtype = 8;
    }
    if (strpos($chapter, 'short') !== false) {
        $qtype = 1;
    }
    if (strpos($chapter, 'true/false') !== false) {
        $qtype = 2;
    }
    if ($qtype > 0) {
//        echo $chapter . "<br>";
        $ids = addQuestions($id, $chapter_nums, $chapter, $contentPerChapter, $qtype);
    }
    return $ids;
}

function checkResponse($contentPerChapter, $qid) {


    readHeadings($contentPerChapter, $qid);
}

function readHeadings($contents, $question_id) {

    global $dbc;

    $document = new DOMDocument();

    $document->loadHTML($contents);

    $tags = array('p');
    $texts = array();

    foreach ($tags as $tag) {

        $elementList = $document->getElementsByTagName($tag);
        foreach ($elementList as $element) {

            if ($element->textContent) {

                $textArray = explode("(", $element->textContent);
                $text = $textArray[1];
                $response = str_replace(")", "", $text);

                $score = $text ? 1 : 0;
                $responseText = $response ? 'Correct' : "Wrong";
                $answerText = mysqli_real_escape_string($dbc, $textArray[0]);

                $obj = (object) [
                            'score' => $score,
                            'text' => $answerText,
                            'response' => $responseText
                ];

                insertAnswer($question_id, $obj);
            }
        }
    }
}

function insertAnswer($question_id, $obj = null) {

    global $dbc;
    global $qtype;

    $answers = array();

    switch ($qtype) {

        case ESSAY:
            $answers[] = "($question_id,null,1,null)";
            break;

        case SHORT_ANSWER:
        case NUMERICAL:

            $answers[] = "($question_id,'$obj->text',$obj->score,'Correct')";
            $answers[] = "($question_id,'@#wronganswer#@',0,'Wrong')";

            break;

        case TRUE_FALSE:
        case MULTICHOICE:

            $answers[] = "($question_id,'$obj->text',$obj->score,'$obj->response')";
            break;

        case MATCHING:
            break;
    }


    if (count($answers) > 0) {
        $query_insert_toc = "INSERT INTO project_course_choices (question_id,text,score,response) VALUES " . implode(",", $answers);

        mysqli_query($dbc, $query_insert_toc) or die(mysqli_error($dbc));
    }
}

function addQuestions($id, $chapter_nums, $chapter, $contentPerChapter, $qtype) {

    global $dbc;
    global $pageId;

    $chapterText = $chapter_nums . $chapter;
    $title = explode("(", $chapterText)[0];

    $select = "SELECT * FROM project_course_question WHERE course_id = $id AND title='" . $title . "'";
    $result = mysqli_query($dbc, $select) or die(mysqli_error($dbc));
    $numRows = mysqli_num_rows($result);
    if ($numRows > 0) {
        $row = mysqli_fetch_array($result);

        return $row['id'];
    }

    $query_insert_toc = "INSERT INTO project_course_question (course_id,title,text,type)
VALUES (" . $id . ",'" . $title . "','" . mysqli_real_escape_string($dbc, $contentPerChapter) . "'," . $qtype . ")";

    $insertResult = mysqli_query($dbc, $query_insert_toc) or die(mysqli_error($dbc));

    if ($insertResult) {

        $question_id = mysqli_insert_id($dbc);
        if ($qtype == ESSAY) {
            insertAnswer($question_id);
        }

        if ($pageId) {

            $insertPage = "INSERT INTO project_course_question_to_page (`question_id`,`page_id`, `sort_id`) SELECT " . $question_id . "," . $pageId . ",IF((MAX(sort_id)>0),MAX(sort_id)+1,1)sort_id FROM project_course_question_to_page WHERE page_id = " . $pageId;

            mysqli_query($dbc, $insertPage) or die(mysqli_error($dbc));
        }

        return $question_id;
    }

    return 0;
}

function getHeadlineInformations($string) {
    $position = 0;
    $chapter_id = "";
    while (isValid_(substr($string, $position, 1))) {
        $chapter_id .= substr($string, $position++, 1);
    }

    return array($chapter_id, substr($string, $position));
}

//function to validate string
function isValid_($string) {
    return is_numeric($string) || ctype_punct($string);
}

// Reading the headings of the file
function getContents($str, $startDelimiter, $endDelimiter) {
    $contents = array();

    $startDelimiterLength = strlen($startDelimiter);
    $endDelimiterLength = strlen($endDelimiter);
    $startFrom = $contentStart = $contentEnd = 0;
    while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
        $contentStart += $startDelimiterLength;
        $contentEnd = strpos($str, $endDelimiter, $contentStart);
        if (false === $contentEnd) {
            break;
        }
        $contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
        $startFrom = $contentEnd + $endDelimiterLength;
    }
//     echo '<pre>';
//   print_r($contents);
//    // exit;

    return $contents;
}

//read Heading with css
function getHeadings($str, $delimiters) {

    global $headings;

    $startEndDelimiters = explode(",", $delimiters);
    $startDelimiter = $startEndDelimiters[0];

    $endDelimiter = $startEndDelimiters[1];
    $startDelimiterLength = strlen($startDelimiter);
    $endDelimiterLength = strlen($endDelimiter);
    $startFrom = $contentStart = $contentEnd = 0;

    while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {

        $contentStart += $startDelimiterLength;
        $contentEnd = strpos($str, $endDelimiter, $contentStart);
        if (false === $contentEnd) {
            break;
        }

        if ($startDelimiter == '<h1') {
            $headings[] = $contentStart . '<=><h1' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h1>';
        }

        if ($startDelimiter == '<h2') {
            $headings[] = $contentStart . '<=><h2' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h2>';
        }

        if ($startDelimiter == '<h3') {
            $headings[] = $contentStart . '<=><h3' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h3>';
        }

        if ($startDelimiter == '<h4') {
            $headings[] = $contentStart . '<=><h4' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h4>';
        }

        if ($startDelimiter == '<h5') {
            $headings[] = $contentStart . '<=><h5' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h5>';
        }

        if ($startDelimiter == '<h6') {
            $headings[] = $contentStart . '<=><h6' . substr($str, $contentStart, $contentEnd - $contentStart) . '</h6>';
        }


        $startFrom = $contentEnd + $endDelimiterLength;
    }
}
