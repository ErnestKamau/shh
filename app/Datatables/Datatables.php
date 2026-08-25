<?php

namespace App\Datatables;

class Datatables
{
	private $tableObject;

	private $request;

	private $columns;

	private $requiredColumns;

	public function __construct($obj, $request, $columns)
	{
		$this->tableObject = $obj;
		$this->request = $request;
		$this->columns = $columns ?? [];
	}

	public function getRequiredColumnsNames($col)
	{
		return $col['db'];
	}

	public function execute()
	{
		$request = $this->request;
		$this->requiredColumns = array_map([$this, 'getRequiredColumnsNames'], $this->columns);

		$tableObject = clone $this->tableObject;

		$filtered = $this->filtered();

		$filteredOBJ = clone $filtered;

		$data = $this->order($filtered);

		if ($request->length > -1) {
			$data->offset($request->start)->limit($request->length);
		}

		return [
			'draw' => isset($request->draw) ? intval($request->draw) : 0,
			'recordsTotal' => intval($tableObject->count()),
			'recordsFiltered' => intval($filteredOBJ->count()),
			'data' => $this->formatOutput($data, $request->start),
		];
	}

	public function formatOutput($data, $start)
	{
		$data = $data->get();
		$response = [];

		$loop = $start;

		foreach ($data as $dt) {
			$row = is_object($dt) && method_exists($dt, 'getAttributes')
				? $dt->getAttributes()
				: (array) $dt;

			$loop++;

			$nRow = ['loop' => $loop];
			foreach ($this->requiredColumns as $col) {
				$nRow[$col] = $row[$col] ?? null;
			}

			$response[] = $nRow;
		}

		return $response;
	}

	public function filtered()
	{
		$request = $this->request;
		$columns = $request->columns ?? [];
		$requiredColumns = $this->requiredColumns;
		$allowedColumns = array_flip($requiredColumns);

		$obj = $this->tableObject;
		$globalSearch = trim((string) ($request->search['value'] ?? ''));
		$orColumns = [];

		foreach ($columns as $col) {
			$col = (array) $col;
			$colName = trim((string) ($col['data'] ?? '')) === ''
				? ($col['name'] ?? '')
				: $col['data'];

			if (! isset($allowedColumns[$colName])) {
				continue;
			}

			$searchable = $col['searchable'] ?? false;
			$isSearchable = $searchable === true
				|| $searchable === 1
				|| $searchable === '1'
				|| $searchable === 'true';

			if (! $isSearchable) {
				continue;
			}

			$columnSearch = trim((string) ($col['search']['value'] ?? ''));

			if ($columnSearch !== '') {
				$obj->where($colName, '=', $columnSearch);
				continue;
			}

			if ($globalSearch !== '') {
				$orColumns[] = $colName;
			}
		}

		if ($globalSearch !== '' && count($orColumns) > 0) {
			$obj->where(function ($query) use ($orColumns, $globalSearch) {
				foreach ($orColumns as $index => $colName) {
					$method = $index === 0 ? 'where' : 'orWhere';
					$query->{$method}($colName, 'like', '%'.$globalSearch.'%');
				}
			});
		}

		return $obj;
	}

	public function order($object)
	{
		$orders = $this->request->order ?? [];
		$requestColumns = $this->request->columns ?? [];

		foreach ($orders as $order) {
			$index = intval($order['column'] ?? 0);
			$requestCol = (array) ($requestColumns[$index] ?? []);
			$colName = trim((string) ($requestCol['name'] ?? ''));
			if ($colName === '') {
				$colName = trim((string) ($requestCol['data'] ?? ''));
			}

			if ($colName === '' || ! in_array($colName, $this->requiredColumns, true)) {
				continue;
			}

			$dir = strtolower((string) ($order['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
			$object->orderBy($colName, $dir);
		}

		return $object;
	}
}
