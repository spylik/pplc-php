<?php
// class PPLCParser
Class PPLCParser {
	private
		$pcreFormat,
		$LookingFor,
		// set log LogFilePatterns
		$LogFilePatterns = array(
			'{timestamp}' => '(?P<timestamp>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2})',
			'{source}' => '(?P<source>[a-zA-Z0-9\-\._:]+)',
			'{process}' => '(?P<process>[a-zA-Z0-9\-\._:]+)',
			'{log_level}' => '(?P<log_level>info+)', // (have another log levels?) 
			'{http_method}' => '(?P<http_method>OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT)', // rfc2616
			'{http_path}' => '(?P<http_path>[a-zA-Z0-9\-\.\/_:]+)',
			'{http_host}' => '(?P<http_host>[a-zA-Z0-9\-\._:]+)',
			'{client_ip}' => '(?P<client_ip>(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))', 
			'{responding_dyno}' => '(?P<responding_dyno>[a-zA-Z0-9\-\._:]+)', 
			'{connection_time}' => '(?P<connection_time>[0-9]+)',
			'{processing_time}' => '(?P<processing_time>[0-9]+)',
			'{http_status}' => '(?P<http_status>[0-9]+)', 
			'{bytes_sent}' => '(?P<receivedBytes>[0-9]+)', 
		);
	// function __construct
	// when we construct our PPLCParser class we can use preset log format
    public function __construct($logFormat = '{timestamp} {source}\[{process}\]: at={log_level} method={http_method} path={http_path} host={http_host} fwd="{client_ip}" dyno={responding_dyno} connect={connection_time}ms service={processing_time}ms status={http_status} bytes={bytes_sent}')
	{
		date_default_timezone_set('UTC');
        $this->setLogFormat($logFormat);
	}
	// end of function __construct


	// function MakeLookingForPatterns
	private function MakeLookingForPatterns($LookingForArray){
		$this->LookingFor=$LookingForArray;
		foreach($LookingForArray as $key=>$val){
			$vars = preg_split("/[\\s+]+/", $val);

			// Now support "{http_method(GET|POST)} {http_path}" only search pattern.  If we need more patterns, just need add "if" and implement pattern
			if($vars[0] ? "GET":"POST" && preg_match("/[a-zA-Z0-9\-\.\/_:]/",$vars[1])){
				$pattern=self::MakeRegexPatternFromUrl($vars[1]);
				$toreturn[$key]['http_method']="#^(".$vars[0].")$#";
				$toreturn[$key]['http_path']=$pattern;
			}
		}
		if($toreturn)return $toreturn;
		else return false;
	}
	// end of function MakeLookingForPatterns


	// function MakeRegexPatternFromUrl
	private function MakeRegexPatternFromUrl($path){
		$patterns[0]='/\{(\w+)\}/i';
		$patterns[1]='/\//i';
		$replacements[0]='([a-zA-Z0-9\-\.\/_:]+)';
		$replacements[1]='\/';
		$regexPattern="#^(".preg_replace($patterns, $replacements, $path).")$#";
		if($regexPattern) return $regexPattern;
		else return false;
	}
	// end of function MakeRegexPatternFromUrl


	// function setLogFormat
    private function setLogFormat($logFormat)
	{
		$pattern[] = "/\[/";
		$replacement[] = "\\[";
		$pattern[] = "/\]/";
		$replacement[] = "\\]";
		$logFormat = preg_replace($pattern, $replacement, $logFormat);
		$this->pcreFormat = "#^{$logFormat}$#";
        foreach ($this->LogFilePatterns as $pattern => $replace) {
            $this->pcreFormat = preg_replace("/{$pattern}/", $replace, $this->pcreFormat);
		}
	}
	// end of function setLogFormat


	// function AnalyseLogFile
	public function AnalyseLogFile($filename,$SearchPatterns,$outputfile=null){
		$lookingFor=self::MakeLookingForPatterns($SearchPatterns);
		$handle = fopen($filename, "r") or die("Couldn't get handle");
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$recordInfo = self::parseLogString($buffer);
				foreach($lookingFor as $key=>$val){
					$looking4keys=array_keys($val);
					$notmatch=false;
					foreach($looking4keys as $looking4keysKEY){
						if(!preg_match($val[$looking4keysKEY],$recordInfo[$looking4keysKEY])){
							$notmatch=true;
							break;
						}
					}
					if(!$notmatch){
						$counted[$key]['requests']++;
						$counted[$key]['response_time'][]=$recordInfo['connection_time']+$recordInfo['processing_time'];

						$counted[$key]['responding_dyno'][$recordInfo['responding_dyno']]++;
					}
				}
			}
			if(is_array($counted)){
				$string.="-------------------------------------\n";
				$string.="Report generated: ".date("Y-m-d H:i:s")."\n";
				$string.="-------------------------------------\n\n";
				foreach($this->LookingFor as $key=>$val){

					// calculating average
					asort($counted[$key]['responding_dyno']);
					$counted[$key]['avgOfResponseTime']=array_sum($counted[$key]['response_time'])/count($counted[$key]['response_time']);
					
					// looking for ResponseTime median
					$counted[$key]['medianOfResponseTime']=self::GetArrayMedian($counted[$key]['response_time']);

					// looking for ResponseTime mode 
					$counted[$key]['modeOfResponseTime']=self::GetArrayMode($counted[$key]['response_time']);

					$string=$string."Pattern \"".$this->LookingFor[$key]."\"\nmatched: ".$counted[$key]['requests']." times, average response time: ".$counted[$key]['avgOfResponseTime']."ms, median of response time: ".$counted[$key]['medianOfResponseTime']."ms, mode of reponse time: ".$counted[$key]['modeOfResponseTime']."ms, the most responded dyno: ".key($counted[$key]['responding_dyno'])."\n\n";
				}
			}
			fclose($handle);
			print_r ($string);
			if($outputfile!=null and $outputfile!=""){
				$fp = fopen($outputfile, 'w');
				fwrite($fp, $string);
				fclose($fp);
			}
		}
	}
	// end of function AnalyseLogFile

	// function GetArrayMode
	private function GetArrayMode($array){
		$values = array_count_values($array);
		$mode = array_search(max($values), $values);
		if($mode) return $mode;
		else return false;
	}
	// end of function GetArrayMode
	
	// function GetArrayMedian
	private function GetArrayMedian($array){
		$count = count($array);
		if($count>0){
			asort($array);
			$middleIndex = floor($count / 2);
			$median = $array[$middleIndex];
			if ($count % 2 == 0) {
				$median = ($median + $array[$middleIndex - 1]) / 2;
			}
		}
		if($median)return $median;
		else return 0;
	}
	// end of function GetArrayMedian


	// function parseLogString
    private function parseLogString($line)
	{
        if (preg_match($this->pcreFormat, $line, $matches)) {
	        foreach (array_filter(array_keys($matches), 'is_string') as $key) {
    	        $toreturn[$key] = $matches[$key];
			}
		}else {
			echo"error in logfile format pattern\n";
		}
		
		if($toreturn)return $toreturn;
		else return false;
	}
	// end of function parseLogString

}
// end of class PPLCParser
