<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Event;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\SamplingSchedule;
use App\SampleType;
use App\AnalysisType;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Response;
use App\User;
use App\ModulePreConfigs;
use App\CalendarEventsNotification;
use App\EventHistory;

use function GuzzleHttp\json_decode;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('calendar_events', 'contract_valid_from')) {
                \Illuminate\Support\Facades\Schema::table('calendar_events', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->date('contract_valid_from')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('calendar_events', 'contract_valid_to')) {
                \Illuminate\Support\Facades\Schema::table('calendar_events', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->date('contract_valid_to')->nullable();
                });
            }
        } catch (\Exception $e) {
            // Silently catch exceptions
        }
    }
    public function indexed()
    {
        // $events = Event::all();
        $users = getCompanyUsers();
        $clients = CRMCustomer::where('active', 1)->get();
        return view('layouts.configuration.system.fullcalendar', compact('users', 'clients'));
    }
    public function created(Request $request)
    {
        if (isset($request->is_routine)) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $diffInDays = $startDate->diffInDays($endDate, false);

            if ($diffInDays < 7) {
                if ($request->frequency != 1) {
                    return redirect()->back()->with('error', 'For tasks shorter than a week, the frequency must be Daily.')->withInput();
                }
            } elseif ($diffInDays < 30) {
                if ($request->frequency != 1 && $request->frequency != 7) {
                    return redirect()->back()->with('error', 'For tasks shorter than a month, the frequency must be Daily or Weekly.')->withInput();
                }
            }
        }
        // return response()->json($request->all());
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
        $newEvent->latitude = $request->latitude;
        $newEvent->longitude = $request->longitude;
        $newEvent->logistics = $request->logistics;
        $newEvent->status = $request->event_status;
        $newEvent->contract_valid_from = $request->contract_valid_from;
        $newEvent->contract_valid_to = $request->contract_valid_to;
        if (isset($request->notify_client)) {
            $client = getCrmCustomerByID($request->client_id);
            $company = getActiveCompany();
            if (isset($client->id)) {
                $body = 'Hi ' . $client->name . ' ,<br>We hereby inform you that you have a ' . $request->status . ' new calendar event that starts at <b>' . $request->start_date . '</b> and ends at <b>' . $request->end_date . '</b>.<br>Kindly prepare in advance.<br>Regards,<br>' . $company->name;
                $subject = '[' . $company->name . '] - Calendar Notification - ' . $request->title;
                $contacts = CustomerContact::where('receive_report', 1)->where('crm_customer_id', $client->id)->get();
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
            $newEvent->save();

            app(\App\Services\Planner\RoutineOccurrenceGenerator::class)
                ->generateFromRequest($newEvent, $request);
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

        if (isset($request->is_routine)) {
            self::syncRoutineStatuses($newEvent->id);
        }

        return redirect()->back()->with('success', 'Event added successfully!');
    }
    public function getEvents(Request $request)
    {
        $events = Event::all();
        return response()->json($events);
    }
    public function dashboard()
    {
        return view('layouts.planner.dashboard');
    }

    public function index()
    {
        Event::where('status', 'Upcoming')
            ->where('start_date', '<=', date('Y-m-d'))
            ->update(['status' => 'In-progress']);

        $statusCounts = Event::selectRaw('status, COUNT(*) as count')
            ->whereIn('status', ['Upcoming', 'Complete', 'Delayed', 'Cancelled', 'Expired', 'In-progress'])
            ->where('status', '!=', 'Pending')
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->groupBy('status')
            ->pluck('count', 'status');
        $today = getTodayDate();
        $end = date('Y-m-t', strtotime($today));
        $start = date("Y-m-01");
        $data = Event::where('status', '!=', 'Pending')
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->get();
        $today_date = getTodayDate();
        $data2 = Event::whereIn('status', ['Upcoming', 'Delayed', 'In-progress'])
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->get();
        $events = [];
        
        foreach ($data2 as $d) {
            switch ($d->status) {
                case 'Upcoming':
                    $color = '#2196f3';
                    break;
                case 'Delayed':
                    $color = '#e65100';
                    break;
                case 'In-progress':
                    $color = '#0000ff';
                    break;
                default:
                    $color = '#000000'; // default color if needed
            }
            
            $events[] = [
                'allDay' => false,
                'title' => $d->title,
                'start' => $d->start_date . ' ' . $d->start_time,
                'end' => $d->end_date . ' ' . $d->end_time,
                'id' => $d->id,
                'responsible_id' => $d->responsible_id,
                'color' => $color,
                'textColor' => 'white',
                'status' => $d->status,
            ];
        }
        // return response()->json($events);

        $users = User::where('is_client', 0)->whereNull('supplier_id')->where('active', 1)->where('is_support_staff', 0)->get();
        // $up = Event::where('status', 'Upcoming')->count();
        // $c = Event::where('status', 'Complete')->count();
        // $dl = Event::where('status', 'Delayed')->count();
        // $canc = Event::where('status', 'Cancelled')->count();
        // $exp = Event::where('status', 'Expired')->count();
        $ong = Event::where('status', 'In-progress')
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->count();

        $clients = CRMCustomer::where('active', 1)->get();
        return view('layouts.planner.calendar', compact('users', 'clients', 'events', 'data','ong','statusCounts'));
    }

    public function tasks()
    {
        Event::where('status', 'Upcoming')
            ->where('start_date', '<=', date('Y-m-d'))
            ->update(['status' => 'In-progress']);

        $statusCounts = Event::selectRaw('status, COUNT(*) as count')
            ->whereIn('status', ['Upcoming', 'Complete', 'Delayed', 'Cancelled', 'Expired', 'In-progress'])
            ->where('status', '!=', 'Pending')
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->groupBy('status')
            ->pluck('count', 'status');
        $today = getTodayDate();
        $end = date('Y-m-t', strtotime($today));
        $start = date("Y-m-01");
        $data = Event::where('status', '!=', 'Pending')
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->get();
        $today_date = getTodayDate();
        $data2 = Event::whereIn('status', ['Upcoming', 'Delayed', 'In-progress'])
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->get();
        $events = [];
        
        foreach ($data2 as $d) {
            switch ($d->status) {
                case 'Upcoming':
                    $color = '#2196f3';
                    break;
                case 'Delayed':
                    $color = '#e65100';
                    break;
                case 'In-progress':
                    $color = '#0000ff';
                    break;
                default:
                    $color = '#000000'; // default color if needed
            }
            
            $events[] = [
                'allDay' => false,
                'title' => $d->title,
                'start' => $d->start_date . ' ' . $d->start_time,
                'end' => $d->end_date . ' ' . $d->end_time,
                'id' => $d->id,
                'responsible_id' => $d->responsible_id,
                'color' => $color,
                'textColor' => 'white',
                'status' => $d->status,
            ];
        }
        // return response()->json($events);

        $users = User::where('is_client', 0)->whereNull('supplier_id')->where('active', 1)->where('is_support_staff', 0)->get();
        $ong = Event::where('status', 'In-progress')
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->count();

        $clients = CRMCustomer::where('active', 1)->get();
        return view('layouts.planner.tasks', compact('users', 'clients', 'events', 'data','ong','statusCounts'));
    }


    public function getEventByUser(Request $request)
    {
        $raw_data = Event::where(function ($query) {
                $userId = (string) auth()->user()->id;
                $query->where('responsible_id', $userId)
                    ->orWhere('responsible_id', 'like', $userId.',%')
                    ->orWhere('responsible_id', 'like', '%,'.$userId.',%')
                    ->orWhere('responsible_id', 'like', '%,'.$userId);
            })
            ->where('status', '!=', 'Pending')
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->get();
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
        $data = Event::where('status', '!=', 'Pending')
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhereColumn('id', 'parent_id')
                      ->orWhere('is_routine', '!=', 1)
                      ->orWhere('status', '!=', 'Pending');
            })
            ->get();
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
                    $u = getUserById($id);
                    if (isset($u->id)) {
                        array_push($res_name, $u->name);
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
        if (isset($request->is_routine)) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $diffInDays = $startDate->diffInDays($endDate, false);

            if ($diffInDays < 7) {
                if ($request->frequency != 1) {
                    return redirect()->back()->with('error', 'For tasks shorter than a week, the frequency must be Daily.')->withInput();
                }
            } elseif ($diffInDays < 30) {
                if ($request->frequency != 1 && $request->frequency != 7) {
                    return redirect()->back()->with('error', 'For tasks shorter than a month, the frequency must be Daily or Weekly.')->withInput();
                }
            }
        }
        $event->status = $request->event_status;
        $event->title = $request->title;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->start_time = $request->start_time;
        $event->description = $request->description;
        $event->responsible_id = implode(',', $request->responsible_id);
        $event->client_id = $request->client_id;
        $event->location = $request->location;
        $event->latitude = $request->latitude;
        $event->longitude = $request->longitude;
        $event->logistics = $request->logistics;
        $event->contract_valid_from = $request->contract_valid_from;
        $event->contract_valid_to = $request->contract_valid_to;

        if ($event->id == $event->parent_id) {
            $childIds = Event::where('parent_id', $event->id)->where('id', '!=', $event->id)->pluck('id');
            CalendarEventsNotification::whereIn('calendar_event_id', $childIds)->delete();
            Event::whereIn('id', $childIds)->delete();

            if (isset($request->is_routine)) {
                $event->is_routine = 1;
                $event->frequency = $request->frequency;
                $event->save();

                app(\App\Services\Planner\RoutineOccurrenceGenerator::class)
                    ->generateFromRequest($event, $request);
            } else {
                $event->is_routine = 0;
                $event->frequency = '';
            }
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
                if ($notification) {
                    $notification->duration = $request->duration[$loop];
                    $notification->rate = $request->rate[$loop];
                    $notification->save();
                }
                ++$loop;
            }
        }
        $new = new EventHistory();
        $new->event_id = $event->id;
        $new->remark = $request->remark;
        $new->status = $request->event_status;
        $new->action_by = auth()->user()->id;
        $new->save();

        if ($event->parent_id) {
            self::syncRoutineStatuses($event->parent_id);
        }

        return redirect()->back()->with('success', 'Event updated successfully!');
    }

    public function delete_event(Request $request)
    {
        $event = Event::find($request->event_id);
        if (!isset($event->id)) {
            return redirect()->back()->with('error', 'No Event with the specified ID!');
        }
        $parent = $event->parent_id;
        if ($event->id == $event->parent_id) {
            Event::where('parent_id', $event->parent_id)->delete();
        } else {
            if (isset($request->delete_future)) {
                $events = Event::where('start_date', '>', $event->start_date)->where('parent_id', $event->parent_id)->get();
                foreach ($events as $e) {
                    $e->delete();
                }
            }
            $event->delete();
        }
        // return response()->json($request->all());
        return redirect()->back()->with('success', 'Event deleted successfully!');
    }
    public function getEvent($id)
    {
        Event::where('status', 'Upcoming')
            ->where('start_date', '<=', date('Y-m-d'))
            ->update(['status' => 'In-progress']);

        $event = Event::find($id);
        if ($event && $event->parent_id) {
            self::syncRoutineStatuses($event->parent_id);
            $event = Event::find($id);
        }
        $history = EventHistory::where('event_id', $event->id)->join('users', 'users.id', '=', 'event_history.action_by')->selectRaw('event_history.*,users.name')->get();
        $notification = getEventNotification($event->id);
        $occurrences = [];
        if (isset($event->id)) {
            $occurrences = Event::where('parent_id', $event->parent_id)
                ->whereRaw('id != parent_id')
                ->orderBy('start_date', 'asc')
                ->get();
        }
        return ['event' => $event, 'history' => $history, 'notification' => $notification, 'occurrences' => $occurrences];
    }

    public function eventUpdateSchedule()
    {
        $expired = Event::where('end_date', '<', date('Y-m-d'))->update(['status' => 'Expired']);
        Event::where('status', 'Upcoming')
            ->where('start_date', '<=', date('Y-m-d'))
            ->update(['status' => 'In-progress']);
        self::syncAllRoutines();
        return response()->json('success');
    }

    public function updateOccurrenceStatus(Request $request)
    {
        $event = Event::find($request->occurrence_id);
        if (!isset($event->id)) {
            return response()->json(['error' => 'Occurrence not found'], 404);
        }
        $old_status = $event->status;
        $event->status = $request->status;
        $event->save();

        $new = new EventHistory();
        $new->event_id = $event->id;
        $new->remark = 'Updated occurrence status from ' . $old_status . ' to ' . $request->status . ' via occurrence management.';
        $new->status = $request->status;
        $new->action_by = auth()->user()->id;
        $new->save();

        if ($event->parent_id) {
            self::syncRoutineStatuses($event->parent_id);
        }

        return response()->json(['success' => true]);
    }

    public static function syncRoutineStatuses($parentId)
    {
        $occurrences = Event::where('parent_id', $parentId)
            ->orderBy('start_date', 'asc')
            ->get();

        $terminalStatuses = ['Complete', 'Completed', 'Cancelled', 'Expired'];

        $hasActive = false;
        foreach ($occurrences as $occ) {
            if (!in_array($occ->status, $terminalStatuses) && $occ->status !== 'Pending') {
                $hasActive = true;
                break;
            }
        }

        if (!$hasActive) {
            foreach ($occurrences as $occ) {
                if ($occ->status === 'Pending') {
                    $occ->status = 'Upcoming';
                    $occ->save();

                    $history = new EventHistory();
                    $history->event_id = $occ->id;
                    $history->remark = 'Routine occurrence activated to Upcoming.';
                    $history->status = 'Upcoming';
                    $history->action_by = auth()->check() ? auth()->user()->id : ($occ->created_by ?? 1);
                    $history->save();

                    break;
                }
            }
        }

        $occurrences = Event::where('parent_id', $parentId)
            ->orderBy('start_date', 'asc')
            ->get();

        foreach ($occurrences as $occ) {
            if ($occ->status === 'Upcoming' && $occ->start_date <= date('Y-m-d')) {
                $occ->status = 'In-progress';
                $occ->save();

                $history = new EventHistory();
                $history->event_id = $occ->id;
                $history->remark = 'Event started: transitioned to In-progress.';
                $history->status = 'In-progress';
                $history->action_by = auth()->check() ? auth()->user()->id : ($occ->created_by ?? 1);
                $history->save();
            }
        }
    }

    public static function syncAllRoutines()
    {
        $parentIds = Event::where('is_routine', 1)
            ->whereRaw('id = parent_id')
            ->pluck('id');

        foreach ($parentIds as $parentId) {
            self::syncRoutineStatuses($parentId);
        }
    }

    public function scheduleSamplingIndex()
    {
        return view('layouts.planner.schedule_sampling');
    }

    public function fillSamplingFormsIndex()
    {
        $scheduleId = request()->query('schedule');
        if ($scheduleId !== null && $scheduleId !== '') {
            $scheduleExists = \App\Models\SamplingSchedule::query()
                ->visibleTo()
                ->whereKey($scheduleId)
                ->exists();
            if (! $scheduleExists) {
                return redirect()
                    ->route('system-planner.fill-sampling-forms')
                    ->with('error', 'The selected sampling schedule could not be found.');
            }
        } else {
            $scheduleId = null;
        }

        return view('layouts.planner.fill_sampling_forms', [
            'scheduleId' => $scheduleId,
        ]);
    }

    public function fillSamplingFormsFill(string $sampleType)
    {
        $exists = \App\SampleType::query()->whereKey($sampleType)->exists();
        if (! $exists) {
            abort(404, 'Sample type not found.');
        }

        $scheduleId = request()->query('schedule');
        if ($scheduleId === null || $scheduleId === '') {
            return redirect()
                ->route('system-planner.fill-sampling-forms')
                ->with('error', 'Choose a sampling schedule first. Sampling forms must be linked to a schedule.');
        }

        $scheduleExists = \App\Models\SamplingSchedule::query()
            ->visibleTo()
            ->whereKey($scheduleId)
            ->exists();
        if (! $scheduleExists) {
            return redirect()
                ->route('system-planner.fill-sampling-forms')
                ->with('error', 'The selected sampling schedule could not be found.');
        }

        return view('layouts.planner.fill_sampling_forms_fill', [
            'sampleTypeId' => $sampleType,
            'scheduleId' => $scheduleId,
        ]);
    }

    public function actualCollectionsIndex()
    {
        return view('layouts.planner.actual_collections');
    }

    public function kpiReportsIndex()
    {
        return view('layouts.planner.kpi_reports');
    }

    /**
     * Sample Collection Label for a System Planner sampling schedule.
     * Prefers a linked submitted form instance when available; otherwise uses schedule details.
     */
    public function samplingScheduleCollectionLabel(string $schedule)
    {
        $samplingSchedule = SamplingSchedule::query()
            ->visibleTo()
            ->with([
                'client',
                'samplePoint',
                'sample_type',
                'analysis_type',
                'submissionFormInstances' => fn ($query) => $query->latest(),
            ])
            ->findOrFail($schedule);

        $linkedInstance = $samplingSchedule->submissionFormInstances->first();
        if ($linkedInstance !== null) {
            return redirect()->route('submission-forms.instances.sample-collection-label', [
                'instance' => $linkedInstance->id,
                'type' => 'collection',
            ]);
        }

        $viewData = app(\App\Services\Planner\SamplingScheduleCollectionLabelService::class)
            ->viewData($samplingSchedule);

        return view('submission-forms.instances.sample-collection-label', $viewData);
    }
}

