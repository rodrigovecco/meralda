<?php

/**
 * Prefix Manager for Autoloading.
 * 
 * Manages autoloading for classes with a specific prefix.
 * Acts as a container for sub-prefix managers.
 */
class mw_autoload_prefman extends mw_baseobj {
	
	/** @var string Class prefix managed by this instance */
	private $pref;
	
	/** @var mw_autoload_manager Main autoload manager reference */
	private $mainman;
	
	/** @var array<string, object> Sub-prefix managers */
	private $managers = [];
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Prefix to manage.
	 */
	function __construct($mainman, $pref) {
		$this->init($mainman, $pref);
	}
	
	/**
	 * Gets class information.
	 * 
	 * @param string $class_name Class name to get info for.
	 * @param array &$info Reference to info array.
	 * @return array Class information.
	 */
	function get_class_info($class_name, &$info = []) {
		if (!$info) {
			$info = [];
		}
		$info["className"] = $class_name;
		$info["autoloader"] = get_class($this);
		$info["pref"] = $this->pref;
		return $info;
	}
	
	/**
	 * Checks if this manager uses namespace mode.
	 * 
	 * @return bool False for base class.
	 */
	function is_ns_mode(): bool {
		return false;
	}
	
	/**
	 * Checks if a class belongs to this manager's namespace.
	 * 
	 * @param string $class_name Class name to check.
	 * @return bool False for base class.
	 */
	function check_class_namespace($class_name): bool {
		return false;
	}
	
	/**
	 * Checks if this is a special autoloader.
	 * 
	 * @return bool False for base class.
	 */
	function isSpecial(): bool {
		return false;
	}
	
	/**
	 * Determines if this manager should be included in special list.
	 * 
	 * @return bool Same as isSpecial() for base class.
	 */
	function includeOnSpecialList(): bool {
		return $this->isSpecial();
	}
	
	/**
	 * Special check for class name matching.
	 * 
	 * @param string $class_name Class name to check.
	 * @return bool False for base class.
	 */
	function specialCheckClassName($class_name): bool {
		return false;
	}
	
	/**
	 * Gets debug information about this manager.
	 * 
	 * @return array Debug info including class and sub-managers.
	 */
	function get_debug_info(): array {
		$r = [];
		$r["class"] = get_class($this);
		
		if ($items = $this->get_managers()) {
			$r["managers"] = [];
			foreach ($items as $cod => $m) {
				$r["managers"][$cod] = $m->get_debug_info();
			}
		}
		return $r;
	}
	
	/**
	 * Gets all sub-managers.
	 * 
	 * @return array Sub-prefix managers.
	 */
	final function get_managers(): array {
		return $this->managers;
	}
	
	/**
	 * Gets a sub-manager by prefix.
	 * 
	 * @param string $pref Sub-prefix identifier.
	 * @return object|false The manager or false.
	 */
	function get_man($pref) {
		return $this->get_existing_man($pref);
	}
	
	/**
	 * Gets an existing sub-manager by prefix.
	 * 
	 * @param string $pref Sub-prefix identifier.
	 * @return object|false The manager or false if not found.
	 */
	final function get_existing_man($pref) {
		if (!$pref) {
			return false;
		}
		if (isset($this->managers[$pref]) && $this->managers[$pref]) {
			return $this->managers[$pref];
		}
		return false;
	}
	
	/**
	 * Performs autoload for a class.
	 * 
	 * @param string $class_name Full class name.
	 * @param array $class_parts Parsed class name parts.
	 * @param array &$report Reference to autoload report.
	 * @return bool|void False on failure.
	 */
	function do_autoload($class_name, $class_parts, &$report = []) {
		if (!$man = $this->get_man($class_parts[1])) {
			return false;
		}
		$report["subman"] = $man;
		$man->do_autoload($class_parts[2], $class_name, $report);
	}
	
	/**
	 * Adds a sub-manager.
	 * 
	 * @param object $man Manager to add.
	 * @return object The added manager.
	 */
	final function add_manager($man) {
		$subpref = $man->pref;
		$this->managers[$subpref] = $man;
		$man->set_pref_man($this);
		return $this->managers[$subpref];
	}
	
	/**
	 * Initializes the manager.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Prefix to manage.
	 * @return void
	 */
	final function init($mainman, $pref): void {
		$this->mainman = $mainman;
		$this->pref = $pref;
	}
	
	/**
	 * Gets the main manager (private accessor).
	 * 
	 * @return mw_autoload_manager Main autoload manager.
	 */
	final function __get_priv_mainman() {
		return $this->mainman;
	}
	
	/**
	 * Gets the prefix (private accessor).
	 * 
	 * @return string Prefix.
	 */
	final function __get_priv_pref(): string {
		return $this->pref;
	}
	
	/**
	 * String representation.
	 * 
	 * @return string The prefix.
	 */
	function __toString(): string {
		return $this->pref;
	}
}

/**
 * Alternative Prefix Manager with base path support.
 * 
 * Creates sub-managers dynamically based on a base path structure.
 */
class mw_autoload_prefman_alt extends mw_autoload_prefman {
	
	/** @var string Base path for class files */
	private $basepath;
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Prefix to manage.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref);
		$this->set_basepath($basepath);
	}
	
	/**
	 * Gets or creates a sub-manager by prefix.
	 * 
	 * @param string $pref Sub-prefix identifier.
	 * @return object|false The manager or false.
	 */
	function get_man($pref) {
		if ($man = $this->get_existing_man($pref)) {
			return $man;
		}
		if (!$pref = $this->mainman->check_pref($pref)) {
			return false;
		}
		$sm = new mw_autoload_subprefmanfilebased_width_subclases_alt($this->mainman, $pref, $this->basepath . "/" . $pref);
		return $this->add_manager($sm);
	}
	
	/**
	 * Sets an alternative path for a sub-manager.
	 * 
	 * @param string $cod Sub-prefix code.
	 * @param string $alt Alternative path.
	 * @return mixed Result of set_alt or void.
	 */
	function set_alt($cod, $alt) {
		if ($man = $this->get_man($cod)) {
			return $man->set_alt($alt);
		}
	}
	
	/**
	 * Sets the base path.
	 * 
	 * @param string $basepath Base path.
	 * @return string The set path.
	 */
	final function set_basepath($basepath): string {
		return $this->basepath = $basepath;
	}
	
	/**
	 * Gets the base path (private accessor).
	 * 
	 * @return string Base path.
	 */
	final function __get_priv_basepath(): string {
		return $this->basepath;
	}
}

/**
 * One-File Prefix Manager.
 * 
 * Autoloader that loads all classes from a single file.
 */
class mw_autoload_prefman_onefile extends mw_autoload_prefman {
	
	/** @var string Full path to the single file */
	private $fullfilepath;
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Prefix to manage.
	 * @param string $fullfilepath Full path to the class file.
	 */
	function __construct($mainman, $pref, $fullfilepath) {
		$this->init($mainman, $pref);
		$this->set_full_file_path($fullfilepath);
	}
	
	/**
	 * Gets the full path for a class.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false File path or false.
	 */
	function get_class_path_full($class_name) {
		if (!$class_name) {
			return false;
		}
		if (!$pref = $this->pref) {
			return false;
		}
		if (strpos($class_name, $pref) !== 0) {
			return false;
		}
		return $this->fullfilepath;
	}
	
	/**
	 * Gets debug information.
	 * 
	 * @return array Debug info.
	 */
	function get_debug_info(): array {
		$r = [];
		$r["class"] = get_class($this);
		$r["file"] = $this->fullfilepath;
		
		if ($items = $this->get_managers()) {
			$r["managers"] = [];
			foreach ($items as $cod => $m) {
				$r["managers"][$cod] = $m->get_debug_info();
			}
		}
		return $r;
	}
	
	/**
	 * Performs autoload for a class.
	 * 
	 * @param string $class_name Full class name.
	 * @param array $class_parts Parsed class name parts.
	 * @param array &$report Reference to autoload report.
	 * @return bool|void False on failure.
	 */
	function do_autoload($class_name, $class_parts, &$report = []) {
		if (class_exists($class_name, false)) {
			return;
		}
		
		if (!$file = $this->get_class_path_full($class_name)) {
			return false;
		}
		$report["file"] = $file;
		
		if (!is_file($file) || !file_exists($file) || !is_readable($file)) {
			return false;
		}
		
		require_once($file);
	}
	
	/**
	 * Sets the full file path.
	 * 
	 * @param string $fullfilepath Full path to file.
	 * @return void
	 */
	final function set_full_file_path($fullfilepath): void {
		$this->fullfilepath = $fullfilepath;
	}
	
	/**
	 * Gets the full file path (private accessor).
	 * 
	 * @return string Full file path.
	 */
	final function __get_priv_fullfilepath(): string {
		return $this->fullfilepath;
	}
}

/**
 * Direct Prefix Manager.
 * 
 * Autoloads classes directly from a base path using underscore-to-directory mapping.
 */
class mw_autoload_prefman_direct extends mw_autoload_prefman {
	
	/** @var string Base path for class files */
	private $basepath;
	
	/** @var bool Whether before_autoload_first has been called */
	private $_before_autoload_first_done = false;
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Prefix to manage.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref);
		$this->set_basepath($basepath);
	}
	
	/**
	 * Gets the sub-path for a class file.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Sub-path or false.
	 */
	function get_class_sub_path_full($class_name) {
		if (!$class_name) {
			return false;
		}
		if (!$pref = $this->pref) {
			return false;
		}
		if (strpos($class_name, $pref) !== 0) {
			return false;
		}
		$pClassFilePath = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
		return $pClassFilePath;
	}
	
	/**
	 * Gets the full path for a class file.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Full file path or false.
	 */
	function get_class_path_full($class_name) {
		if (!$sub = $this->get_class_sub_path_full($class_name)) {
			return false;
		}
		if (!$this->basepath) {
			return false;
		}
		return $this->basepath . "/" . $sub;
	}
	
	/**
	 * Executes before-autoload hook once.
	 * 
	 * @return void
	 */
	final function before_autoload_first(): void {
		if ($this->_before_autoload_first_done) {
			return;
		}
		$this->_before_autoload_first_done = true;
		$this->do_before_autoload_first();
	}
	
	/**
	 * Hook method called before first autoload.
	 * Override in subclasses for custom initialization.
	 * 
	 * @return void
	 */
	function do_before_autoload_first(): void {
	}
	
	/**
	 * Performs autoload for a class.
	 * 
	 * @param string $class_name Full class name.
	 * @param array $class_parts Parsed class name parts.
	 * @param array &$report Reference to autoload report.
	 * @return bool|void False on failure.
	 */
	function do_autoload($class_name, $class_parts, &$report = []) {
		if (class_exists($class_name, false)) {
			return;
		}
		
		if (!$file = $this->get_class_path_full($class_name)) {
			return false;
		}
		$report["file"] = $file;
		
		if (!is_file($file) || !file_exists($file) || !is_readable($file)) {
			return false;
		}
		
		$this->before_autoload_first();
		require_once($file);
	}
	
	/**
	 * Gets debug information.
	 * 
	 * @return array Debug info.
	 */
	function get_debug_info(): array {
		$r = [];
		$r["class"] = get_class($this);
		$r["basepath"] = $this->basepath;
		
		if ($items = $this->get_managers()) {
			$r["managers"] = [];
			foreach ($items as $cod => $m) {
				$r["managers"][$cod] = $m->get_debug_info();
			}
		}
		return $r;
	}

	/**
	 * Sets the base path.
	 * 
	 * @param string $basepath Base path.
	 * @return string The set path.
	 */
	final function set_basepath($basepath): string {
		return $this->basepath = $basepath;
	}
	
	/**
	 * Gets the base path (private accessor).
	 * 
	 * @return string Base path.
	 */
	final function __get_priv_basepath(): string {
		return $this->basepath;
	}
}

/**
 * Direct Prefix Manager with Base Path Mode.
 * 
 * Strips the prefix from class name when building file path.
 */
class mw_autoload_prefman_direct_base_path_mode extends mw_autoload_prefman_direct {
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Prefix to manage.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref);
		$this->set_basepath($basepath);
	}
	
	/**
	 * Gets the sub-path for a class file (strips prefix).
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Sub-path or false.
	 */
	function get_class_sub_path_full($class_name) {
		if (!$class_name) {
			return false;
		}
		if (!$pref = $this->pref) {
			return false;
		}
		if (strpos($class_name, $pref) !== 0) {
			return false;
		}
		$n = substr($class_name, strlen($pref) + 1);
		$pClassFilePath = str_replace('_', DIRECTORY_SEPARATOR, $n) . '.php';
		return $pClassFilePath;
	}
}

/**
 * Direct Prefix Manager for Requests.
 * 
 * Specialized autoloader for request classes.
 */
class mw_autoload_prefman_direct_requests extends mw_autoload_prefman_direct_base_path_mode {
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Prefix to manage.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref);
		$this->set_basepath($basepath);
	}
	
	/**
	 * Gets the sub-path for a class file.
	 * Handles exact prefix match specially.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Sub-path or false.
	 */
	function get_class_sub_path_full($class_name) {
		if (!$class_name) {
			return false;
		}
		
		// Handle exact prefix match
		if ($class_name == $this->pref) {
			return $class_name . ".php";
		}
		
		if (!$pref = $this->pref) {
			return false;
		}
		if (strpos($class_name, $pref) !== 0) {
			return false;
		}
		
		$pClassFilePath = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
		return $pClassFilePath;
	}
}

/**
 * Namespace Mode Prefix Manager.
 * 
 * Autoloads classes based on PHP namespaces.
 */
class mw_autoload_prefman_nsmode extends mw_autoload_prefman {
	
	/** @var string Base path for class files */
	private $basepath;
	
	/** @var string Namespace prefix */
	private $namespace;
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Prefix identifier.
	 * @param string $basepath Base path for class files.
	 * @param string $namespace Namespace prefix.
	 */
	function __construct($mainman, $pref, $basepath, $namespace) {
		$this->init($mainman, $pref);
		$this->set_basepath($basepath);
		$this->set_namespace($namespace);
	}
	
	/**
	 * Checks if this manager uses namespace mode.
	 * 
	 * @return bool Always true for this class.
	 */
	function is_ns_mode(): bool {
		return true;
	}
	
	/**
	 * Gets debug information.
	 * 
	 * @return array Debug info.
	 */
	function get_debug_info(): array {
		$r = [];
		$r["class"] = get_class($this);
		$r["basepath"] = $this->basepath;
			$r["namespace"] = $this->namespace;
		
		if ($items = $this->get_managers()) {
			$r["managers"] = [];
			foreach ($items as $cod => $m) {
				$r["managers"][$cod] = $m->get_debug_info();
			}
		}
		return $r;
	}
	
	/**
	 * Gets class information.
	 * 
	 * @param string $class_name Class name.
	 * @param array &$info Reference to info array.
	 * @return array Class information.
	 */
	function get_class_info($class_name, &$info = []) {
		if (!$info) {
			$info = [];
		}
		$info["className"] = $class_name;
		$info["autoloader"] = get_class($this);
		$info["pref"] = $this->pref;
		$info["namespace"] = $this->namespace;
		return $info;
	}
	
	/**
	 * Checks if a class belongs to this manager's namespace.
	 * 
	 * @param string $class_name Class name to check.
	 * @return bool True if class belongs to namespace.
	 */
	function check_class_namespace($class_name): bool {
		if (!$prefix = $this->namespace) {
			return false;
		}
		if (strpos($class_name, $prefix) === 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * Gets the sub-path for a class file.
	 * Converts namespace separators to directory separators.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Sub-path or false.
	 */
	function get_class_sub_path_full($class_name) {
		if (!$class_name) {
			return false;
		}
		if (!$this->check_class_namespace($class_name)) {
			return false;
		}
		if (!$prefix = $this->namespace) {
			return false;
		}
		if (!$subcod = substr($class_name, strlen($prefix))) {
			return false;
		}
		
		$pClassFilePath = str_replace('\\', DIRECTORY_SEPARATOR, $subcod) . '.php';
		return $pClassFilePath;
	}
	
	/**
	 * Gets the full path for a class file.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Full file path or false.
	 */
	function get_class_path_full($class_name) {
		if (!$sub = $this->get_class_sub_path_full($class_name)) {
			return false;
		}
		if (!$this->basepath) {
			return false;
		}
		return $this->basepath . "/" . $sub;
	}
	
	/**
	 * Performs autoload for a class.
	 * 
	 * @param string $class_name Full class name.
	 * @param array $class_parts Parsed class name parts.
	 * @param array &$report Reference to autoload report.
	 * @return bool|void False on failure.
	 */
	function do_autoload($class_name, $class_parts, &$report = []) {
		if (class_exists($class_name, false)) {
			return;
		}
		if (!$this->check_class_namespace($class_name)) {
			return;
		}
		
		if (!$file = $this->get_class_path_full($class_name)) {
			return false;
		}
			$report["file"] = $file;
		
		if (!is_file($file) || !file_exists($file) || !is_readable($file)) {
			return false;
		}
		
		require_once($file);
	}
	
	/**
	 * Sets the namespace.
	 * 
	 * @param string $namespace Namespace prefix.
	 * @return string The set namespace.
	 */
	final function set_namespace($namespace): string {
		return $this->namespace = $namespace;
	}
	
	/**
	 * Gets the namespace (private accessor).
	 * 
	 * @return string Namespace.
	 */
	final function __get_priv_namespace(): string {
		return $this->namespace;
	}
	
	/**
	 * Sets the base path.
	 * 
	 * @param string $basepath Base path.
	 * @return string The set path.
	 */
	final function set_basepath($basepath): string {
		return $this->basepath = $basepath;
	}
	
	/**
	 * Gets the base path (private accessor).
	 * 
	 * @return string Base path.
	 */
	final function __get_priv_basepath(): string {
		return $this->basepath;
	}
}

/**
 * Special Prefix Manager.
 * 
 * Advanced autoloader with namespace support, custom autoload files,
 * and operation tracking capabilities.
 */
class mw_autoload_prefman_special extends mw_autoload_prefman {
	
	/** @var string Base path for class files */
	private $basepath;
	
	/** @var string|false Namespace prefix or false */
	private $namespace = false;
	
	/** @var bool Force non-namespace mode even if namespace is set */
	public $nonsmode = false;
	
	/** @var string Real class prefix */
	private $realPref = "";
	
	/** @var string Class name directory separator character */
	public $classdirsep = "_";
	
	/** @var array Tracking of completed operations */
	public $operationsdone = [];
	
	/** @var string Autoload script filename */
	private $autoloadfile = "autoload.php";
	
	/** @var string Class info script filename */
	private $classinfofile = "classinfo.php";
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $cod Unique identifier code.
	 * @param string $basepath Base path for class files.
	 * @param string $pref Real class prefix.
	 * @param string|false $namespace Namespace or false.
	 */
	function __construct($mainman, $cod, $basepath, $pref = "", $namespace = false) {
		$this->init($mainman, $cod);
		$this->set_realPref($pref);
		$this->set_basepath($basepath);
		$this->set_namespace($namespace);
	}
	
	/**
	 * Checks if a class name matches namespace and prefix.
	 * 
	 * @param string $className Class name to check.
	 * @param string|false $ns Namespace to check against.
	 * @param string $prefix Prefix to check against.
	 * @return bool True if matches.
	 */
	function classNameCheckNS($className, $ns = false, $prefix = ""): bool {
		if (!$ns) {
			// Non-namespaced mode
			if (strpos($className, "\\")) {
				return false;
			}
			if (!$prefix) {
				return true;
			}
			
			$len = strlen($prefix);
			if (strncmp($prefix, $className, $len) === 0) {
				return true;
			}
			return false;
		}
		
		// Namespaced mode
		if (!strpos($className, "\\")) {
			return false;
		}
		$prefix = $ns . "\\" . $prefix;
		$len = strlen($prefix);
		if (strncmp($prefix, $className, $len) === 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * Gets whether an operation has been completed.
	 * 
	 * @param string $cod Operation code.
	 * @return int Number of times operation was done.
	 */
	function getOperationIsDone($cod): int {
		return ($this->operationsdone[$cod] ?? 0) + 0;
	}
	
	/**
	 * Marks an operation as done.
	 * 
	 * @param string $cod Operation code.
	 * @return void
	 */
	function setOperationIsDone($cod): void {
		$this->operationsdone[$cod] = ($this->operationsdone[$cod] ?? 0) + 1;
	}
	
	/**
	 * Checks if this manager uses namespace mode.
	 * 
	 * @return bool True if using namespace mode.
	 */
	function is_ns_mode(): bool {
		if ($this->nonsmode) {
			return false;
		}
		if ($this->namespace) {
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if a class belongs to this manager's namespace.
	 * 
	 * @param string $class_name Class name to check.
	 * @return bool True if class belongs to namespace.
	 */
	function check_class_namespace($class_name): bool {
		if (!$prefix = $this->namespace) {
			return false;
		}
		$prefix .= "\\";
		$len = strlen($prefix);
		if (strncmp($prefix, $class_name, $len) === 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * Gets debug information.
	 * 
	 * @return array Debug info.
	 */
	function get_debug_info(): array {
		$r = [];
		$r["class"] = get_class($this);
		$r["basepath"] = $this->basepath;
		$r["namespace"] = $this->namespace;
		$r["realPref"] = $this->realPref;
		$r["autoloaderPref"] = $this->getFullPref();
		
		if ($items = $this->get_managers()) {
			$r["managers"] = [];
			foreach ($items as $cod => $m) {
				$r["managers"][$cod] = $m->get_debug_info();
			}
		}
		return $r;
	}
	
	/**
	 * Checks if this is a special autoloader.
	 * 
	 * @return bool Always true for this class.
	 */
	function isSpecial(): bool {
		return true;
	}
	
	/**
	 * Determines if this manager should be included in special list.
	 * 
	 * @return bool False if namespace mode, otherwise isSpecial().
	 */
	function includeOnSpecialList(): bool {
		if ($this->is_ns_mode()) {
			return false;
		}
		return $this->isSpecial();
	}
	
	/**
	 * Special check for class name matching.
	 * 
	 * @param string $class_name Class name to check.
	 * @return bool True if class matches the full prefix.
	 */
	function specialCheckClassName($class_name): bool {
		if (!$prefix = $this->getFullPref()) {
			return false;
		}
		if (strpos($class_name, $prefix) === 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * Gets the full prefix including namespace.
	 * 
	 * @return string Full prefix.
	 */
	function getFullPref(): string {
		$r = "";
		if ($this->namespace) {
			$r .= $this->namespace . "\\";
		}
		$r .= $this->realPref;
		return $r;
	}
	
	/**
	 * Gets the sub-path for a class file.
	 * 
	 * @param string $class_name Class name.
	 * @return false Always false for base special manager.
	 */
	function get_class_sub_path_full($class_name): bool {
		return false;
	}
	
	/**
	 * Gets the full path for a class file.
	 * 
	 * @param string $class_name Class name.
	 * @return false Always false for base special manager.
	 */
	function get_class_path_full($class_name): bool {
		return false;
	}
	
	/**
	 * Gets the autoload file path.
	 * 
	 * @param string|false $class_name Optional class name context.
	 * @return string|false Autoload file path or false.
	 */
	function get_autoload_file($class_name = false) {
		if (!$this->basepath || !$this->autoloadfile) {
			return false;
		}
		return $this->basepath . "/" . $this->autoloadfile;
	}
	
	/**
	 * Gets the relative path for a class in sub-directory mode.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Relative path or false.
	 */
	function get_class_rel_path_sub_dir_mode($class_name) {
		if (!$p = $this->className2SubDirBySep($class_name)) {
			return false;
		}
		return $p . ".php";
	}
	
	/**
	 * Converts class name to subdirectory path using separator.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Directory path or false.
	 */
	function className2SubDirBySep($class_name) {
		if (!$classnons = $this->get_class_name_no_ns($class_name)) {
			return false;
		}
		$a = explode($this->classdirsep, $classnons);
		return implode(DIRECTORY_SEPARATOR, $a);
	}
	
	/**
	 * Gets the class name without namespace.
	 * 
	 * @param string $class_name Full class name.
	 * @return string|false Class name without namespace or false.
	 */
	function get_class_name_no_ns($class_name) {
		// Already validated that class belongs to this manager
		if (!$class_name) {
			return false;
		}
		if (!$prefix = $this->namespace) {
			return $class_name;
		}
		if ($subcod = substr($class_name, strlen($prefix) + 1)) {
			return $subcod;
		}
		return false;
	}
	
	/**
	 * Gets the class info file path.
	 * 
	 * @param string|false $class_name Optional class name context.
	 * @return string|false Class info file path or false.
	 */
	function get_class_info_file($class_name = false) {
		if (!$this->basepath || !$this->classinfofile) {
			return false;
		}
		return $this->basepath . "/" . $this->classinfofile;
	}
	
	/**
	 * Gets class information.
	 * 
	 * @param string $class_name Class name.
	 * @param array &$info Reference to info array.
	 * @return array Class information.
	 */
	function get_class_info($class_name, &$info = []) {
		if (!$info) {
			$info = [];
		}
		$info["className"] = $class_name;
		$info["autoloader"] = get_class($this);
		$info["basepath"] = $this->basepath;
		$info["realPref"] = $this->realPref;
		$info["autoloaderPref"] = $this->getFullPref();
		$info["classOK"] = $this->specialCheckClassName($class_name);
		$info["autoloaderFile"] = $this->get_autoload_file($class_name);
		$info["classInfoFile"] = $this->get_class_info_file($class_name);
		$info["infofromscript"] = $this->get_class_info_from_file($class_name);
		return $info;
	}
	
	/**
	 * Gets class information from an external file.
	 * 
	 * @param string $class_name Class name.
	 * @return array Class info from file.
	 */
	function get_class_info_from_file($class_name): array {
		$info = [];
		$_file = $this->get_class_info_file($class_name);
		
		if ($_file && is_file($_file) && file_exists($_file) && is_readable($_file)) {
			include $_file;
		}
		
		return $info;
	}
	
	/**
	 * Performs autoload for a class.
	 * 
	 * @param string $class_name Full class name.
	 * @param array $class_parts Parsed class name parts.
	 * @param array &$report Reference to autoload report.
	 * @return bool|void True on success, false on failure.
	 */
	function do_autoload($class_name, $class_parts, &$report = []) {
		if (class_exists($class_name, false)) {
			return;
		}
		if (!$this->specialCheckClassName($class_name)) {
			return;
		}
		
		if (!$file = $this->get_autoload_file($class_name)) {
			return false;
		}
		$report["file"] = $file;
		
		if (!is_file($file) || !file_exists($file) || !is_readable($file)) {
			return false;
		}
		
		$loadanotherfile = false;
		$finalfiletoload = false;
		
		include $file;
		
			return true;
	}
	
	/**
	 * Includes an autoload script file.
	 * 
	 * @param string $file File path (obfuscated variable name to avoid conflicts).
	 * @param string $class_name Class name being loaded.
	 * @param array &$report Reference to autoload report.
	 * @return void
	 */
	function includeAutoloadScript($file, $class_name, &$report = []): void {
		// Already validated
		require $file . "";
	}
	
	/**
	 * Requires an autoload script file.
	 * 
	 * @param string $file File path (obfuscated variable name to avoid conflicts).
	 * @param string|false $class_name Class name being loaded.
	 * @param bool $once Whether to use require_once.
	 * @return void
	 */
	function requireoautoloadscript($file, $class_name = false, $once = true): void {
		// Already validated
		if ($once) {
			require_once $file . "";
		} else {
			require $file . "";
		}
	}
	
	/**
	 * Sets the real prefix.
	 * 
	 * @param string $pref Prefix.
	 * @return string The set prefix.
	 */
	final function set_realPref($pref = ""): string {
		return $this->realPref = $pref . "";
	}
	
	/**
	 * Gets the real prefix (private accessor).
	 * 
	 * @return string Real prefix.
	 */
	final function __get_priv_realPref(): string {
		return $this->realPref;
	}
	
	/**
	 * Sets the namespace.
	 * 
	 * @param string|false $namespace Namespace or false.
	 * @return string|false The set namespace.
	 */
	final function set_namespace($namespace = false) {
		return $this->namespace = $namespace;
	}
	
	/**
	 * Gets the namespace (private accessor).
	 * 
	 * @return string|false Namespace.
	 */
	final function __get_priv_namespace() {
		return $this->namespace;
	}
	
	/**
	 * Gets the autoload filename (private accessor).
	 * 
	 * @return string Autoload filename.
	 */
	final function __get_priv_autoloadfile(): string {
		return $this->autoloadfile;
	}
	
	/**
	 * Sets the base path.
	 * 
	 * @param string $basepath Base path.
	 * @return string The set path.
	 */
	final function set_basepath($basepath): string {
		return $this->basepath = $basepath;
	}
	
	/**
	 * Gets the base path (private accessor).
	 * 
	 * @return string Base path.
	 */
	final function __get_priv_basepath(): string {
		return $this->basepath;
	}
}

?>