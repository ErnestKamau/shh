<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Event;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Response;
use App\User;
use App\Models\System\SystemConfiguration;
use App\ModulePreConfigs;
use App\CalendarEventsNotification;
use App\EventHistory;
use App\UserRole;

use function GuzzleHttp\json_decode;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function indexed()
    {
        $config = SystemConfiguration::where('key','view_all_events_role_id')->first();
        if(!isset($config->id)){
            return redirect()->back()->with('error','kindly add view_all_events_role_id configuration');
        }
        // $events = Event::all();
        $users = getCompanyUsers();
        $clients = CRMCustomer::where('active', 1)->get();
        return view('layouts.configuration.system.fullcalendar', compact('users', 'clients'));
    }
    public function created(Request $request)
    {
        return response()->json($request->all());
        $newEvent = new Event();
        $newEvent->title = $request->title;
        $newEvent->description = $request->description;
        $newEvent->start_date = $request->start_date;
        $newEvent->end_date = $request->end_date;
        $newEvent->start_time = $request->start_time;
        $newEvent->end_time = $request->end_time;
        $newEvent->client_id = $request->client_id;
        $newEvent->location = $request->location;
        $newEvent->responsible_id = implode(',', $request->responsible_id);
        $newEvent->status = $request->event_status;
        if (isset($request->notify_client)) {
            $client = getCrmCustomerByID($request->client_id);
            $company = getActiveCompany();
            if (isset($client->id)) {
                $body = 'Hi ' . $client->name . ' ,<br>We hereby inform you that you have a ' . $request->status . ' new calendar event that starts at <b>' . $request->start_date . '</b> and ends at <b>' . $request->end_date . '</b>.<br>Kindly prepare in advance.<br>Regards,<br>' . $company->name;
                $subject = '[' . $company->name . '] - Calendar Notification - ' . $request->title;
                $contacts = CustomerContact::where('receive_report', 1)->where('crm_customer_id', $request->id)->get();
                if (sizeof($contacts) > 0) {
                    foreach ($contacts as $c) {
                        notify_user($body, $c->email, $subject);
                    }
                } else {
                    notify_user($body, $client->email, $subject);
                }
            } else {
                return redirect()->back()->with('error', 'No client with specified ID');
            }
        } else {
            $newEvent->is_client_notify = 0;
        }

        if ($request->hasFile('attachment')) {
            
            $path = $request->attachment->path();
            $file = Storage::putFile('Event', new File($path));
            $file = explode('/', $file);
            $fname = '/storage/Event/' . urlencode(end($file));
            $newEvent->attachment = (string) $fname;
        }
        $newEvent->created_by = auth()->user()->id;
        $newEvent->save();
        $newEvent->parent_id = $newEvent->id;
        
        if (isset($request->is_routine)) {
            $newEvent->frequency = $request->frequency;
            $newEvent->is_routine = 1;
            
            

            $reset_start = $newEvent->start_date;
            $reset_end = $newEvent->end_date;
            $interval = floor(360 / intval($request->frequency));
            // return response()->json($interval);
            foreach (range(1, $interval-1) as $days) {
                $set = ' + ' . $request->frequency . ' days';
                $start_date = date('Y-m-d', strtotime($reset_start . $set));
                $end_date = date('Y-m-d', strtotime($reset_end . $set));
                $reset_start = $start_date;
                $reset_end = $end_date;


                $event = new Event();
                $event->title = $request->title;
                $event->description = $request->description;
                $event->start_date =   $reset_start;
                $event->end_date = $reset_end;
                $event->start_time =  $request->start_time;
                $event->end_time = $request->end_time;
                $event->client_id = $request->client_id;
                $event->location = $request->location;
                $event->responsible_id = implode(',', $request->responsible_id);
                $event->status = $request->event_status;
                $event->is_routine = 1;
                $event->parent_id = $newEvent->id;
                $event->frequency = $request->frequency;
                $event->save();
                if (isset($request->duration) && $request->duration != '') {

                    $loop = 0;
                    foreach ($request->duration as $duration) {
                        if ($duration != '') {
                            // return response()->json($duration,200);
                            $event->has_notification = 1;

                            $notification = new CalendarEventsNotification();
                            $notification->duration = $duration;
                            $notification->rate = $request->rate[$loop];
                            $notification->calendar_event_id = $event->id;
                            // return response()->json($notification,200);
                            $notification->save();
                        }
                        ++$loop;
                    }
                }
                $event->save();
            }
        }

        if (isset($request->duration) && $request->duration != '') {

            $loop = 0;
            foreach ($request->duration as $duration) {
                if ($duration != '') {
                    $newEvent->has_notification = 1;
                    $newEvent->save();
                    $notification = new CalendarEventsNotification();
                    $notification->duration = $duration;
                    $notification->rate = $request->rate[$loop];
                    $notification->calendar_event_id = $newEvent->id;
                    // return response()->json($notification,200);
                    $notification->save();
                }
                ++$loop;
            }
        }
        if (isset($request->notification)) {
            $companyDetails = getCompanyDetails();
            $user = getUserById($newEvent->responsible_id);
            $subject = '[' . $companyDetails['name'] . '] Calendar Notification - [' . $newEvent->title . ']';
            $body = 'Hi ' . $user->name . ', <br> We hereby inform you that you have a new task <b>[' . $newEvent->title . ']</b> scheduled <b>' . $newEvent->start_date . '</b> to <b>' . $newEvent->end_date . '</b> .<br>Kindly review this.';
            notify_user($body, $user->email, $subject);
        }
        $new = new EventHistory();
        $new->event_id = $newEvent->id;
        $new->remark = 'Create event.';
        $new->status = $request->event_status;
        $new->action_by = auth()->user()->id;
        $new->save();
        $newEvent->save();
        return redirect()->back()->with('success', 'Event added successfully!');
    }
    public function getEvents(Request $request)
    {
        $events = Event::all();
        return response()->json($events);
    }
    public function index()
    {
        $config = SystemConfiguration::where('key','view_all_events_role_id')->first();
        if(!isset($config->id)){
            return redirect()->back()->with('error','kindly add view_all_events_role_id configuration');
        }
        $user_role = UserRole::where('user_id',auth()->user()->id)->where('role_id',$config->value)->first();
        $today = getTodayDate();
        $end = date('Y-m-t',strtotime($today));
        $start = date("Y-m-01");
        $data = Event::all();
        $data2 = Event::where('status','Upcoming')->get();
        // if(isset($user_role->id)){
            
        // }else{
        //     $data= Event::where('responsible_id',auth()->user()->id)->get();
        // }
        
        
        $events = [];
        $today_date = getTodayDate();
        foreach ($data2 as $d) {
            
            if ($d->status == 'Upcoming') {
                $events[] = [
                    'allDay' => false,
                    'title' => $d->title,
                    'start' => $d->start_date . ' ' . $d->start_time,
                    'end' => $d->end_date . ' ' . $d->end_time,
                    'id' => $d->id,
                    'responsible_id' => $d->responsible_id,
                    'color' => '#2196f3',
                    'textColor' => 'white',
                    'status' => $d->status,
                    
                    
                ];
            } elseif ($d->status == 'Delayed') {
                $events[] = [
                    'allDay' => false,
                    'title' => $d->title,
                    'start' => $d->start_date . ' ' . $d->start_time,
                    'end' => $d->end_date . ' ' . $d->end_time,
                    'id' => $d->id,
                    'responsible_id' => $d->responsible_id,
                    'color' => '#e65100',
                    'textColor' => 'white',
                    'status' => $d->status,
                    
                ];
            } elseif ($d->status == 'Complete') {
                $events[] = [
                    'allDay' => false,
                    'title' => $d->title,
                    'start' => $d->start_date . ' ' . $d->start_time,
                    'end' => $d->end_date . ' ' . $d->end_time,
                    'id' => $d->id,
                    'responsible_id' => $d->responsible_id,
                    'color' => '#2e7d32',
                    'textColor' => 'white',
                    'status' => $d->status,
                    
                ];
            } elseif ($d->status == 'Cancelled') {
                $events[] = [
                    'allDay' => false,
                    'title' => $d->title,
                    'start' => $d->start_date . ' ' . $d->start_time,
                    'end' => $d->end_date . ' ' . $d->end_time,
                    'id' => $d->id,
                    'responsible_id' => $d->responsible_id,
                    'color' => '#c62828',
                    'textColor' => 'white',
                    'status' => $d->status,
                    
                ];
            }
        }
 
        // return response()->json($events, 200);
        $users = User::where('is_client', 0)->where('supplier_id', 0)->where('active',1)->where('is_support_staff',0)->get();
        $up = Event::where('status', 'Upcoming')->count();
        $c = Event::where('status', 'Complete')->count();
        $dl  = Event::where('status', 'Delayed')->count();
        $canc = Event::where('status', 'Cancelled')->count();
        $exp = Event::where('status','Expired')->count();
        $ong = Event::where('start_date',date('Y-m-d'))->count();

        $clients = CRMCustomer::where('active', 1)->get();
        // return response()->json('test');
        return view('layouts.configuration.system.fullcalendar_', compact('users', 'clients', 'up', 'c', 'dl', 'canc','events','data','exp','ong'));
    }
    

    public function getEventByUser(Request $request)
    {
        $raw_data = Event::where('responsible_id', auth()->user()->id)->get();
        $events = [];
        foreach ($raw_data as $data) {
            $events[] = [
                'id' => $data->id,
                'start' => $data->start_date,
                'end' => $data->end_date,
                'title' => $data->title,
                'allDay' => false,

            ];
        }
        return Response::json($events);
    }
    public function printUserEvents()
    {
        $user = auth()->user();
        $position = ModulePreConfigs::find($user->position);
        $today_date = getTodayDate();
        $data = Event::all();
        $events = [];
        $frequecy = [
            90 => 'Quarterly',
            7 => 'Weekly',
            1 => 'Daily',
            30 => 'Monthly',
            180 => 'Semi Annually',
            365 => 'Annually'
        ];
        foreach ($data as $dt) {
            $res_id = explode(',', $dt->responsible_id);
            if (in_array(auth()->user()->id, $res_id)) {
                if ($dt->is_routine == 1) {

                    $dt->frequency_name = $frequecy[$dt->frequency];
                }
                $res_name = [];
                foreach ($res_id as $id) {
                    $user = getUserById($id);
                    if (isset($user->id)) {
                        array_push($res_name, $user->name);
                    }
                }
                $dt->responsible_name = implode(',', $res_name);
                array_push($events, $dt);
                $res_name = [];
            }
        }
        // return response()->json($events,200);
        $company = getActiveCompany();



        return view('layouts.configuration.system.print_user_task', compact('user', 'events', 'company', 'position'));
    }
    public function editEvent(request $request)
    {
        $event = Event::find($request->event_id);
        if (!isset($event->id)) {
            return redirect()->back()->with('error', 'No Event with specified ID!');
        }
        $event->status = $request->event_status;
        $event->title = $request->title;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->end_date = $request->end_date;
        $event->start_time = $request->start_time;
        $event->description = $request->description;
        $event->responsible_id = implode(',', $request->responsible_id);
        $event->client_id = $request->client_id;
        $event->location = $request->location;

        if (isset($request->is_routine)) {
            $event->is_routine = 1;
            $event->frequency = $request->frequency;
        } else {
            $event->frequency = '';
        }
        if ($request->hasFile('attachment')) {
            $path = $request->attachment->path();
            $file = Storage::putFile('Event', new File($path));
            $file = explode('/', $file);
            $fname = '/storage/Event/' . urlencode(end($file));
            $event->attachment = (string) $fname;
        }
        $event->save();
        if (isset($request->notification_id)) {
            $loop = 0;
            foreach ($request->notification_id as $id) {
                $notification = CalendarEventsNotification::find($id);
                $notification->duration = $request->duration[$loop];
                $notification->rate = $request->rate[$loop];
                $notification->save();
                // return response()->json($notification,200);
                ++$loop;
            }
        }
        $new = new EventHistory();
        $new->event_id = $event->id;
        $new->remark = $request->remark;
        $new->status = $request->event_status;
        $new->action_by = auth()->user()->id;
        $new->save();
        return redirect()->back()->with('success', 'Event updated successfully!');
    }

    public function delete_event(Request $request){
        $event = Event::find($request->event_id);
        if(!isset($event->id)){
            return redirect()->back()->with('error','No Event with the specified ID!');
        }
        $parent = $event->parent_id;
        if(isset($request->delete_future)){
            $events = Event::where('id','>',$event->id)->where('parent_id',$event->parent_id)->get();
            // return response()->json($events);
            foreach($events as $e){
                $e->delete();
            }
        }
        $event->delete();
        // return response()->json($request->all());
        return redirect()->back()->with('success','Event deleted successfully!');
    }
    public function getEvent($id){
        $event = Event::find($id);
        $history = EventHistory::where('event_id',$event->id)->join('users','users.id','=','event_history.action_by')->selectRaw('event_history.*,users.name')->get();
        $notification = getEventNotification($event->id);
        return ['event'=>$event,'history'=>$history,'notification'=>$notification];
    }

    public function eventUpdateSchedule(){
        $expired = Event::where('end_date','<',date('Y-m-d'))->update(['status'=>'Expired']);
        return response()->json('success');
    }
}
