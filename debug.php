<?php

define("DGDEBUG_ROOT_PATH", dirname(__FILE__) . DIRECTORY_SEPARATOR);
define("DGDEBUG_LOG_PATH", DGDEBUG_ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR);
if(!defined("DGDEBUG_CONSOLE_MODE"))
	define("DGDEBUG_CONSOLE_MODE", true);// ON
if(!defined("DGDEBUG_HIGHLIGHT_MODE"))
	define("DGDEBUG_HIGHLIGHT_MODE", true);// ON

class dgDebug{
	/**
	 * @var dgDebug
	 */
	static private $instance;
	private $data;
	private $stack = array();
	private $logFile = 'default.log';
	private $logPutHeader = array();
	private $toConsole = true;
	private $highlightOn = true;
	private $stackTraceSteps = 4;
	private $tmp;
	private $toDebug=false;
	private $toLog=false;
	private $modeOff=false;
	private $modeGlobalOff=false;
	private $fixMode=false;
	private $version = '0.4.1';
	private $cut='';

	protected function __construct(){
		$this->console = DGDEBUG_CONSOLE_MODE;
		$this->highlightOn = DGDEBUG_HIGHLIGHT_MODE;
		$this->setToDebugOnly();
		$this->modeOff = false;
		$this->modeGlobalOff = false;
	}
	
	// STATICS
	/**
	 * @return dgDebug
	 */
	static public function _($title=''){
		if(!(self::$instance instanceof dgDebug))
			self::$instance = new dgDebug();
		
		if(!empty($title)) $title = '[<strong>'.$title.'</strong>] ';
		self::$instance->cut = $title . self::$instance->getTrace(1);
		self::$instance->setToDebugOnly();
		self::$instance->modeOff = false;
	
		self::$instance->addCut();
		
		return self::$instance;
	}
	
	public function flushConsole(){
		$t = self::_();
		if(count($t->stack) > 0){
			if(!$t->console)	return;
			
			include(DGDEBUG_ROOT_PATH . "debugwin.tpl.php");
		}
	}
	
	// PUBLICS
	
	/**
	 * @return dgDebug
	 */
	public function setModeOffIf($cond){
		if($cond) 	$this->modeOff();
		else		$this->modeOn();
		return $this;
	}

	/**
	 * @return dgDebug
	 */
	public function setModeGlobalOffIf($cond){
		if($cond) 	$this->modeGlobalOff();
		else		$this->modeGlobalOn();
		return $this;
	}	

	/**
	 * @return dgDebug
	 */	
	public function highlightOff(){
		$this->highlightOn = false;
		return $this;
	}
	
	/**
	 * @return dgDebug
	 */	
	public function highlightOn(){
		$this->highlightOn = true;
		return $this;
	}
	
	/**
	 * @return dgDebug
	 */
	public function modeOff(){$this->modeOff = true;return $this;}
	/**
	 * @return dgDebug
	 */
	public function setModeOff(){$this->modeOff = true;return $this;}	
	/**
	 * @return dgDebug
	 */

	public function modeGlobalOff(){$this->modeGlobalOff = true;return $this;}
	/**
	 * @return dgDebug
	 */
	public function setModeGlobalOff(){$this->modeGlobalOff = true;return $this;}

	/**
	 * @return dgDebug
	 */
	public function modeOn(){$this->modeOff = false;return $this;}
	/**
	 * @return dgDebug
	 */
	public function setModeOn(){$this->modeOff = false;return $this;}	
	/**
	 * @return dgDebug
	 */

	public function modeGlobalOn(){$this->modeGlobalOff = false;return $this;}
	/**
	 * @return dgDebug
	 */
	public function setModeGlobalOn(){$this->modeGlobalOff = false;return $this;}
	
	/**
	 * @return dgDebug
	 */
	public function setFixModeOn(){
		$this->fixMode = true;
		return $this;
	}

	/**
	 * @return dgDebug
	 */
	public function setFixModeOff(){
		$this->fixMode = false;
		return $this;
	}


	/**
	 * @return dgDebug
	 */
	public function setLogFile($file){
		$this->logFile = $file;
		return $this;
	}
	
	/**
	 * @return dgDebug
	 */
	public function debug($data, $title=false){
		if(!$this->isAvailable())return $this;

		$this->data =& $data;
		
		$this->addToStack($title);
		return $this;
	}	
	
	/**
	 * @return dgDebug
	 */
	public function log($data, $title=false, $logFile = null){
		if(!$this->isAvailable()) return $this;

		if($logFile != null) $this->setLogFile($logFile);
		$this->data =& $data;
		
		$this->addToLog($title);
		if($logFile != null)	$this->setLogFile("default.log");
		return $this;
	}
	
	/**
	 * @return dgDebug
	 */
	public function d($data, $title=false){
		if(!$this->isAvailable()){return $this;}

		if($this->toDebug)	$this->debug($data, $title);
		if($this->toLog)	$this->log($data, $title);

		return $this;
	}
	
	public function trace($title=""){
		if(!$this->isAvailable())return $this;

		$backtrace = debug_backtrace();
		array_splice($backtrace, 0, 1);
		$this->data = array();
		foreach($backtrace as $step){
			$tmp = "";

			if(!empty($step["class"]))
				$tmp .= $step["class"] . (!empty($step["function"]) ? "::" : "");
			if(!empty($step["function"]))
				$tmp .= $step["function"] . "()";

			$tmp .= (empty($tmp) ? "" : " | ") . @$step["file"] . ":" . @$step["line"];

			$this->data[] = $tmp;
		}
		
		if(empty($title))	$title = "Trace";
		else				$title = "Trace: " . $title;

		if($this->toDebug)	$this->addToStack($title);
		if($this->toLog)	$this->addToLog($title);
		
		return $this;
	}
	
	/**
	 * @param $file		nombre del log que se utilizara, al pasar este parm este metodo tambien hace un setLogFile
	 * @return dgDebug
	 */
	public function setToLog($file=false){
		if($this->fixMode === false){
			if($file !== false) $this->setLogFile($file);
		
			$this->toLog = true;
		}
		return $this;
	}

	/**
	 * @return dgDebug
	 */
	public function setToDebug(){
		if($this->fixMode === false){
			$this->toDebug = true;
		}
		return $this;
	}
	
	/**
	 * @return dgDebug
	 */
	public function setToLogOnly($file = false){
		if($this->fixMode === false){
			if($file !== false) $this->setLogFile($file);
		
			$this->toDebug = false;
			$this->toLog = true;
		}
		return $this;
	}

	/**
	 * @return dgDebug
	 */
	public function setToDebugOnly(){
		if($this->fixMode === false){
			$this->toDebug = true;
			$this->toLog = false;
		}
		return $this;
	}
	
	/**
	 * @return dgDebug
	 */
	public function setToLogNDebug(){
		if($this->fixMode === false){
			$this->toDebug = true;
			$this->toLog = true;
		}
		return $this;
	}
	
	// PROTECTEDS

	protected function isAvailable(){
		if($this->modeGlobalOff) return false;
		if($this->modeOff) return false;
		return true;
	}

	protected function getTrace($steps=false){
		if($steps === false) $steps = $this->stackTraceSteps;

		$backtrace = debug_backtrace();
		array_splice($backtrace, 0, 2); // elimino las llamadas a esta clase.
		//$backtrace = array_reverse($backtrace);
		

		if(count($backtrace) > $steps) $cC = $steps;
		else	$cC = count($backtrace);
		
		$trace = "";
		

		for($i=0;$i<$cC;$i++){
			if(@strpos($backtrace[$i]['file'], DGDEBUG_ROOT_PATH . 'debug.php') !== false) continue;
			if(@empty($backtrace[$i]['file'])) continue;
			$trace .= (empty($trace) ? "" : " | ") . $backtrace[$i]['file'] . ":" . $backtrace[$i]['line'];
		}

		return $trace;
	}

	protected function addToLog($title=false){
		$trace = ($title ? "[<strong>{$title}</strong>] " : '') .$this->getTrace();
		
		$this->dataToString();
		
		$log = "";
		if(!array_key_exists($this->logFile, $this->logPutHeader)){
			$log .= "################################################################################" . PHP_EOL;
			$log .= "# [" . date("d/m/Y")  . "] " . $_SERVER["REQUEST_URI"] . PHP_EOL;
			$log .= "################################################################################" . PHP_EOL;

			$this->logPutHeader[$this->logFile] = true;
		}
		
		$this->dataToString();
		
		$log .= PHP_EOL . "[" . date("H:i:s") . " en '" . $trace . "'] " . PHP_EOL . $this->tmp . PHP_EOL;
		
		file_put_contents(DGDEBUG_LOG_PATH . $this->logFile, $log, FILE_APPEND);
	}

	protected function addToStack($title=false){
		$trace = ($title ? "[<strong>{$title}</strong>] " : '') . $this->getTrace();

		$this->dataToString();

		if($this->highlightOn)
			$this->highlight();
		$this->stack[] = array(
			"trace" => $trace,
			"html" => $this->tmp
		);
		$this->tmp = "";
	}
	
	protected function addCut(){
		if(!empty($this->cut)){
			$this->stack[] = (string)$this->cut;
			$this->cut=null;
		}
	}
	
	protected function dataToString(){
		ob_start();
		switch(gettype($this->data)){
			case 'boolean':
				echo gettype($this->data) . " " . ($this->data ? "true" : "false");
			break;
			case 'object':
			case 'array':
				print_r($this->data);
			break;
			case 'string':
				echo "string(".strlen($this->data).") " . $this->data;
			break;
			default:
				echo gettype($this->data) . " " . $this->data;
			break;
		}
		$this->tmp = ob_get_clean();
	}

	protected function highlight(){
		$this->tmp = htmlentities($this->tmp);
		
		$this->tmp = preg_replace('" \[.*\]"', "<span class='__key'>\\0</span>", $this->tmp);
		$this->tmp = preg_replace('" \".*\""', "<span class='__string'>\\0</span>", $this->tmp);
		//$this->tmp = preg_replace('"\(.*\)"', "<span> <span onclick='collapseThis(this)'>(</span><span class='Collapsable'>\\0</span><span>)</span></span>", $this->tmp);
		$this->tmp = preg_replace(
			array
			(
				'"string\(.*\)"',
				'"array\(.*\)"',
				'"Array"',
				'"object\(.*\)"',
                '"bool\(.*\)"',
                '"int\(.*\)"',
                '"boolean: "',
                '"string:"',
                '"integrer:"',
                '"double:"',
                '"NULL"',
                '"true"',
                '"false"',

			),
			"<span class='__type'>\\0</span>", $this->tmp
		);
		
		$this->tmp = preg_replace(
			array
			(
				'"\(\n"',
				'"\{\n"',
			),
			"<span><span class='__collapsablebtn__ __block'>" . str_replace("\n", "", "\\0")  . "</span><span class='__collapsableelm__'>", $this->tmp
		);

		$this->tmp = preg_replace(
			array
			(
				'"\n\)\n"',
				'"\n\}\n"',
			),
			"</span><span class='__block'>\\0</span></span>", $this->tmp
		);
		
		$this->tmp = preg_replace(
			array
			(
				'" [ ]*\(\n"',
				'" [ ]*\{\n"',
			),
			"<span><span class='__collapsablebtn__' __block>" . str_replace("\n", "", "\\0")  . "</span><span class='__collapsableelm__'>", $this->tmp
		);

		$this->tmp = preg_replace(
			array
			(
				'" [ ]*\)\n"',
				'" [ ]*\}\n"',
			),
			"</span><span class='__block'>\\0</span></span>", $this->tmp
		);

		$this->tmp = preg_replace(
			array
			(
				'"\=\>"',
				'"\|"',
				'"\|\|"',
				'"\& "',
				'"\&\& "',
				'"\+"',
				'"\-"',

			),
			"<span class='__operator'>\\0</span>", $this->tmp
		); 
	}
}

/**
 * @return dgDebug
 */
function dbg($title=''){
	return dgdebug::_($title);
}

register_shutdown_function(array("dgDebug", "flushConsole"));
?>