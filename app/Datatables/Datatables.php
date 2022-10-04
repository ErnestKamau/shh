<?php

	namespace App\Datatables;

	class Datatables{
		private $tableObject;
		private $request;
		private $columns;
		private $requiredColumns;

		public function __construct($obj, $request, $columns){
			$this->tableObject = $obj;
			$this->request = $request;
			$this->columns = $columns ?? array();
		}

		public function getRequiredColumnsNames($col){
			return $col['db'];
		}

		public function execute(){
			$request = $this->request;
			$this->requiredColumns = array_map(array($this, 'getRequiredColumnsNames'), $this->columns);

			$tableObject = clone $this->tableObject;

			$filtered = $this->filtered();

			$filteredOBJ = clone $filtered;

			$data = $this->order($filtered);

			if($request->length > -1){
				$data->offset($request->start)->limit($request->length);
			}


			return array(
				"draw"            => isset ( $request->draw ) ? intval( $request->draw ) : 0,
				"recordsTotal"    => intval( $tableObject->get()->count() ),
				"recordsFiltered" => intval( $filteredOBJ->get()->count() ),
				"data"            => $this->formatOutput( $data, $request->start )
			);
		}

		public function formatOutput($data, $start){
			$data = $data->get()->toArray();
			$response = array();

			$loop = $start;

			foreach($data as $dt){
				$row = (array) $dt;

				$loop++;

				$nRow = array("loop" => $loop);
				foreach($this->requiredColumns as $col){
					$nRow[$col] = $row[$col];
				}

				$response[] = $nRow;
			}

			return $response;
		}

		public function filtered(){
			$request = $this->request;
			$columns = $request->columns ?? array();
			$requiredColumns = $this->requiredColumns;

			$obj = $this->tableObject;
			$where = array();
			$orWhere = array();

			foreach($columns as $col){
				$col = (array) $col;
				$colName = $col['data'];

				if(in_array($colName, $requiredColumns)){
					if($col['searchable'] == true){
						$searchValue = trim( $col['search']['value'] ?? $request->search['value'] );
						if($searchValue != ""){

							if(trim($col['search']['value']) != ""){
								$where[] = "`".$colName."` = '".$searchValue."'";
							}
							else{
								$searchValue = '%'.$searchValue.'%';

								$orWhere[] = "`".$colName."` LIKE '%".$searchValue."%'";
							}
						}
					}
				}
			}

			$orWhereStr = implode(" OR ", $orWhere);

			if(count($orWhere) > 0){
				$orWhereStr = "( ".$orWhereStr." )";

				$where[] = $orWhereStr;
			}

			$whereStr = implode(" AND ", $where);

			if(count($where) > 0){
				$obj->havingRaw($whereStr);
			}

			return $obj;
		}

		public function order($object){
			$orders = $this->request->order ?? array();

			foreach($orders as $order){
				$index = intval($order['column'])-1 < 0 ? 0 : intval($order['column'])-1;

				$column = $this->requiredColumns[$index];
				$object->orderBy($column, $order['dir']);
			}

			return $object;
		}
	}
