<?php

namespace App\Http\Controllers;

use App\Classes\curl;
use App\Server;
use Illuminate\Support\Facades\Http;

class CourseController extends Controller
{
    public function fetchCourses()
    {
        $servers = Server::all();


        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<tree id="0">';

        foreach ($servers as $server) {

            $xml .= "<item id='m_" . $server->id . "' text='" . $server->name . "'>";
            $xml .= "<userdata name='path'>" . $server->path . "</userdata>";
            $xml .= "<userdata name='token'>" . $server->token . "</userdata>";

            $domainname = $server->path; //paste your domain here
            $wstoken = $server->token; //here paste your enrol token
            $wsfunctionname = 'core_course_get_courses';
            $restformat = 'json';

            $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
            $curl = new curl;
            $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
            $resp = $curl->post($serverurl . $restformat);
            $courses = json_decode($resp);

            foreach ($courses as $course) {
                $xml .= "<item id='" . $server->id . '_' . $course->id . "' text='" . $course->fullname . "' />";
            }

            $xml .= '</item>';
        }

        $xml .= '</tree>';

        return response()->xml($xml);

    }

    public function fetchTopics($serverId, $courseId)
    {

        //the script receive a parent item id from GET scope as my_script.php?id=PARENT_ID
        //if parent id not sent - top level in related sample - then  set it equal to 0
        $parent = (!isset($_GET['id'])) ? 0 : $_GET['id'];

        list($domainname, $wstoken) = $this->getServerDetails($serverId);
        $restformat = 'json';
        $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';


        $xml = '<?xml version="1.0" encoding="UTF-8"?>';

        if ($parent === 0) {

            $wsfunctionname = 'core_course_get_contents';
            $params = array('courseid' => $courseId);
            $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

            $curl = new curl;
            $resp = $curl->post($serverurl . $restformat, $params);
            $topics = json_decode($resp);


            $xml .= "<rows parent='" . $parent . "'>";

            foreach ($topics as $topic) {

                $xmlkids = (isset($topic->modules) && count($topic->modules) > 0) ? '1' : '';

                $xml .= '<row id="a_' . $topic->id . '" xmlkids="' . $xmlkids . '">';
                $xml .= '<cell></cell>';
                $xml .= '<cell image="folder.gif"><![CDATA[' . $topic->name . ']]></cell>';
                $xml .= '<cell></cell>';
                $xml .= '<cell>' . $topic->section . '</cell>';
                $xml .= '</row>';
            }

            $xml .= "</rows>";

        } else {

            $variableArray = explode("_", $parent);
            $mode = $variableArray[0];
            $parent_id = $variableArray[1];
//            list($mode, $parent_id, $instance) = explode("_", $parent);

            if ($mode == 'a') {

                $wsfunctionname = 'core_course_get_contents';
                $params = array('courseid' => $courseId);
                $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

                $curl = new curl;
                $resp = $curl->post($serverurl . $restformat, $params);
                $topics = json_decode($resp);

                $topic = null;

                foreach ($topics as $item) {

                    if ($parent_id == $item->id) {
                        $topic = $item;
                        break;
                    }
                }

                $xml .= "<rows parent='" . $_GET['id'] . "'>";

                if (isset($topic->modules)) {
                    foreach ($topic->modules as $module) {
                        $xml .= '<row id="b_' . $module->id . '_' . $module->instance . '" xmlkids="' . (($module->modname == 'lesson') ? "1" : "") . '" >';
                        $xml .= '<cell></cell>';
                        $xml .= '<cell><![CDATA[' . $module->name . ']]></cell>';
                        $xml .= '<cell>' . $module->modname . '</cell>';
                        $xml .= '<cell>' . $module->instance . '</cell>';
                        $xml .= '</row>';
                    }
                }

                $xml .= "</rows>";
            }

            if ($mode == 'b') {

                $instance = $variableArray[2];
                $wsfunctionname = 'mod_lesson_get_pages';
                $params = array('lessonid' => $instance);
                $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

                $curl = new curl;
                $resp = $curl->post($serverurl . $restformat, $params);
                $pages = json_decode($resp);

                $xml .= "<rows parent='" . $_GET['id'] . "'>";

                foreach ($pages->pages as $page) {

                    $xml .= '<row id="c_' . $page->page->id . '">';
                    $xml .= '<cell></cell>';
                    $xml .= '<cell><![CDATA[' . $page->page->title . ']]></cell>';
                    $xml .= '<cell>page</cell>';
                    $xml .= '<cell>' . $page->page->id . '</cell>';
                    $xml .= '<cell>' . $page->page->prevpageid . '</cell>';
                    $xml .= '<cell>' . $page->page->nextpageid . '</cell>';
                    $xml .= '</row>';
                }

                $xml .= "</rows>";
            }
        }

        return response()->xml($xml);
    }

    public function fetchLessonPage($lessonId)
    {
        $domainname = 'https://education.nts.nl';
        $loginurl = $this->getloginurl($domainname, 'abdallah@nts.nl');
        $path = '&wantsurl=' . urlencode("$domainname/mod/lesson/edit.php?id=" . $lessonId);

        return [
            'success' => true,
            'url' => $loginurl . $path
        ];
    }

    public function fetchLessonPageContent($moduleId, $serverId, $lessonId)
    {

        list($domainname, $wstoken) = $this->getServerDetails($serverId);

        $params = array('lesson' => $lessonId, 'module' => $moduleId);
        $serverurl = $domainname . "/data_content.php?action=4";
        $curl = new curl;
        $resp = $curl->post($serverurl, $params);
        $result = json_decode($resp);

        $item = ['content' => $result->item->content];
        return ['item' => $item];
    }

    public function fetchPageContent($moduleId, $serverId, $courseId)
    {

        list($domainname, $wstoken) = $this->getServerDetails($serverId);
        $wsfunctionname = 'mod_page_get_pages_by_courses';
        $restformat = 'json';

        $params = array('courseids' => array($courseId));

        header('Content-Type: application/json');
        $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
        $curl = new curl;
        $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
        $resp = $curl->post($serverurl . $restformat, $params);
        $result = json_decode($resp);

        $item = null;
        foreach ($result->pages as $struct) {

            if ($moduleId == $struct->coursemodule) {
                $item = $struct;
                break;
            }
        }

        return ['item' => $item];
    }

    private function getServerDetails($server_id)
    {
        $server = Server::find($server_id);

        return [$server->path, $server->token];
    }

    private function getloginurl($domainname, $useremail)
    {

        $token = 'aab4bfd6d2b31675978674039befb5c9';
        $functionname = 'auth_userkey_request_login_url';

        $params = [
            'user' => [
                'email' => $useremail
            ]
        ];

        $serverurl = $domainname . '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $functionname . '&moodlewsrestformat=json';

        $curl = new curl();

        try {
            $resp = $curl->post($serverurl, $params);
            $resp = json_decode($resp);

            if ($resp && !empty($resp->loginurl)) {
                $loginurl = $resp->loginurl;
            }
        } catch (Exception $ex) {
            return false;
        }

        if (!isset($loginurl)) {
            return false;
        }

        return $loginurl;
    }
}
