<?php

namespace App\Http\Controllers\WorkOrder;

use App\Topology;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TopologyController extends Controller
{
	function __construct()
	{
		$this->middleware('auth');
	}

	public function index(){
		$topologies = Topology::leftJoin('topologies as t', 't.parent', 'topologies.id')
			->selectRaw('topologies.id, topologies.name, topologies.level, topologies.parent, count(t.id) as cNo')
			->groupBy('topologies.id', 'topologies.name', 'topologies.level', 'topologies.parent')->get();

		return view('layouts.workorder.topology.index', compact('topologies'));
	}

	public function getTopologies($parent=0){
		$topologies = Topology::leftJoin('topologies as t', 't.parent', 'topologies.id')
			->selectRaw('topologies.id, topologies.name, topologies.level, topologies.parent, count(t.id) as cNo')->where('topologies.parent', $parent)
			->groupBy('topologies.id', 'topologies.name', 'topologies.level', 'topologies.parent')->get();

		return json_encode($topologies);
	}

	public function add(Request $request, $parent=0){
		$topology = new Topology;
		$topology->name = $request->name;
		if($parent > 0){
			$pTopology = Topology::find($parent);
			$topology->parent = $pTopology->id;
			$topology->level = $pTopology->level + 1;
		}

		$topology->save();

		return redirect()->back()->with('success', 'Topology added successfully.');
	}

	public function edit(Request $request, $id){
		$topology = Topology::find($id);
		$topology->name = $request->name;
		if($request->has('parent')){
			$pTopology = Topology::find($request->parent);
			$topology->parent = $pTopology->id;
			$topology->level = $pTopology->level + 1;
		}
		$topology->save();

		return redirect()->back()->with('success', 'Topology edited successfully.');
	}

	public function remove(Request $request, $id){
		$topology = Topology::find($id);
		$hasChildren = Topology::where('parent', $id)->get()->count();

		if($hasChildren > 0){
			return redirect()->back()->with('error', 'Can not remove topology with children. Delete children topology items first.');
		}

		$topology->delete();

		return redirect()->back()->with('success', 'Topology edited successfully.');
	}
}
