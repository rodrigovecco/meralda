<?php

/**
 * Autoload Manager Class.
 * 
 * Manages class autoloading with prefix-based and namespace-based strategies.
 * Supports multiple autoloaders, special autoloaders, and sub-prefix managers.
 */
class mw_autoload_manager {
	
	/** @var array<string, object> Registered autoloaders indexed by code */
	private $autoloadersByCod = [];
	
	/** @var array<string, object> Special autoloaders indexed by code */
	private $specialAutoloadersByCod = [];
	
	/** @var array<string, object> Prefix managers */
	private $prefmanagers = [];
	
	/** @var array<string, object> Namespace prefix managers */
	private $nsprefmanagers = [];
	
	/** @var string Default prefix for class names */
	private $defpref = "mwmod";
	
	/** @var bool Whether to output errors */
	public $output_error = false;
	
	/** @var bool Silent mode - suppresses all error output */
	public $silentMode = false;
	
	/** @var array Autoload reports for debugging */
	public $reports = [];
	
	/** @var bool Whether to keep autoload reports */
	public $keep_reports = false;
	
	/**
	 * Constructor.
	 */
	function __construct() {
		// Initialize
	}
	
	/**
	 * Enables silent mode - suppresses all error output.
	 * 
	 * @return void
	 */
	function setSilentMode(): void {
		$this->silentMode = true;
		$this->output_error = false;
	}
	
	/**
	 * Adds a special autoloader with custom path and prefix.
	 * 
	 * @param string $cod Unique identifier for the autoloader.
	 * @param string $basepath Base path for class files.
	 * @param string $pref Class prefix.
	 * @param string|false $namespace Namespace or false.
	 * @return mw_autoload_prefman_special The created autoloader.
	 */
	function addSpecialAutoloader($cod, $basepath, $pref = "", $namespace = false) {
		$autoloader = new mw_autoload_prefman_special($this, $cod, $basepath, $pref, $namespace);
		$this->add_pref_man($cod, $autoloader);
		return $autoloader;
	}
	
	/**
	 * Starts collecting autoload reports.
	 * 
	 * @return array Previous reports before reset.
	 */
	function startReporting(): array {
		$prev = $this->reports;
		$this->reports = [];
		$this->keep_reports = true;
		return $prev;
	}
	
	/**
	 * Stops collecting autoload reports.
	 * 
	 * @return array Collected reports.
	 */
	function endReporting(): array {
		$prev = $this->reports;
		$this->reports = [];
		$this->keep_reports = false;
		return $prev;
	}
	
	/**
	 * Gets class information for a given class name.
	 * 
	 * @param string|array $class_name Class name or array of class names.
	 * @return array|false Class info array or false if invalid.
	 */
	function get_class_info_for_class($class_name) {
		if (!$class_name) {
			return false;
		}
		
		// Handle array of class names recursively
		if (is_array($class_name)) {
			$r = [];
			foreach ($class_name as $cn) {
				$r[$cn] = $this->get_class_info_for_class($cn);
			}
			return $r;
		}
		
		$info = [];
		$info["className"] = $class_name;
		$a = explode("_", $class_name, 4);
		
		if ($al = $this->getAutoloaderForClass($class_name, $a, $info)) {
			$al->get_class_info($class_name, $info);
		} else {
			$info["error"] = "no autoloader for class";
		}
		
		return $info;
	}
	
	/**
	 * Gets the namespace prefix manager for a namespaced class.
	 * 
	 * @param string $class_name Fully qualified class name with namespace.
	 * @return object|false The namespace manager or false if not found.
	 */
	function get_ns_pref_man_for_class($class_name) {
		// Check if class has namespace
		if (!strpos($class_name, '\\')) {
			return false;
		}
		
		if ($mans = $this->get_ns_mans()) {
			foreach ($mans as $m) {
				if ($m->check_class_namespace($class_name)) {
					return $m;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Gets a sub-prefix manager.
	 * 
	 * @param string $subpref Sub-prefix identifier.
	 * @param string|false $pref Main prefix (uses default if false).
	 * @return object|false The sub-prefix manager or false if not found.
	 */
	function get_sub_pref_man($subpref, $pref = false) {
		if (!$pref) {
			$pref = $this->get_def_pref();
		}
		
		if (!$pm = $this->get_pref_man($pref)) {
			return false;
		}
		
		return $pm->get_man($subpref);
	}
	
	/**
	 * Gets the sub-prefix manager for a given class name.
	 * 
	 * @param string $class_name Class name to analyze.
	 * @return object|false The sub-prefix manager or false if not found.
	 */
	function get_sub_pref_man_for_class($class_name) {
		if (!$class_name || !is_string($class_name)) {
			return false;
		}
		
		$a = explode("_", $class_name, 4);
		return $this->get_sub_pref_man($a[1], $a[0]);
	}
	
	/**
	 * Creates and adds a sub-prefix manager.
	 * 
	 * @param string $subpref Sub-prefix identifier.
	 * @param string $path Base path for class files.
	 * @param string|false $pref Main prefix (uses default if false).
	 * @return object|false The created manager or false on failure.
	 */
	function create_and_add_sub_pref_man($subpref, $path, $pref = false) {
		if (!$pref) {
			$pref = $this->get_def_pref();
		}
		if (!$this->check_pref($subpref)) {
			return false;
		}
		if (!$prefman = $this->get_or_create_pref_man($pref)) {
			return false;
		}
		
			$sm = new mw_autoload_subprefmanfilebased_width_subclases($this, $subpref, $path);
		
		return $this->add_manager($sm, $pref);
	}
	
	/**
	 * Checks if a class exists, optionally triggering autoload.
	 * 
	 * @param string $class_name Class name to check.
	 * @return string|false 'class_exists', 'loaded', or false.
	 */
	function class_exists($class_name) {
		if (!$class_name) {
			return false;
		}
		
		// Check if already loaded without autoload
		if (class_exists($class_name, false)) {
			return "class_exists";
		}
		
		$this->output_error = false;
		$ok = false;
		
		// Try to load the class
		if (class_exists($class_name)) {
			$ok = "loaded";
		}
		
		if (!$this->silentMode) {
			$this->output_error = true;
		}
		
		return $ok;
	}
	
	/**
	 * Performs autoload for a class.
	 * 
	 * @param string $class_name Class name to autoload.
	 * @param bool $silent Whether to suppress error output.
	 * @return bool True if class was loaded successfully.
	 */
	final function do_autoload($class_name, $silent = false) {
		ob_start();
		$report = $this->_do_autoload($class_name);
		ob_end_clean();
		
		if ($this->keep_reports) {
			$this->reports[$class_name] = $report;
		}
		
		if ($this->output_error && !$silent && !$report["result"]) {
			mw_array2list_echo($report);
		}
		
		return $report["result"];
	}
	
	/**
	 * Internal autoload implementation.
	 * 
	 * @param string $class_name Class name to load.
	 * @return array Autoload report with result status.
	 */
	private function _do_autoload($class_name): array {
		$report = [
			"classname" => $class_name,
			"result" => false
		];
		
		if (!$class_name || !is_string($class_name)) {
			return $report;
		}
		
		$a = [];
		if (!$pm = $this->getAutoloaderForClass($class_name, $a, $report)) {
			return $report;
		}
		
		$report["prefman"] = $pm;
		$pm->do_autoload($class_name, $a, $report);
		
		// Check if class or interface was loaded
		if (class_exists($class_name, false)) {
			$report["result"] = true;
		} elseif (interface_exists($class_name, false)) {
			$report["result"] = true;
			$report["isinterface"] = true;
		}
		
		return $report;
	}
	
	/**
	 * Gets the appropriate autoloader for a class.
	 * 
	 * @param string $class_name Class name to find autoloader for.
	 * @param array &$nameparts Reference to store parsed name parts.
	 * @param array &$report Reference to store autoload report.
	 * @return object|false The autoloader or false if not found.
	 */
	function getAutoloaderForClass($class_name, &$nameparts = [], &$report = []) {
		if(!$class_name){
			return false;	
		}
		$nameparts=explode("_",$class_name,4);
		if(strpos($class_name,'\\')){
			if($pm=$this->get_ns_pref_man_for_class($class_name)){
				$report["autoloaderselectmode"]="ns";
				
				return $pm;	
			}
		}else{
			if($pm=$this->get_pref_man($nameparts[0])){
				$report["autoloaderselectmode"]="pref";
				return $pm;	
			}
		}
		if($pm=$this->getSpecialAutoloaderForClass($class_name)){
			$report["autoloaderselectmode"]="special";
			return $pm;	
				
		}
	}
	function getSpecialAutoloaderForClass($class_name){
		if($items=$this->getSpecialAutoloaders()){
			foreach($items as $item){
				if($item->specialCheckClassName($class_name)){
					return $item;	
				}
			}
		}
	}
	
	function add_alt_man($pref, $basepath) {
		$man = new mw_autoload_prefman_alt($this, $pref, $basepath);
		return $this->add_pref_man($pref, $man);
	}

	/**
	 * Adds a sub-manager to a prefix manager.
	 * 
	 * @param object $man The manager to add.
	 * @param string|false $pref Prefix (uses default if false).
	 * @return object|false The added manager or false.
	 */
	final function add_manager($man, $pref = false) {
		if (!$pref) {
			$pref = $this->get_def_pref();
		}
		
		$subpref = $man->pref;
		if (!$this->check_pref($subpref)) {
			return false;
		}
		if (!$prefman = $this->get_or_create_pref_man($pref)) {
			return false;
		}
		
		return $prefman->add_manager($man);
	}
	
	/**
	 * Gets a prefix manager by prefix.
	 * 
	 * @param string $pref Prefix identifier.
	 * @return object|false The prefix manager or false.
	 */
	final function get_pref_man($pref) {
		if (!$this->check_pref($pref)) {
			return false;
		}
		
		if (isset($this->prefmanagers[$pref]) && $this->prefmanagers[$pref]) {
			return $this->prefmanagers[$pref];
		}
		
		return false;
	}
	
	/**
	 * Gets all namespace managers.
	 * 
	 * @return array Namespace prefix managers.
	 */
	final function get_ns_mans(): array {
		return $this->nsprefmanagers;
	}
	
	/**
	 * Creates a new prefix manager.
	 * 
	 * @param string $pref Prefix identifier.
	 * @return object|false The created manager or false.
	 */
	function create_pref_man($pref)  {
		if (!$this->check_pref($pref)) {
			return false;
		}
		
		$man = new mw_autoload_prefman($this, $pref);
		return $man;
	}
	
	/**
	 * Gets all special autoloaders.
	 * 
	 * @return array Special autoloaders indexed by code.
	 */
	final function getSpecialAutoloaders(): array {
		return $this->specialAutoloadersByCod;
	}
	
	/**
	 * Gets all registered autoloaders.
	 * 
	 * @return array Autoloaders indexed by code.
	 */
	final function getAutoloaders(): array {
		return $this->autoloadersByCod;
	}
	
	/**
	 * Registers a prefix manager.
	 * 
	 * @param string $pref Prefix identifier.
	 * @param object $man The manager to register.
	 * @return object|false The registered manager or false.
	 */
	final function add_pref_man($pref, $man) {
		if (!$this->check_pref($pref)) {
			return false;
		}
		
		$this->autoloadersByCod[$pref] = $man;
		
		if (!$man->isSpecial()) {
			$this->prefmanagers[$pref] = $man;
			if ($man->is_ns_mode()) {
				$this->nsprefmanagers[$pref] = $man;
			}
		} else {
			if ($man->includeOnSpecialList()) {
				$this->specialAutoloadersByCod[$pref] = $man;
			}
			if ($man->is_ns_mode()) {
				$this->nsprefmanagers[$pref] = $man;
			}
		}
		
		return $man;
	}
	
	/**
	 * Creates and adds a prefix manager.
	 * 
	 * @param string $pref Prefix identifier.
	 * @return object|false The created manager or false.
	 */
	function create_and_add_prefman($pref) {
		if ($man = $this->create_pref_man($pref)) {
			return $this->add_pref_man($pref, $man);
		}
		return false;
	}
	
	/**
	 * Gets or creates a prefix manager.
	 * 
	 * @param string $pref Prefix identifier.
	 * @return object|false The manager or false.
	 */
	function get_or_create_pref_man($pref) {
		if ($man = $this->get_pref_man($pref)) {
			return $man;
		}
		return $this->create_and_add_prefman($pref);
	}
	
	/**
	 * Gets the default prefix.
	 * 
	 * @return string Default prefix.
	 */
	final function get_def_pref(): string {
		return $this->defpref;
	}
	
	/**
	 * Sets the default prefix.
	 * 
	 * @param string $pref New default prefix.
	 * @return bool True on success, false on invalid prefix.
	 */
	final function set_def_pref($pref): bool {
		if (!$this->check_pref($pref)) {
			return false;
		}
		$this->defpref = $pref;
		return true;
	}
	
	/**
	 * Validates a prefix string.
	 * 
	 * @param mixed $pref Prefix to validate.
	 * @return string|false The prefix if valid, false otherwise.
	 */
	final function check_pref($pref): string|false {
		if (!$pref || !is_string($pref) || !ctype_alpha($pref)) {
			return false;
		}
		return $pref;
	}
	
	/**
	 * Gets debug information about all managers.
	 * 
	 * @return array Debug info with manager details.
	 */
	function get_debug_info(): array {
		$r = ["managers" => []];
		
		if ($items = $this->getAutoloaders()) {
			foreach ($items as $cod => $m) {
				$r["managers"][$cod] = $m->get_debug_info();
			}
		}
		
		return $r;
	}
	
	/**
	 * Gets all prefix managers.
	 * 
	 * @return array Prefix managers.
	 */
	final function get_managers(): array {
		return $this->prefmanagers;
	}
}

include_once "prefman.php";
include_once "subprefman.php";
?>