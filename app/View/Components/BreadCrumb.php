<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BreadCrumb extends Component
{
	/**
	 * Create a new component instance.
	 *
	 * @return void
	 */

	public $items;
	public $lastIndex;

	public function __construct($items)
	{
		$this->items = array(
			array(
				'link' => route('home'),
				'name' => 'Apps',
				'icon' => 'mdi mdi-apps text-danger'
			)
		);

		$this->items = array_merge($this->items, $items);

		$this->lastIndex = count($items)+1;
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return \Illuminate\View\View|string
	 */
	public function render()
	{
		return view('components.bread-crumb');
	}
}
