<?php

namespace App\Listeners;

use App\Models\Audit;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserEventSubscriber
{
	/**
	 * Handle user login events.
	 */
	public function handleUserLogin($event) {
		$loginAudit = new Audit;

		$loginAudit->auditable_id = auth()->user()->id;
		$loginAudit->auditable_type = "Event\Login";
		$loginAudit->event = "User Login";
		$loginAudit->url = request()->fullUrl();
		$loginAudit->ip_address = request()->getClientIp();
		$loginAudit->user_agent = request()->userAgent();
		$loginAudit->created_at = Carbon::now();
		$loginAudit->updated_at = Carbon::now();
		$loginAudit->user_id = auth()->user()->id;
		$loginAudit->user_type = 'App\User';

		$loginAudit->save();
	}

	/**
	 * Handle user logout events.
	 */
	public function handleUserLogout($event) {
		$logoutAudit = new Audit;

		$logoutAudit->auditable_id = auth()->user()->id;
		$logoutAudit->auditable_type = "Event\Logout";
		$logoutAudit->event      = "User Logout";
		$logoutAudit->url       = request()->fullUrl();
		$logoutAudit->ip_address = request()->getClientIp();
		$logoutAudit->user_agent = request()->userAgent();
		$logoutAudit->created_at = Carbon::now();
		$logoutAudit->updated_at = Carbon::now();
		$logoutAudit->user_id    = auth()->user()->id;
		$logoutAudit->user_type = 'App\User';

		$logoutAudit->save();
	}

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  \Illuminate\Events\Dispatcher  $events
	 */
	public function subscribe($events)
	{
		$events->listen(
			'Illuminate\Auth\Events\Login',
			'App\Listeners\UserEventSubscriber@handleUserLogin'
		);

		$events->listen(
			'Illuminate\Auth\Events\Logout',
			'App\Listeners\UserEventSubscriber@handleUserLogout'
		);
	}
}
