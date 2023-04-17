<?php
class ModelToolCompute extends Model {
	private $error = array();
	private $min_population_count = 2;
	private $max_cv_count = 10;
	private $numeric = "NUMERIC";
	private $choice = "CHOICE";
	private $update_qry = "";
	private $numeric_algorithm_mean	= "MEAN";
	private $sstar_const = 1.483;
	private $phi_const = 1.5;
	private $sstar2_const = 1.134;	
	private $x = array();

	/**
	 * The list of analytes in the population. 
	 */
	private $analyte_list = array(); 
	private $ommited_analyte_list = array(); 
	private $results_present_list = array(); 

	/**
	 * The results currently being processed
	 */
	private $result_list = array();
	private $batch_results = array();
	private $batch_results_options = array();
	private $batch_list = array();
	
	/**
	 * the list that will be used to compute cv* --> all batch results
	 */
	private $total_batch_results = array();

	public function batch_result_processor(){
		$this->load->model('tool/compute');

		if (isset($this->request->get['batch_code'])) {
			$batch_code = $this->request->get['batch_code'];
		} else {
			$batch_code = '';
		}

		if (isset($this->request->get['scheme_id'])) {
			$scheme_id = $this->request->get['scheme_id'];
		} else {
			$scheme_id = '';
		}

		$json = array();

		// retrieve analyte list
		$this->log->write("Retrieving list of analytes => batch: ".$batch_code);
		$this->analyte_list =  $this->retrieveAnalyteList($batch_code);

		// retrieve results list
		$this->log->write("Retrieving results list => batch: ".$batch_code);
		$this->result_list =  $this->retrieveResultList($batch_code);

		// retrieve batch results options
		$this->batch_results_options = $this->currBatchResultOption($batch_code);

		// $this->log->write("To be processed: ". print_r($this->batch_results_options, TRUE));

		// retrieve results present list
		$this->log->write("Retrieving results present analyte list => batch: ".$batch_code);
		$this->results_present_list =  $this->resultsPresentAnalyteList($batch_code);

		// $this->log->write("got  ". sizeof($this->result_list) . " published results");
		// $this->log->write("got  ". print_r($this->analyte_list, TRUE) );

		// clear batch results
		$this->clearBatchEntries($batch_code);

		// clear update query string
		$this->update_qry = "";

		// total batch entries
		$this->total_batch_results = $this->retrieveBatchResultsSortedByBatchSortCode();

		// initialize current batch
		$this->init_batch_results($batch_code);

		$this->batch_results = array();
		// initialize current batch results correctly and without sorting keys
		// must be unsorted to correctly calculate the CV* later on
		$this->batch_results = array_filter($this->total_batch_results, function ($batch_result) use ($batch_code) {
			return ($batch_result['batch_code'] == $batch_code);
		});

		// reset zScores
		// $new_results_list = $this->resetZScores($this->result_list);

		// set new results list
		// $this->result_list = $new_results_list;

		// compute
		$this->computeBatchResults($batch_code);

		// do batch updates here after computation
		$this->executeBatchUpdates();



		// $this->log->write("batch list  " . print_r($this->batch_results, TRUE));
		// $this->log->write("batch list  " . print_r($this->total_batch_results, TRUE));

		$json[] = array(
			'type'         => $batch_code,
			'analyte_list'         => $this->analyte_list,
			'result_list'         => $this->result_list,
			'result_present_list'         => $this->results_present_list,
			'ommited_analyte_list'         => $this->ommited_analyte_list,
			'result_list size'         => sizeof($this->result_list)
		);
		
		$this->log->write("Done ... " );

		return $json;
	}

	public function init_batch_results($batch_code){
		$brd = array();
		for ($analyte_key = 0; $analyte_key < sizeof($this->analyte_list); $analyte_key++) {
			// check see if the list is ok...
			if ($this->isResultPresent($this->analyte_list[$analyte_key]['analyte_code']) || !$this->isNumeric($this->analyte_list[$analyte_key])){
				// batch result data
				$this->batch_results[] = array(
					'batch_code' => $batch_code,
					'analyte_code' => $this->analyte_list[$analyte_key]['analyte_code'],
					'average' => NULL,
					'std_deviation' => NULL,
					'cv_star' => NULL,
					'sd_star' => NULL,
					'cv' => NULL,
					'total_population_size' => NULL,
	 				'statistical_population_size' => NULL,
					'scored_population_size' => NULL,
					'min_value' => NULL,
					'max_value' => NULL
				);

				$this->log->write("adding analyte: ".$this->analyte_list[$analyte_key]['analyte_code']." batch: ".$batch_code);
			} else {
				array_push($this->ommited_analyte_list, $this->analyte_list[$analyte_key]['analyte_code']);

				$this->log->write("IGNORING: ".$this->analyte_list[$analyte_key]['analyte_code']." less than: ".$this->min_population_count." batch: ".$batch_code);
			}
			
		}
		$this->total_batch_results = array_merge($this->total_batch_results, $this->batch_results);

	}

	private function isResultPresent($analyte_code)
  {
  	if(in_array($analyte_code, array_column($this->results_present_list, 'analyte_code'))) {
			$key = array_search($analyte_code, array_column($this->results_present_list, 'analyte_code'));

			$size = $this->results_present_list[$key]['cnt'] >= $this->min_population_count ? $this->results_present_list[$key]['cnt'] : 0;

			// $this->log->write('Analyte: '.$analyte_code.'  count: '.$this->results_present_list[$key]['cnt']);

			return $size ? $size : false;
		} else {
			return false;
		}
	}
	
	private function isNumeric($analyte)
  {
  	return $analyte['statsmethod'] == $this->numeric ? true : false;
	}

	private function isNumericOption($analyte)
  {
  	return $analyte[0]['statsmethod'] == $this->numeric ? true : false;
	}

	private function isNumericAlgorithmMean($analyte)
  {
  	return $analyte['numericalgorithm'] == $this->numeric_algorithm_mean ? true : false;
	}
	
	// results present list
  public function resultsPresentAnalyteList($batch_code){
    $sql = "SELECT DISTINCT analyte_code, count(analyte_code) AS cnt FROM `" . DB_PREFIX . "results` WHERE `batch_code` = '".$this->db->escape($batch_code)."' AND is_result_present = 1 GROUP BY analyte_code";

    $query = $this->db->query($sql);

    return $query->rows;
  }

  public function retrieveAnalyteList($batch_code){
    $sql = "SELECT DISTINCT r.analyte_code, brp.analytegroup_id, brp.group_code, brp.statsmethod, brp.numericalgorithm, a.decimal_places, a.low_limit, a.high_limit FROM `" . DB_PREFIX . "results` r, `" . DB_PREFIX . "analyte` a, `" . DB_PREFIX . "analytegroup` brp  WHERE r.`batch_code` = '".$this->db->escape($batch_code)."' AND r.analyte_code = a.analyte_code AND a.analytegroup_id = brp.analytegroup_id ";
		
		/*$sql = "SELECT DISTINCT r.analyte_code, brp.analytegroup_id, brp.group_code, brp.statsmethod, brp.numericalgorithm, a.decimal_places, a.low_limit, a.high_limit FROM `" . DB_PREFIX . "results` r, `" . DB_PREFIX . "analyte` a, `" . DB_PREFIX . "batch_resultoptions` brp  WHERE r.`batch_code` = '".$this->db->escape($batch_code)."' AND brp.`batch_code` = '".$this->db->escape($batch_code)."' AND r.analyte_code = a.analyte_code AND a.analytegroup_id = brp.analytegroup_id"; */

    $query = $this->db->query($sql);

    return $query->rows;
	}

  public function retrieveBatchResultsSortedByBatchSortCode(){
    $sql = "SELECT br.* FROM `" . DB_PREFIX . "batch_results` br, `" . DB_PREFIX . "batch` b
    WHERE br.`batch_code` = b.`batch_code` order by batch_code DESC";

    $query = $this->db->query($sql);

    return $query->rows;
	}
	
  public function currBatchResultOption($batch_code){
		$sql = "SELECT * FROM `" . DB_PREFIX . "batch_resultoptions` WHERE `batch_code` = '".$this->db->escape($batch_code)."'";
		// $sql = "SELECT * FROM `" . DB_PREFIX . "batch_resultoptions` WHERE `batch_code` = '".$this->db->escape($batch_code)."' AND `analytegroup_id` = $analytegroup_id LIMIT 1";

		$query = $this->db->query($sql);
		
		// $num_rows = $query->num_rows;

    // if ($num_rows) {
		// 	return $query->rows;
		// } else {
		// 	$sql = "SELECT * FROM `" . DB_PREFIX . "analytegroup` 
		// 	WHERE `analytegroup_id` = $analytegroup_id LIMIT 1";

		// 	$query = $this->db->query($sql);

			return $query->rows;
		// }
    
  }
	
  public function getSampleReferenceValues($batch_code, $analyte_code){
    $sql = "SELECT * FROM `" . DB_PREFIX . "samplereferencevalues` 
    WHERE `sample_id` IN (SELECT sample_id from `" . DB_PREFIX . "batch` WHERE `batch_code` = '".$this->db->escape($batch_code)."' AND analyte_code = '".$this->db->escape($analyte_code)."') LIMIT 1";

    $query = $this->db->query($sql);

    return $query->row;
  }

  public function retrieveResultList($batch_code){
    $sql = "select r.*, sort_code, sp.profile_code, a.analytegroup_id from " . DB_PREFIX . "results r, " . DB_PREFIX . "analyte a, " . DB_PREFIX . "scheme_profile sp where r.analyte_code = a.analyte_code and r.profile_id = sp.scheme_profile_id and r.batch_code = '".$this->db->escape($batch_code)."' order by r.scheme_code ASC, r.batch_code ASC, r.lab_code ASC, sp.profile_code asc, sort_code asc";

    $query = $this->db->query($sql);

    return $query->rows;
  }

  public function retrieveBatchResults($batch_code, $is_numeric = 1){
    $is_numeric_string = " ";
    if ($is_numeric == 1){
      $is_numeric_string = " AND brp.statsmethod = 'NUMERIC' ";
    } elseif($is_numeric == 0){
      $is_numeric_string = " AND brp.statsmethod != 'NUMERIC' ";
    }

    $sql = "select r.*, a.sort_code, IF(brp.statsmethod = 'NUMERIC', 1, 0) AS is_numeric, '' as reference_value, brp.*, a.name AS analyte_name from " . DB_PREFIX . "batch_results r, " . DB_PREFIX . "analyte a, " . DB_PREFIX . "batch_resultoptions brp, " . DB_PREFIX . "batch b where r.batch_code = '$batch_code' AND brp.batch_code = '$batch_code' AND b.batch_code = '$batch_code' $is_numeric_string AND r.analyte_code = a.analyte_code AND a.analytegroup_id = brp.analytegroup_id order by a.sort_code asc";

    $query = $this->db->query($sql);

    return $query->rows;
	}
	
  public function clearBatchEntries($batch_code){
    $sql = "DELETE FROM " . DB_PREFIX . "batch_results WHERE batch_code = '$batch_code'";

    $this->db->query($sql);
	}
	
  public function resetZScores($resultsList){
		foreach ($resultsList as $key => $result) {
			$resultsList[$key]['z_score'] = NULL;
		}

		return $resultsList;
	}

	
  public function computeBatchResults($batch_code){
		
		for ($analyte_key = 0; $analyte_key < sizeof($this->analyte_list); $analyte_key++) {
			$analyte_code = $this->analyte_list[$analyte_key]['analyte_code'];
			$analytegroup_id = $this->analyte_list[$analyte_key]['analytegroup_id'];

			if(sizeof($this->ommited_analyte_list) == 0 || !in_array($analyte_code, $this->ommited_analyte_list) ){
				
				// $currBatchResultOption = $this->currBatchResultOption($batch_code, $this->analyte_list[$analyte_key]['analytegroup_id']);

				$currBatchResultOption = array_filter($this->batch_results_options, function ($analyte_group) use ($analytegroup_id) {
					return ($analyte_group['analytegroup_id'] == $analytegroup_id);
				});

				$curr_result_list = array_filter($this->result_list, function ($result) use ($analyte_code) {
					return ($result['analyte_code'] == $analyte_code);
				});

				$batch_result_key = array_search($analyte_code, array_column($this->batch_results, 'analyte_code'));

				// $batch_code_results = array_filter($this->total_batch_results, function ($result) use ($batch_code) {
				// 	return ($result['batch_code'] == $batch_code);
				// });

				// $batch_code_result_key = array_search($analyte_code, array_column($batch_code_results, 'analyte_code'));

				// set current population size
				$this->batch_results[$batch_result_key]['total_population_size'] = sizeof($curr_result_list) > 0  ? sizeof($curr_result_list) : 0;

				// do actual calculations
				$currBatchResultOption = array_values($currBatchResultOption);
				if ($this->isNumericOption($currBatchResultOption)) {
					// numeric calculations
					$this->numericResultModule($batch_result_key, $currBatchResultOption, $analyte_key, $curr_result_list);

				} else {
					// non numeric calculations
					$this->nonNumericResultModule($batch_result_key, $currBatchResultOption, $analyte_key, $curr_result_list);
				}

				
			} else {
				// $this->log->write("Ommited: ".$analyte_list[$analyte_key]['analyte_code']);
			}
		}

	}
	
	// numeric results  module
	private function numericResultModule($batch_result_key, $currBatchResultOption, $analyte_key, $currList) {
		$batchResult = $this->batch_results[$batch_result_key];
		$analyteList = $this->analyte_list[$analyte_key];
		// $this->log->write("Processing currBatchResultOption: ". print_r($currBatchResultOption, TRUE));
		$batch_code = $currBatchResultOption[0]['batch_code'];
		$analyte_code = $analyteList['analyte_code'];
		$decimal_places = $analyteList['decimal_places']+1;
		// initialize all variables
		$min_value = null;
		$max_value = null;
		$robust_average = null;
		$robust_std_deviation = null;
		$robust_cv = null; 
		$statisticalPopulationSize = null; 
		$cv_star = null; 
		$sd_star = null; 
		$z_results = [];
		$this->x = array();
		$this->log->write("===========================================================");
		$this->log->write("Processing batch: ". $batch_code.", Analyte: ".$analyte_code);

		$curr_result_list = array_filter($this->total_batch_results, function ($result) use ($analyte_code) {
			return ($result['analyte_code'] == $analyte_code);
		});

		$batch_key = null;
		
		foreach ($curr_result_list as $key => $value) {
			// only start processing results once we encounter the batch code;
			if ($value['batch_code'] == $batch_code)
			{
				$batch_key = $key;
			}
		}
		unset($key);
		unset($value);

		$sample_reference_values = $this->getSampleReferenceValues($batch_code, $analyte_code);

		$isNumericAlgorithmMean = $this->isNumericAlgorithmMean($analyteList);
		$diff = array();
		$xdiff = array();

		$validResults = $this->resultPresentContributesMean($currList);

		usort($validResults, function($a, $b) {
			return strcmp($a['result'], $b['result']);
		});

		if(sizeof($sample_reference_values) == 0 && !$isNumericAlgorithmMean) {
			$this->log->write("Failed analyte: ". $analyte_code);
		} else {
			// initialize all variables
			$min_value = min(array_column($validResults, 'result'));
			$max_value = max(array_column($validResults, 'result'));
			$robust_average = null;
			$robust_std_deviation = null;
			$robust_cv = null; 
			$statisticalPopulationSize = null; 
			
			if ($min_value == $max_value) {
				$robust_average = $min_value;
				$robust_std_deviation =0.0;
				$robust_cv = 0.0; 
				$statisticalPopulationSize = sizeof($currList) > 0  ? sizeof($currList) : 0;;
			} else {
				// $this->x[] = sizeof($validResults);
				
				for ($i = 0; $i < sizeof($validResults); $i++) {
					$this->x[$i] = $validResults[$i]['result'];
				}
				
				$compute_res = $this->compute($batch_code, $analyte_code, $decimal_places, $validResults, $currList);

				$robust_average = $compute_res['robust_average'];
				$robust_cv = $compute_res['robust_cv'];
				$robust_std_deviation = $compute_res['robust_std_deviation'];
				$statisticalPopulationSize = $compute_res['statisticalPopulationSize'];

			}

			//
			$validResults = $this->resultPresentScore($currList);
			$validResults = array_values($validResults);
											
			$scoredPopulationSize = sizeof($validResults);

			$batch_updates['batch_code'] = $batch_code;
			$batch_updates['analyte_code'] = $analyte_code;
			$batch_updates['min_value'] = $min_value;
			$batch_updates['max_value'] = $max_value;
			$batch_updates['robust_cv'] = $robust_cv;
			$batch_updates['robust_std_deviation'] = $robust_std_deviation;
			$batch_updates['robust_average'] = $robust_average;
			$batch_updates['statistical_population_size'] = $statisticalPopulationSize;
			$batch_updates['scored_population_size'] = $scoredPopulationSize;

			$this->total_batch_results[$batch_key]['min_value'] = $min_value;
			$this->total_batch_results[$batch_key]['max_value'] = $max_value;
			$this->total_batch_results[$batch_key]['average'] = $robust_average;
			$this->total_batch_results[$batch_key]['cv'] = $robust_cv;
			$this->total_batch_results[$batch_key]['std_deviation'] = $robust_std_deviation;
			$this->total_batch_results[$batch_key]['statistical_population_size'] = $statisticalPopulationSize;
			$this->total_batch_results[$batch_key]['scored_population_size'] = $scoredPopulationSize;

		// $this->updateBatchNumericResults($batch_updates);

		$cv_results = $this->computeCVStar($analyte_code, $batch_code);
		$curr_key = $cv_results['curr_key'];
		$cv_star = $cv_results['cv_star'];	
		$sd_star = $cv_star * ($robust_average/100);

		$this->log->write("Passed batch_result_key: ". $curr_key);
		$this->log->write("Passed sd_star: ". $sd_star);
	 	$this->log->write("Passed cv_star: ". $cv_star);
	 	$this->log->write("Passed scoredPopulationSize: ". $scoredPopulationSize);
	 	$this->log->write("Passed statisticalPopulationSize: ". $statisticalPopulationSize);

			// compute zscore
			for ($i = 0; $i < $scoredPopulationSize; $i++) {
				if ($isNumericAlgorithmMean)
				{
					$z_score = $sd_star > 0 ? ($validResults[$i]['result'] - $robust_average) / $sd_star : 0;
					$z_score = round($z_score, $decimal_places);
				}
				else
				{
					$z_score = ($validResults[$i]['result'] - $sample_reference_values['reference_value']) / $sd_star;
					$z_score = round($z_score, $decimal_places);
				}

				$validResults[$i]['z_score'] = $z_score;

				// 
				// if($analyte_code == 'F-CHOL'){
					$calculation_comments = $this->getCalculationComments($validResults[$i]);
				// }

				$z_results[] = array(
					'z_score'         => $z_score,
					'calculation_comments'   => $calculation_comments ? $calculation_comments : "",
					'analyte_code'    => $analyte_code,
					'batch_code'      => $batch_code,
					'profile_id'      => $validResults[$i]['profile_id'],
					'result_id'      => $validResults[$i]['result_id']
				);
			}

			$total_population_size = sizeof($currList);

			$batch_updates['batch_code'] = $batch_code;
			$batch_updates['analyte_code'] = $analyte_code;
			$batch_updates['min_value'] = $min_value;
			$batch_updates['max_value'] = $max_value;
			$batch_updates['robust_cv'] = $robust_cv;
			$batch_updates['robust_std_deviation'] = $robust_std_deviation;
			$batch_updates['robust_average'] = $robust_average;
			$batch_updates['statistical_population_size'] = $statisticalPopulationSize;
			$batch_updates['scored_population_size'] = $scoredPopulationSize;
			$batch_updates['total_population_size'] = $total_population_size;
			$batch_updates['cv_star'] = $cv_star;
			$batch_updates['sd_star'] = $sd_star;

			$this->total_batch_results[$batch_key]['cv_star'] = $cv_star;
			$this->total_batch_results[$batch_key]['sd_star'] = $sd_star;

			$this->updateBatchNumericResults($batch_updates, false);

			//update z_scores
			$this->updateZScores($z_results);

			// remove z_scores from results that are not scored. 
			$non_scored_results = $this->resultPresentNotScored($currList);
			if (sizeof($non_scored_results) > 0) {
				$non_scored_result_ids = $this->array_value_recursive("result_id",  $non_scored_results);
				if (sizeof($non_scored_result_ids) > 0) {
					$this->db->query("UPDATE `" . DB_PREFIX . "results` set z_score=null where result_id in (" . implode(",", $non_scored_result_ids). ")");
				}
			}

			
		}

	}

	// calculation comments
	private function getCalculationComments ($validResults) {
		//$this->log->write("Processing valid results: ". print_r($validResults, TRUE));

			if (!$validResults['is_result_present'])
				return "No Result";
			if ($validResults['score'] && $validResults['z_score'] != null && abs($validResults['z_score']) >= 3) 
				return "ACTION!!";
			if ($validResults['score'] && $validResults['z_score'] != null && abs($validResults['z_score']) >= 2) 
				return "WARNING!";
			if ($validResults['score'] && $validResults['z_score'] != null && $validResults['contribute_to_mean'] && abs($validResults['z_score']) < 2) 
				return "Acceptable";
			if ($validResults['score'] && !$validResults['contribute_to_mean'])
				return "Suspect";
			if (!$validResults['score'] && $validResults['contribute_to_mean'])
				return "Not Scored";
			if (!$validResults['score'] && !$validResults['contribute_to_mean'])
				return "Excluded";

			return "";
	}

	// non numeric results module
	private function nonNumericResultModule ($batch_result_key, $currBatchResultOption, $analyte_key, $currList) {
		$count0 = NULL;
		$count1 = NULL;
		$count2 = NULL;
		$count3 = NULL;
		$count4 = NULL;

		$analyteList = $this->analyte_list[$analyte_key];
		$batch_code = $currBatchResultOption[0]['batch_code'];
		$analyte_code = $analyteList['analyte_code'];

		if(strlen(trim($currBatchResultOption[0]['option0'])) > 0) {
			$count0 = 0;
		} 
		if (strlen(trim($currBatchResultOption[0]['option1'])) > 0) {
			$count1 = 0;
		} 
		if (strlen(trim($currBatchResultOption[0]['option2'])) > 0) {
			$count2 = 0;
		} 
		if (strlen(trim($currBatchResultOption[0]['option3'])) > 0) {
			$count3 = 0;
		} 
		if (strlen(trim($currBatchResultOption[0]['option4'])) > 0) {
			$count4 = 0;
		}

		// $this->log->write("Processing currBatchResultOption: ". print_r($currBatchResultOption, TRUE));

		$non_numeric_results = array();

		$curr_result_list = array_filter($currList, function ($result) use ($analyte_code) {
			return ($result['analyte_code'] == $analyte_code);
		});

		foreach ($curr_result_list as $key => $value) {
			if($value['result'] == '0') {
				if ($count0 == NULL) $count0 = 1; else $count0++;
			} elseif ($value['result'] == '1') {
				if ($count1 == NULL) $count1 = 1; else $count1++;
			} elseif ($value['result'] == '2') {
				if ($count2 == NULL) $count2 = 1; else $count2++;
			} elseif ($value['result'] == '3') {
				if ($count3 == NULL) $count3 = 1; else $count3++;
			} elseif ($value['result'] == '4') {
				if ($count4 == NULL) $count4 = 1; else $count4++;
			}
		}

		unset($key);
		unset($value);

		$non_numeric_results = array (
			'count0' => $count0,
			'count1' => $count1,
			'count2' => $count2,
			'count3' => $count3,
			'count4' => $count4,
			'analyte_code' => $analyte_code,
			'batch_code' => $batch_code,
			'statistical_population_size' => sizeof($curr_result_list),
			'scored_population_size' => sizeof($curr_result_list),
			'total_population_size' => sizeof($curr_result_list)
		);

		$this->log->write("Processing non numeric results: ". print_r($non_numeric_results, TRUE));

		$this->updateBatchNonNumericResults($non_numeric_results);
	}

	//compute
	private function compute($batch_code, $analyte_code, $decimal_places, $validResults, $currList){
		// $validResults = array_filter($validResults);

		$statisticalPopulationSize = sizeof($validResults);

		// initialize all variables
		$sum = null;
		$robust_average = null;
		$robust_std_deviation = null;
		$robust_cv = null; 
		$xstar = null;
		$median_diff = null;
		$cv_star = null;
		$sd_star = null;
		$z_score = null;
		$sstar = null;
		$delta = null;
		$ysum = null;
		$sumsq = null;
		$sumx = null;
		$oldsstar = 0;
		$oldxstar = 0;

		$xstar = $this->calculate_median($validResults);

		$diff = [];
		for ($i = 0; $i < sizeof($this->x); $i++) {
			$diff[$i] = abs($this->x[$i] - $xstar);
		}

		usort($diff, function($a, $b) {
			// return strcmp($a, $b);
			if ($a == $b) {
        return 0;
			}
			return ($a < $b) ? -1 : 1;
		});

		$median_diff = $this->calculate_median_m($diff);

		// if ($median_diff == "0") {
		// 	$median_diff = 1;
		// }

		// if($analyte_code == 'RDW1'){
		// 	$this->log->write("Processing median_diff: ". print_r($median_diff, TRUE));
		// 	$this->log->write("Processing validResults: ". print_r($diff, TRUE));
		// }

		// if($analyte_code == 'Cl') {
		// 	$this->log->write("median diff: ". print_r($diff, TRUE));
		// 	$this->log->write("median diff: ". print_r($median_diff, TRUE));
		// }

		$sstar = 1.483 * $median_diff;

			// the tolerance for convergence
			$delta = pow(10,-$decimal_places);

			while((abs($xstar - $oldxstar) > 0) || (abs($sstar - $oldsstar) > $delta)) {
				$phi = 1.5 * $sstar;

				for ($i = 0; $i < sizeof($this->x); $i++) {
					if ($this->x[$i] < ($xstar - $phi))
					{
						$this->x[$i] = ($xstar - $phi);
						// $this->log->write("Subtraction: ". ($xstar - $phi));
						// $this->log->write("Subtraction val : ". print_r($this->x[$i], TRUE));
						// $val['is_outlier'] = true;
						// $outliers_found = true;
					}
					else
					{
						if($this->x[$i] > ($xstar + $phi)) {
							$this->x[$i] = ($xstar + $phi);
							// $this->log->write("Addition: ". ($xstar + $phi));
							// $val['is_outlier'] = true;
							// $outliers_found = true;
						}
					}
				}

				$oldsstar = $sstar; 
				$oldxstar = $xstar;
				$sumx = 0; 
				$sumsq = 0;

				$sumx = array_sum($this->x);
				$xstar = $sumx / sizeof($this->x);

				for ($i = 0; $i < sizeof($this->x); $i++) {
					$xdiff = $this->x[$i] - $xstar;
					$sumsq = $sumsq + ($xdiff * $xdiff);
				}

				$ysum = $sumsq / (sizeof($validResults) - 1);

				$sstar = $this->sstar2_const * sqrt($ysum);
			}

			$robust_average = $xstar; // = round(xstar); 
			$robust_std_deviation = $sstar; // = round(sstar);
			$robust_cv = $robust_std_deviation * 100 / $robust_average;
			

			$this->log->write("Passed robust_cv: ". $robust_cv);
			$this->log->write("Passed sstar: ". $sstar);
			$this->log->write("Passed robust_std_deviation: ". $robust_std_deviation);
			$this->log->write("Passed robust_average: ". $robust_average);
			// $this->log->write("statisticalPopulationSize: ". $statisticalPopulationSize);

			$compute_res = array(
				'robust_average'         => $robust_average,
				'robust_std_deviation'         => $robust_std_deviation,
				'robust_cv'         => $robust_cv,
				'sstar'         => $sstar,
				'statisticalPopulationSize'      => $statisticalPopulationSize
			);

			return $compute_res;
		
	}

	private function updateZScores($z_results = array()){
		// $query = "";
		
		for ($i = 0; $i < sizeof($z_results); $i++) {
			$query = " UPDATE " . DB_PREFIX . "results SET z_score = ".$z_results[$i]['z_score'].", calculation_comments = '".$z_results[$i]['calculation_comments']."' WHERE result_id=".$z_results[$i]['result_id']."; ";

			// $this->update_qry .= " UPDATE " . DB_PREFIX . "results SET z_score = ".$z_results[$i]['z_score']." WHERE profile_id='".$z_results[$i]['profile_id']."' AND batch_code='".$z_results[$i]['batch_code']."' AND analyte_code='".$z_results[$i]['analyte_code']."'; ";
		
			$this->db->query($query);
		}

		// $this->db->query($query);
		// $this->log->write("query: ". $query);
		
	}

	private function updateBatchNumericResults($batch_updates, $insert = true){

		$query = "INSERT INTO ".DB_PREFIX ."batch_results (`batch_code`, `analyte_code`, `average`, `std_deviation`, `cv`, `statistical_population_size`, `scored_population_size`, `min_value`, `max_value`, `total_population_size`, `sd_star`, `cv_star`) 
		VALUES ('".$batch_updates['batch_code']."', '".$batch_updates['analyte_code']."', '".$batch_updates['robust_average']."', '".$batch_updates['robust_std_deviation']."', '".$batch_updates['robust_cv']."', '".$batch_updates['statistical_population_size']."', '".$batch_updates['scored_population_size']."','".$batch_updates['min_value']."', '".$batch_updates['max_value']."', '".$batch_updates['total_population_size']."', '".$batch_updates['sd_star']."', '".$batch_updates['cv_star']."')";

		// $this->log->write("batch query: ". $query);
	
		$this->db->query($query);

		// if($insert) {
		// 	$query = "INSERT INTO ".DB_PREFIX ."batch_results (`batch_code`, `analyte_code`, `average`, `std_deviation`, `cv`, `statistical_population_size`, `scored_population_size`, `min_value`, `max_value`) 
		// 	VALUES ('".$batch_updates['batch_code']."', '".$batch_updates['analyte_code']."', '".$batch_updates['robust_average']."', '".$batch_updates['robust_std_deviation']."', '".$batch_updates['robust_cv']."', '".$batch_updates['statistical_population_size']."', '".$batch_updates['scored_population_size']."','".$batch_updates['min_value']."', '".$batch_updates['max_value']."')";
		// } else {
		// 	$query = "UPDATE ".DB_PREFIX ."batch_results SET `total_population_size` = '".$batch_updates['total_population_size']."', `sd_star` = '".$batch_updates['sd_star']."', `cv_star` = '".$batch_updates['cv_star']."' WHERE `batch_code` = '".$batch_updates['batch_code']."' AND `analyte_code` = '".$batch_updates['analyte_code']."'";
		// }

		// $this->db->query($query);
	}

	private function updateBatchNonNumericResults($batch_updates){
		$query = "INSERT INTO ".DB_PREFIX ."batch_results SET ";

		foreach ($batch_updates as $key => $value) {
			if($value >= '0') {
				$query .= $key." = '".$value."',";
			}
		}

		$query = rtrim($query, ',').";";

		// $this->log->write("batch query: ". $query);
	
		$this->db->query($query);

	}

	private function resultPresentContributesMean($currList){
		$is_result_present = 1; 
		$contribute_to_mean = 1;
		$curr_result_list = array_filter($currList, function ($result) use ($is_result_present, $contribute_to_mean) {
			return ($result['is_result_present'] == $is_result_present && $result['contribute_to_mean'] == $contribute_to_mean);
		});

		return $curr_result_list;
	}


	private function resultPresentNotScored($currList){
		$score = 0;
		$curr_result_list = array_filter($currList, function ($result) use ($score) {
			return ($result['score'] == $score);
		});

		return $curr_result_list;
	}
	
	private function resultPresentScore($currList){
		$is_result_present = 1; 
		$score = 1;
		$curr_result_list = array_filter($currList, function ($result) use ($is_result_present, $score) {
			return ($result['is_result_present'] == $is_result_present && $result['score'] == $score);
		});

		return $curr_result_list;
	}

	// find median
	function calculate_median($arr) {
    $count = count($arr); //total numbers in array
		$even = $count % 2 == 0;

		if ($even)
			return ($arr[intval($count / 2)]['result'] + $arr[intval($count / 2)-1]['result']) / 2;
		else
			return $arr[intval($count / 2)]['result'];


    return $median;
	}

	// find median
	function calculate_median_m($arr) {
    $count = count($arr); //total numbers in array
		$even = $count % 2 == 0;

		if ($even)
			return ($arr[intval($count / 2)] + $arr[intval($count / 2)-1]) / 2;
		else
			return $arr[intval($count / 2)];
	}

	public function getBatch($batch_code) {
		
		$sql = "SELECT * FROM  " . DB_PREFIX . "batch where batch_code ='".$batch_code."'";
		
		$query = $this->db->query($sql);
		
		return $query->row;
	}

	// Robust statistical calculation
	private function computeCVStar($analyte_code, $batch_code){
		// $this->batch_results[$batch_result_key]
		$ret_val = 0; $count = 0; $curr_key = null;
		$cv_results = array();
		
		$cv_results = array(
			'curr_key'         => -1,
			'cv_star'         => 0
		);

		$curr_result_list = array_filter($this->total_batch_results, function ($result) use ($analyte_code) {
			return ($result['analyte_code'] == $analyte_code);
		});

		// $curr_result_list = array_values($curr_result_list);

		uasort($curr_result_list, function($a, $b) {
			return strcmp($b['batch_code'], $a['batch_code']);
		});

		if ($analyte_code == 'META') {
			$this->log->write("Sample curr_result_list after: ". json_encode($curr_result_list));
		}

		$batches_str = "";
		
		$flag = false;
		foreach ($curr_result_list as $key => $value) {
			$is_survey = false;
			$curr_batch = null;

			$curr_batch = $this->getBatch($value['batch_code']);
			if(sizeof($curr_batch) > 0) {
				$is_survey = $curr_batch['is_survey'];
			}

			// only start processing results once we encounter the batch code;
			if ($curr_batch['batch_code'] == $batch_code)
			{
				$curr_key = $key;
				$cv_results['curr_key'] = $curr_key;
				$is_survey = false; // ignore the fact that the first item may be a survey batch.
				$flag = true;
			}

			if ($flag)
			{
				if (!$is_survey)	
				{
					// 

					//log("processing batch " + currList[i].getBatchCode());
					if ($value['cv'] == null)
					{
						$this->log->write("cv is null..ignoring this value()");
						$batches_str .= $curr_batch['batch_code'].", ";
						// $ret_val = $ret_val + $value['cv'];
						$count++;
					}
					else
					{
						$batches_str .= $curr_batch['batch_code'].", ";
						$ret_val = $ret_val + $value['cv'];
						$count++;
					}
				}
				else
				{
					$this->log->write("ignoring survey batch ".$curr_batch['batch_code']);
				}
			}

			if ($count >= $this->max_cv_count)
			{
				if ($analyte_code == 'META'){
					$this->log->write("batches_str ".$batches_str);
				}

				$this->log->write("CV COUNT = " . $count);
				$cv_star = ($ret_val / $count);
				$cv_results['cv_star'] = $cv_star;
				return $cv_results;
			}

		}
		unset($value);
		unset($key);

		if ($count == 0)
		{
			$this->log->write("No results found");
			$cv_star = 0;
			$cv_results['cv_star'] = $cv_star;
			return $cv_results;
			//return 0;
		}

		$this->log->write("CV COUNT = " . $count);
		$cv_star = ($ret_val / $count);
		$cv_results['cv_star'] = $cv_star;
		return $cv_results;
		// return ($ret_val / $count);
		
	}

	//execute batch updates here
	private function executeBatchUpdates(){
		if(strlen(trim($this->update_qry)) > 0){
			$this->log->write("Query : ". $this->update_qry);
			// $res = $this->db->multi_query($this->update_qry);
			// $this->log->write("Query Res: ". $res);
		}
	}

}
