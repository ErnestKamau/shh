<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExporter implements FromCollection, WithHeadings
{
	public $data;
	public $columns;

	public function __construct($data, $columns){
		$this->data = $data;
		$this->columns = $columns;
	}

	/**
	* @return \Illuminate\Support\Collection
	*/
	public function collection()
	{
		return collect($this->data);
	}

	public function headings(): array{
		return $this->columns;
	}
}
