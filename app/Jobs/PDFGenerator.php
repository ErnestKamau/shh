<?php

namespace App\Jobs;

use App\RequestEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;



class PDFGenerator implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $id;
	protected $isHTML;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($id, $isHTML)
	{
		$this->id = $id;
		$this->isHTML = $isHTML;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$id = $this->id;
		$isHTML = $this->isHTML;

		$currentE = RequestEntity::find($id);
		$swapper = false;

		$isRFQ = false;

		if($currentE->request_type == "Request for Quotation"){
			$id = RequestEntity::find($id)->parent_material_requisition;
			$swapper = true;
			$isRFQ = true;
		}

		$entity = RequestEntity::find($id);
		$arrays = getDocumentTemplates();
		$is_supply = false;
		$isInternal = false;
		// return response()->json($arrays, 200);
		// return view($arrays[$entity->request_type], compact('entity', 'isHTML'));

		$PDF = \App::make('dompdf.wrapper')->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
		$pdf = $PDF->loadView($is_supply ? $arrays[$is_supply] : $arrays[$swapper ? $currentE->request_type : $entity->request_type], compact('entity', 'isHTML', 'isInternal'));
		$pdfPath = storage_path('app/requisition-pdfs/');

		if (!file_exists($pdfPath)) {
			mkdir($pdfPath, 0755, true);
		}
		$pdf->setPaper('A4', 'portrait');
		// $pdf->setOption("isPhpEnabled", true);
		$file = $entity->request_code.'-v'.$entity->ammendment.'.pdf';
		// return $pdf->download();

		$pPath = $pdfPath.$file;

		$pdf->save($pPath);

		$entity->downloadable_link = '/storage/requisition-pdfs/'.$file;
		$entity->save();

		if($isRFQ){
			$currentE->downloadable_link = '/storage/requisition-pdfs/'.$file;
			$currentE->save();
		}

		return "Completed";
	}
}
