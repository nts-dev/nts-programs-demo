<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\Document;
use App\Event;
use App\Http\Resources\EventFormResource;
use App\Http\Resources\EventUserComboCollection;
use App\Http\Resources\EventUserResource;
use App\Http\Resources\scheduleResource;
use App\Http\Resources\ScheduleUnitResource;
use App\Project;
use App\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pId, $type, $id)
    {
        $colId = 'document_id';

        if ($type == 'chapter') {
            $colId = 'chapter_id';
        }

        $events = Event::with(['creator', 'users'])->where([['project_id', '=', $pId], ["{$colId}", '=', $id], ['parent_id', '=', 0]])->get();

        $xml = self::get_events_xml($events);

        return response()->xml($xml);
    }

    public static function get_events_xml(&$events)
    {

        $xml = '<rows>';
        foreach ($events as $event) {

            $assigned = array();
            if (count($event->users) > 0) {
                foreach ($event->users as $user) {
                    $assigned[] = $user->name;
                }
            }

            $xml .= "<row id='{$event->id}'>";
            $xml .= "<cell>" . $event->id . "</cell>";
            $xml .= "<cell><![CDATA[" . $event->details . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $event->creator->name . "]]></cell>";
            $xml .= "<cell><![CDATA[" . implode(", ", $assigned) . "]]></cell>";
            $xml .= "<cell>" . (string)$event->start_date . "</cell>";
            $xml .= "<cell>" . (string)$event->end_date . "</cell>";
            $xml .= "<cell>" . $event->is_visible . "</cell>";
            $xml .= "<cell>" . $event->status . "</cell>";
            $xml .= "</row>";
        }
        $xml .= '</rows>';

        return $xml;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'project_id' => 'required'
        ]);

        if ($request->chapter_id) {
            $chapter_id = $request->chapter_id;
            $chapter = Chapter::where('id', $request->chapter_id)->first();
            $document_id = $chapter->document_id;
            $doc_name = $chapter->title;
        }

        if ($request->document_id) {
            $doc_name = Document::where('id', $request->document_id)->first()->title;
            $document_id = $request->document_id;
            $chapter_id = 0;

        }

        $project_name = Project::where('id', $request->project_id)->first()->title;
        $details = Project::generateProjectId($request->project_id) . ' | ' . $project_name . ' | ' . $doc_name;

        $user_id = 1; //Auth::id();

        $event = new Event(array(
            'title' => $details,
            'details' => $details,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now(),
            'project_id' => $request->project_id,
            'document_id' => $document_id,
            'chapter_id' => $chapter_id,
            'user_id' => $user_id
        ));
        $event->save();
        $event->users()->attach($user_id);

        $response = Response::json([
            'success' => true,
            'message' => 'The event has been created succesfully',
            'id' => $event->id
//            'data' => new EventResource($event),
        ]);

        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $event = collect(new EventFormResource(Event::find($id)));

        $event->toArray();

        return response()->xml($event, $status = 200, [], $xmlRoot = 'data');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'begin_time' => 'required',
            'end_time' => 'required',
        ]);

        $start_date = Carbon::parse($request->start_date . " " . $request->begin_time);
        $end_date = Carbon::parse($request->end_date . " " . $request->end_time);

        $event = Event::find($id);
        $event->title = $request->title;
        $event->details = $request->details;
        $event->start_date = $start_date;
        $event->end_date = $end_date;
        $event->is_variable = $request->is_variable;
        $event->frequency = $request->frequency;
        $event->comments = $request->comments;

        $event->save();

        $days = array();
        foreach ($request->days_select as $key => $value) {
            if ($value == '1')
                $days[] = $key;
        }

        $event->days()->sync($days);

        $response = Response::json([
            'success' => true,
            'message' => 'The event has been updated.',
        ]);

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {

            $response = Response::json([
                'error' => [
                    'message' => 'The event cannot be found.'
                ]
            ], 404);

            return $response;
        }

        Event::destroy($id);

        $response = Response::json([
            'success' => true,
            'message' => 'The event has been deleted.'
        ]);

        return $response;
    }

    public function getUserList()
    {

        $users = new EventUserComboCollection(User::all());
        $response = Response::json($users, 200);

        return $response;

    }

    public function getAssignedUsers($event_id)
    {
        /*
        $users = DB::table('users')
            ->leftJoin('event_user', function ($join) use ($event_id) {
                $join->on('users.id', '=', 'event_user.user_id')
                    ->where('event_user.event_id', '=', $event_id);
            })
            ->select('users.id', 'users.name', 'event_user.id as checked')
            ->get();

        $users = new UserEventComboCollection($users);
        */
        $event = Event::whereId($event_id)->firstOrFail();
        $users = EventUserResource::collection($event->users);


        $response = Response::json($users, 200);
        return $response;

    }

    public function getAssignedDays($event_id)
    {
        $event = Event::whereId($event_id)->firstOrFail();
        $days = $event->days->pluck('id')->toArray();
        $response = Response::json($days, 200);
        return $response;

    }

    public function attachUser(Request $request)
    {
        $event = Event::find($request->event_id);

        if ($request->get('new_value') == '1') {
            $event->users()->attach($request->get('user_id'));
        }

        if ($request->get('new_value') == '0') {
            $event->users()->detach($request->get('user_id'));
        }

        $response = Response::json([
            'success' => true,
            'message' => 'Successful Updated'
        ]);

        return $response;
    }


    public function generateEvents($event_id)
    {

        $event = Event::with(['days', 'users'])->whereId($event_id)->firstOrFail();

//        $start_date = Carbon::createFromFormat('Y-m-d', $event->start_date)->toDateTimeString();

//        $start_date = $event->start_date->toDateString();


//        $response = Response::json($days, 200);
//        return $response;
//
//        exit;

        $start_date = $event->start_date->toDateString();
        $begin_time = $event->start_date->toTimeString();
        $end_date = $event->end_date->toDateString();
        $end_time = $event->end_date->toTimeString();

        $events = array();
        $typ = 'day';
        switch ($event->frequency) {
            //switch days days
            case 1:
                $interval = 7;
                break;
            case 2:
                $interval = 14;
                break;
            case 3:
                $interval = 1;
                $typ = "month";
                return $this->createInt($event, $interval, $typ, $end_time);
                exit();
                break;
            case 4:
                $interval = 84;
                break;
            case 5:
                $interval = 6;
                $typ = "month";
                return $this->createInt($event, $interval, $typ, $end_time);
                exit();
                break;
            case 6:
                $interval = 366;
                /* $interval = 1;
                  $typ = "year";
                  createInt($max_pl, $s1, $s2, $interval, $typ, $endtime, $dbString, $document_id, $holdays);
                  exit(); */
                break;
            case 7:
                $interval = 28;
                break;
            case 8:
                $interval = 2;
                $typ = "month";
                return $this->createInt($event, $interval, $typ, $end_time);
                exit();
                break;
            case 9:
                $interval = 56;
                break;
            case 10:
                $interval = 21;
                break;
            default :
                $interval = 365;
                break; //handle date infinite
        }

        $day_number = date('N', strtotime($event->start_date));

        $days = $event->users->pluck('id')->toArray();
        foreach ($days as $key => $day) {

            $startDate = $event->start_date;
            if ($day_number != $day) {
                $z = $day_number - $day;
                $w = 7;
                $y = $w - $z;
                $y = str_replace("-", "", $y);

                if ($z < 0) {
                    $z = str_replace("-", "", $z);
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $z . " day"));
                } else {
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . $y . " day"));
                }
            }

            while (strtotime($startDate) < strtotime($end_date)) {

//                $s2 = DateTime::createFromFormat('D M d Y H:i:s e+', $request->end_date);
//                $start = Carbon::createFromFormat('Y-m-d', $startDate)->toDateString();
//                $s2 = $s2->format('Y-m-d');
//                $end = Carbon::parse($s2 . " " . $request->end_time);

                $tskend = new DateTime($startDate);
                $tskend = $tskend->format('Y-m-d');
                $tskend = $tskend . " " . $end_time;

                $new_event = new Event(array(
                        'title' => $event->title,
                        'start_date' => $startDate,
                        'end_date' => $tskend,
                        'project_id' => $event->project_id,
                        'document_id' => $event->document_id,
                        'chapter_id' => $event->chapter_id,
                        'user_id' => $event->user_id,
                        'details' => $event->details,
                        'parent_id' => $event->id,
                        'status' => $event->status,
                    )
                );
                $new_event->save();
                $new_event->users()->attach($event->users);

                $events[] = $new_event;

                $startDate = date('Y-m-d H:i:s', strtotime($startDate . " + " . $interval . $typ));

            }
        }

        $event->is_visible = 0;
        $event->save();

        $response = Response::json($events, 200);
        return $response;
    }

    public function createInt($event, $interval, $typ, $endtime)
    {

        $s = $event->start_date;
        $s2 = $event->start_date;
        $s = date('Y-m-d H:i:s', strtotime($event->start_date . " - " . $interval . $typ));
        $typ_dy = "day";
        //if no day is checked get the current day on the date field
        $dbString = $event->users->pluck('id')->toArray();

        if (empty($dbString)) {
            unset($dbString);
            $dbString[] = date('N', strtotime($event->start_date));
        }

        //if($typ == 'month' && $interval != 2){$startDate = date('Y-m-d H:i:s', strtotime($startDate . " - ".$interval.$typ));}
        $endDate = date('Y-m-d H:i:s', strtotime($event->end_date . " - " . $interval . $typ));
        $p = $event->start_date;
        $day_number = date('N', strtotime($p));

        $events = array();
        foreach ($dbString as $key => $value) {

            if ($event->is_variable == 0) {
                if ($day_number != $value) {
                    $z = $day_number - $value;
                    $w = 7;
                    $y = $w - $z;
                    $y = str_replace("-", "", $y);
                    if ($z < 0) {
                        $z = str_replace("-", "", $z);
                        $startDate = date('Y-m-d H:i:s', strtotime($event->start_date . $z . " day"));
                    } else {
                        $startDate = date('Y-m-d H:i:s', strtotime($event->start_date . $y . " day"));
                    }
                }
            }

            while (strtotime($startDate) < strtotime($endDate)) {

                $p = $startDate;
                $day_number = date('N', strtotime($p));
                if ($event->is_variable == 0) {
                    $wk_bal = $day_number - $value;
                    $startDate = date('Y-m-d H:i:s', strtotime($startDate . "-" . $wk_bal . $typ_dy));
                }
                $day_number2 = date('N', strtotime($startDate));

                if ($typ != 'year') {
                    if (in_array(6, $dbString)) {
                        if ($day_number == 6 && $event->variable == 1) {
                            $startDate = date('Y-m-d H:i:s', strtotime($startDate . " -1 day"));
                            $unselectedDays[] = $value;
                        }
                    }
                    if (in_array(7, $dbString)) {
                        if ($day_number == 7 && $event->variable == 1) {
                            $startDate = date('Y-m-d H:i:s', strtotime($startDate . " +1 day"));
                            $unselectedDays[] = $value;
                        }
                    }
                }
                //get deadline
                $sysDate = date('d', strtotime($startDate));
                $orgDate = date('d', strtotime($s2));
                $evtDate = $startDate;

                if (str_replace("-", "", date('d', strtotime($evtDate)) - date('d', strtotime($s2))) > 0) {

                    $eDay = date('d', strtotime($s2));
                    $eMonth = date('m', strtotime($evtDate));
                    $eYear = date('Y', strtotime($evtDate));
                    $time = date('H:i:s', strtotime($evtDate));

                    $setDayNUmber = date('N', strtotime($eYear . "-" . $eMonth . "-" . $eDay . " " . $time));
                    $sysDayNumber = date('N', strtotime($evtDate));

                    $dayDifference = $setDayNUmber - $sysDayNumber;
                    if ($dayDifference < 0) {
                        $days = 7 + $dayDifference;
                    } else {
                        $days = $dayDifference;
                    }
                    $evtDate = date('Y-m-d H:i:s', strtotime($eYear . "-" . $eMonth . "-" . $eDay . " " . $time . "-" . $days . " day"));
                }
                //check for national holidays ~ jump a week
                /*
                $checkHolidayDate = new DateTime($evtDate);
                $checkHolidayDate = $checkHolidayDate->format('Y-m-d');
                if (in_array($checkHolidayDate, $holdays)) {
                    $evtDateH = date('Y-m-d H:i:s', strtotime($evtDate . " -1 week"));
                } else {
                    $evtDateH = $evtDate;
                }
                */
                $tskend = new DateTime($evtDate);
                $tskend = $tskend->format('Y-m-d');
                $tskend = $tskend . " " . $endtime;

                $new_event = new Event(array(
                        'title' => $event->title,
                        'start_date' => $evtDate,
                        'end_date' => $tskend,
                        'project_id' => $event->project_id,
                        'document_id' => $event->document_id,
                        'chapter_id' => $event->chapter_id,
                        'user_id' => $event->user_id,
                        'details' => $event->details,
                        'parent_id' => $event->id,
                        'status' => $event->status,
                    )
                );

                if (strtotime($startDate) >= strtotime($s2)) {

                    $new_event->save();
                    $new_event->users()->attach($event->users);

                    $events[] = $new_event;

                }

                $startDate = date('Y-m-d H:i:s', strtotime($startDate . " + " . $interval . $typ));
            }
        }

        $event->is_visible = 0;
        $event->save();
        $response = Response::json($events, 200);
        return $response;
    }

    public static function getChildEvents($event_id)
    {
        $events = Event::with(['creator', 'users'])->where('parent_id', '=', $event_id)->orderBy('start_date')->get();

        $xml = self::get_events_xml($events);

        return response()->xml($xml);

    }

    public function fetchScheduleEvents()
    {
        $events = DB::table('events')
            ->join('event_user', 'events.id', '=', 'event_user.event_id')
            ->select('event_user.id', 'event_user.user_id', 'events.title', 'events.start_date', 'events.end_date', 'events.details')
            ->where('is_visible', '=', 1)
            ->get();

        $schedule = scheduleResource::collection($events);
        $response = Response::json($schedule, 200);
        return $response;
    }

    public function fetchUserScheduleEvents($id)
    {

        $events = DB::table('events')
            ->join('event_user', 'events.id', '=', 'event_user.event_id')
            ->select('event_user.id', 'event_user.user_id', 'events.title', 'events.start_date', 'events.end_date', 'events.details')
            ->where([['event_user.user_id', '=', $id], ['is_visible', '=', 1]])
            ->get();

        $schedule = scheduleResource::collection($events);
        $response = Response::json($schedule, 200);
        return $response;
    }

    public function fetchScheduleUsers()
    {
        $users = ScheduleUnitResource::collection(User::all());
        $response = Response::json($users, 200);
        return $response;
    }
}
