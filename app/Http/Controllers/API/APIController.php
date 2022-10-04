<?php

namespace App\Http\Controllers\API;

use Auth;
use App\User;
use App\InventoryItem as ITEM;
use App\Event;
use App\CalendarEventsNotification;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;

class APIController extends Controller
{
	// public function __construct()
	// {
	//   $this->middleware('auth');
	// }

	public function items_available(Request $request, $item_id, $brand_id = 0)
	{
		$items = ITEM::where('inventory_sub_category_id', $item_id)->selectRaw('SUM(stock_in) as stock_in, SUM(stock_out) as stock_out, item_brand_id')
			->groupBy('item_brand_id');

		if ($brand_id > 0) {
			$items = $items->where('item_brand_id', $brand_id);
		}

		$totalItems = 0;

		foreach ($items->get() as $item) {
			$available = floatval($item->stock_in) - floatval($item->stock_out);
			$totalItems += $available;
		}

		return json_encode(["formatted" => number_format($totalItems, 2), "value" => $totalItems]);
	}
	public function send_event_notifications()
	{
		$today_date = getTodayDate();
		$company = getActiveCompany();
		$events = Event::where('start_date', '>=', $today_date)->where('has_notification', 1)->where('notification_sent', 0)->get();
		foreach ($events as $event) {
			$notifications = CalendarEventsNotification::where('calendar_event_id', $event->id)->get();
			foreach ($notifications as $n) {
				if ($n->is_sent == 0) {
					$required_date = $event->start_date . ' ' . $event->start_time;
					$required_time = strtotime($required_date);
					$reduction = ' - ' . $n->duration . ' ' . $n->rate;
					$check_date = date('Y-m-d H:i:s', strtotime($required_date . $reduction));
					$balance = strtotime($check_date) - time();
					if ($balance < 180) {
						$subject = '[' . $company->name . '] Event Notification - ' . $event->title;
						$users = explode(',', $event->responsible_id);
						foreach ($users as $user) {
							$target_user = getUserById((int) $user);

							$body = 'Hi ' . $target_user->name . ' ,<br><br>
                                        This is a notification reminder for the <b>' . $event->title . '</b> calendar event.<br><br>
                                        The event is scheduled to start ' . $required_date . '.<br>
                                        Kindly avail your self.<br><br>
                                        Regards,<br>
                                        ' . $company->name . '.';
							notify_user($body, $target_user->email, $subject);
						}
						$n->is_sent = 1;
						$n->save();
						$event->notification_sent = 0;
					}
				}
			}
			$event->save();
		}
		return response()->json('succcess', 200);
	}
}
