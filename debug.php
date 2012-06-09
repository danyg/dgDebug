<?php

define("DGDEBUG_ROOT_PATH", dirname(__FILE__) . DIRECTORY_SEPARATOR);
define("DGDEBUG_LOG_PATH", DGDEBUG_ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR);
if(!defined("DGDEBUG_CONSOLE_MODE"))
	define("DGDEBUG_CONSOLE_MODE", true);// ON
if(!defined("DGDEBUG_HIGHLIGHT_MODE"))
	define("DGDEBUG_HIGHLIGHT_MODE", true);// ON

class dgDebug{
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
	public function _(){
		if(!(self::$instance instanceof dgDebug))
			self::$instance = new dgDebug();
		
		self::$instance->setToDebugOnly();
		self::$instance->modeOff = false;
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
	 * Turn off the dbg Block if $cond is true
	 * @param {boolean} $cond
	 * @return dgDebug
	 */
	public function setModeOffIf($cond){
		if($cond) 	$this->modeOff();
		else		$this->modeOn();
		return $this;
	}

	/**
	 * Turn off the all dbg Blocks if $cond is true
	 * @return dgDebug
	 */
	public function setModeGlobalOffIf($cond){
		if($cond) 	$this->modeGlobalOff();
		else		$this->modeGlobalOn();
		return $this;
	}	
	
	/**
	 * Turn off the actual dbg block
	 * 
	 * @return dgDebug
	 */
	public function modeOff(){$this->modeOff = true;return $this;}
	
	/**
	 * Alias of dgDebug::modeOff
	 * @see dgDebug::modeOff
	 * @return dgDebug
	 */
	public function setModeOff(){$this->modeOff = true;return $this;}	
	
	/**
	 * Turn off all dbg Blocks
	 * 
	 * @return dgDebug
	 */
	public function modeGlobalOff(){$this->modeGlobalOff = true;return $this;}
	
	/**
	 * Alias of dgDebug::modeGlobalOff
	 * @see dgDebug::modeGlobalOff
	 * @return dgDebug
	 */
	public function setModeGlobalOff(){$this->modeGlobalOff = true;return $this;}

	/**
	 * Turn on the dbg Block
	 * 
	 * @return dgDebug
	 */
	public function modeOn(){$this->modeOff = false;return $this;}
	
	/**
	 * Alias of dgDebug::modeOn
	 * @see dgDebug::modeOn
	 * @return dgDebug
	 */
	public function setModeOn(){$this->modeOff = false;return $this;}	
	
	/**
	 * Turn on All dbg Blocks
	 * 
	 * @return dgDebug
	 */
	public function modeGlobalOn(){$this->modeGlobalOff = false;return $this;}
	
	/**
	 * Alias of dgDebug::modeGlobalOn
	 * @see dgDebug::modeGlobalOn
	 * @return dgDebug
	 */
	public function setModeGlobalOn(){$this->modeGlobalOff = false;return $this;}
	

	/**
	 * Sets the file name to be used for log.
	 * Path: _dgDebug/logs/{$file}
	 * 
	 * @return dgDebug
	 */
	public function setLogFile($file){
		$this->logFile = $file;
		return $this;
	}
	
	/**
	 * Dump the $data var in the HTML console
	 * 
	 * @return dgDebug
	 */
	public function debug($data, $title=false){
		if(!$this->isAvailable())return $this;

		$this->data =& $data;
		
		$this->addToStack($title);
		return $this;
	}	
	
	/**
	 * Dump the $data var in the logFile.
	 * If the logfile is not seted be used the default
	 * 
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
	 * Dump the $data var in HTML Console AND LogFile depends of
	 *  dbg()->setToLogOnly() dbg()->setToDebugOnly()
	 * If the logfile is not seted be used the default
	 * 
	 * @see dgDebug::setToLogOnly
	 * @see dgDebug::setToDebugOnly
	 * 
	 * @return dgDebug
	 */
	public function d($data, $title=false){
		if(!$this->isAvailable()){return $this;}

		if($this->toDebug)	$this->debug(&$data, $title);
		if($this->toLog)	$this->log(&$data, $title);

		return $this;
	}
	
	/**
	 * Shows the call stacktrace In the HTML Console AND logfile depends of
	 * dbg()->setToLogOnly() dbg()->setToDebugOnly()
	 * 
	 * If the logfile is not seted be used the default
	 * 
	 * @return dgDebug
	 */
	public function trace(){
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

			$tmp .= (empty($tmp) ? "" : " | ") . $step["file"] . ":" . $step["line"];

			$this->data[] = $tmp;
		}
		
		$title = "Trace";
		if($this->toDebug)	$this->addToStack($title);
		if($this->toLog)	$this->addToLog($title);
		
		return $this;
	}
	
	/**
	 * Sets the logmode ON when use dbg()->d() AND sets the log filename
	 * 
	 * @param $file=false		The log filename. the real path be 
	 *							_dgdebug/logs/{$file}
	 * @return dgDebug
	 */
	public function setToLog($file=false){
			if($file !== false) $this->setLogFile($file);
		
			$this->toLog = true;
			return $this;
	}

	/**
	 * Sets the HTML Console Output ON when use dbg()->d()
	 * 
	 * @return dgDebug
	 */
	public function setToDebug(){
		$this->toDebug = true;
		return $this;
	}
	
	/**
	 * Sets the logmode ON AND HTML console Output OFF AND sets the log filename
	 * 
	 * @param $file=false		The log filename. the real path be 
	 *							_dgdebug/logs/{$file}
	 * 
	 * @return dgDebug
	 */
	public function setToLogOnly($file = false){
		if($file !== false) $this->setLogFile($file);
		
		$this->toDebug = false;
		$this->toLog = true;
		return $this;
	}

	/**
	 * Sets the HTML console Output ON AND logmode OFF 
	 * 
	 * @return dgDebug
	 */
	public function setToDebugOnly(){
		$this->toDebug = true;
		$this->toLog = false;
		return $this;
	}
	
	/**
	 * Sets the HTML console Output ON AND logmode ON (default mode)
	 * 
	 * @return dgDebug
	 */
	public function setToLogNDebug(){
		$this->toDebug = true;
		$this->toLog = true;
		return $this;
	}
	
	// PROTECTEDS

	protected function isAvailable(){
		if($this->modeGlobalOff) return false;
		if($this->modeOff) return false;
		return true;
	}

	protected function getTrace(){
		$backtrace = debug_backtrace();
		array_splice($backtrace, 0, 2); // elimino las llamadas a esta clase.
		//$backtrace = array_reverse($backtrace);
		

		if(count($backtrace) > $this->stackTraceSteps) $cC = $this->stackTraceSteps;
		else	$cC = count($backtrace);
		
		$trace = "";
		

		for($i=0;$i<$cC;$i++){
			if(strpos($backtrace[$i]['file'], DGDEBUG_ROOT_PATH . 'debug.php') !== false) continue;
			if(empty($backtrace[$i]['file'])) continue;
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
		
		$log .= "[" . date("H:i:s") . " en '" . $trace . "'] " . PHP_EOL . $this->tmp . PHP_EOL;
		
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
function dbg(){
	return dgdebug::_();
}

register_shutdown_function(array("dgDebug", "flushConsole"));
?>