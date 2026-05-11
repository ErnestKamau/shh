<?php
/**
 * @author		Nuvemite Technologies
*/
namespace App\ReportGenerator;

/**
* ReportGenerator class
*/

use lib\JasperReports\phpjasper\geekcom\phpjasper\src\PHPJasper;
use Illuminate\Support\Facades\Log;
// use PHPJasper\PHPJasper;
class ReportGenerator {
	private $registry;
	protected $main_report;
	protected $main_report_name;
	protected $client_name;
	protected $sub_reports = array();
	protected $report_params = array();
	protected $db_connection;
	protected $report_compile_input_dir;
	protected $report_formats = array();
	protected $report_output_file_name;
	protected $report_generate_output_dir;
	protected $log;
	const report_output_dir = 'app/reports';

	//Report types
	const RESULT_SHEET_TYPE = 'resultsheet';
	const OPERATIONAL_TYPE = 'operational';
	const STATISTICS_TYPE = 'statistical';
	const ADHOC_TYPE = 'adhoc';

	/**
	 * Constructor
 	*/
	public function __construct($registry, $report_type, $report_output_file_name, $db_connection, $client_name, $report_input_dir) {
		$this->registry = $registry;
		$this->db_connection = $db_connection;
		$this->report_formats[] = 'pdf';
		$this->report_compile_input_dir = $report_input_dir . $report_type;
		$this->report_output_file_name = $report_output_file_name;
		$this->client_name = urlencode($client_name);

		// if($this->config->get('config_ieqas_report_pdf_output_dir') != null) {
		// 	$this->report_generate_output_dir = $this->config->get('config_ieqas_report_pdf_output_dir');
		// } else {
		// 	$this->report_generate_output_dir = sys_get_temp_dir();
		// }
		$this->report_generate_output_dir = storage_path('app/reports').'/'.$this->client_name.'/';

		// $this->log = new Log('error.log');
	}

	public function __get($name) {
		return $this->registry->get($name);
	}

	/**
   *
   * @param	string	$report
  */
	public function addMainReport($report) {
		$this->main_report = $report;
		$this->main_report_name = str_replace(".jrxml", '', $report);
	}

	/**
   *
   * @param	string	$reports
  */
	public function addSubReports($sreports) {
		$this->sub_reports = $sreports;
	}

	/**
	 *
	 * @param	string	$report
	*/
	public function addSubReport($report) {
		$this->sub_reports[] = $report;
	}

	public function setReportParams($report_params) {
		$this->report_params = $report_params;
	}

	// private function compileReports() {
	// 	try{
	// 		if (!file_exists($this->report_compile_output_dir) && !mkdir($this->report_compile_output_dir, 0777, true))
	// 		{
	// 			Log::error("FATAL ERROR!!! FAILED TO CREATE DIRECTORY $this->report_compile_output_dir");
	// 		}

	// 		Log::debug('(): Compiling reports [' . implode(',', $this->sub_reports) . ']');
	// 		Log::debug('(): Compiling reports [' . $this->main_report . ']');
	// 		Log::debug('(): Compiling reports [' . $this->report_compile_input_dir . $this->main_report . ']');
	// 		Log::debug('(): [Input Dir =' . $this->report_compile_input_dir . '], [Output Dir =' . $this->report_compile_output_dir . ']' . ']');

	// 		$jasper = new PHPJasper;
	// 		//Compile sub reports
	// 		if(sizeof($this->sub_reports) > 0){
	// 			if (!file_exists($this->report_compile_output_dir.'subreports/'. $this->main_report_name) && !mkdir($this->report_compile_output_dir.'subreports/'. $this->main_report_name, 0777, true))
	// 			{
	// 				Log::error("FATAL ERROR 2!!! FAILED TO CREATE DIRECTORY $this->report_compile_output_dir");
	// 			}
	// 		}
	// 		foreach ($this->sub_reports as $report) {
	// 			$compile_report_file = $this->report_compile_input_dir.'subreports/'. $this->main_report_name. '/' . $report;
	// 			$jasper->compile($compile_report_file, $this->report_compile_output_dir.'subreports/'. $this->main_report_name)->execute();
	// 		}

	// 		$this->log->write("jasper compile report: ". print_r($jasper->compile(
	// 			$this->report_compile_input_dir . $this->main_report , $this->report_compile_output_dir
	// 			)->output(), TRUE));

	// 		$jasper->compile($this->report_compile_input_dir . $this->main_report , $this->report_compile_output_dir )->execute();

	// 	} catch(Exception $e){
	// 		$this->log->write( __FUNCTION__ . '(): Error compiling report(s) [' . $e->getMessage() .']');
	// 		return array('result_code' => 1, 'result_msg' => '<strong>Error compiling report(s)</strong>
	// 		<br> <strong>Error Message:</strong> [' . $e->getMessage() .']');
	// 	}
	// 	return array('result_code' => 0, 'result_msg' => '');
	// }

	/**
	 *
	 *
	*/
	public function generateReport() {
		try {
			if (strlen($this->main_report) === 0) {
				throw new \Exception('Error: Report to compile is required!');
			}
			
			if (!file_exists($this->report_generate_output_dir) && !mkdir($this->report_generate_output_dir, 0777, true))
			{
				throw new \Exception("FATAL ERROR!!! FAILED TO CREATE DIRECTORY [$this->report_generate_output_dir]");
			}

			// $output = $this->compileReports();

				$jdbc_dir = env('JDBC_DIR', base_path('lib/JasperReports/phpjasper/geekcom/phpjasper/bin/jasperstarter/jdbc/postgresql'));

				if($this->db_connection) {
					$options = [
				    'format' => $this->report_formats,
				    'locale' => 'en',
				    'params' => $this->report_params,
						'db_connection' => [
							'driver' => 'generic',
							'host' => env('DB_HOSTNAME', '127.0.0.1'),
							'port' => env('DB_PORT', '5432'),
							'jdbc_driver' => env('JDBC_DRIVER', 'org.postgresql.Driver'),
							'jdbc_url' => env('JDBC_URL', 'jdbc:postgresql://' . env('DB_HOST', '127.0.0.1') . ':' . env('DB_PORT', '5432') . '/' . env('DB_DATABASE', 'gcla')),
							'jdbc_dir' => $jdbc_dir,
							'database' => env('DB_DATABASE', 'gcla'),
							'username' => env('DB_USERNAME', 'root'),
							'password' => env('DB_PASSWORD', '')
						]
					];
				} else {
					$options = [
				    'format' => $this->report_formats,
				    'locale' => 'en',
					];
				}


				$jasper = new PHPJasper;

				// $generate_input = storage_path('app/compiled_reports/'.$this->report_compile_input_dir . '.jasper');

				$generate_input = $this->report_compile_input_dir . '.jasper';

				// $this->log->write( __FUNCTION__ . '(): Generating report [' . $this->main_report . ']');
        // $this->log->write( __FUNCTION__ . '(): [Input Dir =' . $generate_input . '], [Output Dir =' . $this->report_generate_output_dir  . $this->report_output_file_name . ']');
        
				if (method_exists($jasper, "getPathExecutable")) {
					$path_executable = $jasper->getPathExecutable() ."/";
				} else {
					$path_executable = "";
        }

        // Log::debug("output report options: ". print_r($options, TRUE));
        
        Log::debug("jasper exec report: $path_executable". print_r($jasper->process(
          $generate_input,
          $this->report_generate_output_dir . $this->report_output_file_name,
          $options
          )->output(), TRUE));

				$output = $jasper->process(
						$generate_input,
						$this->report_generate_output_dir . $this->report_output_file_name,
						$options
            )->execute();
        
        Log::debug("output report: ". print_r($output, TRUE));
				// if ($output != null) {
				// 	$output = print_r($output, true);
				// }
				// $this->downloadReport();
				
			// }
		} catch(Exception $e){
			// $this->log->write( __FUNCTION__ . '(): Error generating report [' . $e->getMessage() .']');
			// return array('result_code' => 1, 'result_msg' => '<strong>Error generating report(s)</strong>
			// <br> <strong>Error Message: </strong>[' . $e->getMessage() .']');
		}

		return $output;
	}

	public function downloadReport() {
		foreach ($this->report_formats as $report_format) {
			if($report_format == 'pdf') {
				$this->downloadReportAsPDF();
			}
		}
	}

	private function downloadReportAsPDF(){
		$pdf_file_name = $this->report_output_file_name . '.pdf';
		$tmpName = $this->report_generate_output_dir . $this->report_output_file_name .'.pdf';
		// $tmpName = $this->report_generate_output_dir . pathinfo($this->main_report)['filename'] .'.pdf';

		$this->log->write( __FUNCTION__ . '(): [Report Download Dir =' . $tmpName . ']');
		// $this->log->write( __FUNCTION__ . '(): [Refresh URL =' . $url . ']');

		// header('refresh:2;url='.$url );
		header('Content-Description: File Transfer'); 
		header("Content-type:application/pdf"); 
		header('Content-Disposition: attachment; filename='.$pdf_file_name); 
		header('Content-Transfer-Encoding: binary'); 
		header('Expires: 0'); 
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($tmpName));

		ob_clean();
		flush();
		readfile($tmpName);
	}
}
