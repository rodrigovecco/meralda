<?php

/**
 * Sub-Prefix Manager for Autoloading.
 * 
 * Manages autoloading for classes with a specific sub-prefix (e.g., mwmod_subpref_*).
 * Each sub-prefix manager handles a module or component within a prefix namespace.
 */
class mw_autoload_subprefman extends mw_baseobj {
	
	/** @var string Sub-prefix identifier */
	private $pref;
	
	/** @var mw_autoload_manager Main autoload manager reference */
	private $mainman;
	
	/** @var string Base path for class files */
	private $basepath;
	
	/** @var mw_autoload_prefman Parent prefix manager */
	private $prefman;
	
	/** @var bool Whether base autoload has been executed */
	public $autoload_base_done;
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Sub-prefix identifier.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref, $basepath);
	}
	
	/**
	 * Gets class information.
	 * 
	 * @param string $class_name Class name.
	 * @return array Class information including sub-code, path, and file.
	 */
	function get_class_info($class_name): array {
		return [
			"class_name" => $class_name,
			"sub_cod" => $this->get_class_sub_code($class_name),
			"subpath" => $this->get_class_sub_path($class_name),
			"file" => $this->get_class_file_basename($class_name),
		];
	}
	
	/**
	 * Gets the sub-path for a class.
	 * 
	 * @param string $class_name Class name.
	 * @return false Always false for base class.
	 */
	function get_class_sub_path($class_name) {
		return false;
	}
	
	/**
	 * Gets the file basename for a class.
	 * 
	 * @param string $class_name Class name.
	 * @return false Always false for base class.
	 */
	function get_class_file_basename($class_name) {
		return false;
	}
	
	/**
	 * Extracts the sub-code from a class name.
	 * Removes the prefix and returns the remaining identifier.
	 * 
	 * @param string $class_name Class name.
	 * @return string|false Sub-code or false if invalid.
	 */
	function get_class_sub_code($class_name) {
		if (!$class_name || !is_string($class_name)) {
			return false;
		}
		
		$pref = $this->get_complete_class_pref() . "_";
		$len = strlen($pref);
		
		if (substr($class_name, 0, $len) != $pref) {
			return false;
		}
		
		return substr($class_name, $len);
	}
	
	/**
	 * Sets the parent prefix manager.
	 * 
	 * @param mw_autoload_prefman $man Parent prefix manager.
	 * @return void
	 */
	final function set_pref_man($man): void {
		$this->prefman = $man;
	}
	
	/**
	 * Gets the parent prefix manager.
	 * 
	 * @return mw_autoload_prefman Parent prefix manager.
	 */
	final function get_pref_man() {
		return $this->prefman;
	}
	
	/**
	 * Gets debug information.
	 * 
	 * @return array Debug info including class, basepath, and prefix.
	 */
	function get_debug_info(): array {
		return [
			"class" => get_class($this),
			"basepath" => $this->basepath,
			"classpref" => $this->get_info_class_pref(),
		];
	}
	
	/**
	 * Gets the class prefix for info/debug purposes.
	 * 
	 * @return string Complete class prefix.
	 */
	function get_info_class_pref(): string {
		return $this->get_complete_class_pref();
	}
	
	/**
	 * Performs autoload for a class.
	 * 
	 * @param string $cod Class code (after prefix).
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool True on success, false on failure.
	 */
	function do_autoload($cod, $class_name, &$report = []): bool {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		
		$this->do_autoload_base($report);
		$file = $this->basepath . "/$cod/class.php";
		$report["file"] = $file;
		
		if (file_exists($file)) {
			include_once $file;
		}
		return true;
	}
	
	/**
	 * Loads the base file for this sub-prefix (once).
	 * 
	 * @param array &$report Reference to autoload report.
	 * @return bool True when complete.
	 */
	function do_autoload_base(&$report = []): bool {
		if ($this->autoload_base_done) {
			return true;
		}
		$this->autoload_base_done = true;
		
		$file = $this->basepath . "/base.php";
		$report["basefile"] = $file;
		
		if (file_exists($file)) {
			include_once $file;
		}
		return true;
	}
	
	/**
	 * Gets the complete class prefix (prefix + sub-prefix).
	 * 
	 * @return string|false Complete prefix or false.
	 */
	function get_complete_class_pref() {
		if (!$pm = $this->get_pref_man()) {
			return false;
		}
		return $pm->pref . "_" . $this->pref;
	}
	
	/**
	 * Initializes the manager.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Sub-prefix identifier.
	 * @param string $basepath Base path for class files.
	 * @return void
	 */
	final function init($mainman, $pref, $basepath): void {
		$this->mainman = $mainman;
		$this->pref = $pref;
		$this->basepath = $basepath;
	}
	
	/**
	 * Gets the base path (private accessor).
	 * 
	 * @return string Base path.
	 */
	final function __get_priv_basepath(): string {
		return $this->basepath;
	}
	
	/**
	 * Gets the sub-prefix (private accessor).
	 * 
	 * @return string Sub-prefix.
	 */
	final function __get_priv_pref(): string {
		return $this->pref;
	}
	
	/**
	 * Gets the prefix manager (private accessor).
	 * 
	 * @return mw_autoload_prefman Prefix manager.
	 */
	final function __get_priv_prefman() {
		return $this->prefman;
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
	 * String representation.
	 * 
	 * @return string Complete class prefix.
	 */
	function __toString(): string {
		return $this->get_complete_class_pref() . "";
	}
}

/**
 * Sub-Prefix Manager with Subclass Support.
 * 
 * Extends base sub-prefix manager to support subclasses within modules.
 * Looks for class.php in main folder and subclass files in subclasses folder.
 */
class mw_autoload_subprefmanwithsubclass extends mw_autoload_subprefman {
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Sub-prefix identifier.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref, $basepath);
	}
	
	/**
	 * Loads the main class file for a module.
	 * 
	 * @param string $cod Class code.
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool|void True if class was loaded.
	 */
	function do_autoload_class($cod, $class_name, &$report = []) {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		
		$this->do_autoload_base($report);
		$file = $this->basepath . "/$cod/class.php";
		$report["classfile"] = $file;
		
		if (file_exists($file)) {
			include_once $file;
			if (class_exists($class_name, false)) {
				return true;
			}
		}
	}
	
	/**
	 * Loads a subclass file from the subclasses folder.
	 * 
	 * @param string $cod Class code.
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool|void True if subclass was loaded.
	 */
	function do_autoloadsubclass($cod, $class_name, &$report = []) {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		
		if ($subclasscode = $this->get_sub_class_code($class_name)) {
			$scpath = $this->basepath . "/$cod/subclasses";
			$report["subclasscode"] = $subclasscode;
			
			if (is_dir($scpath)) {
				$scfile = $scpath . "/" . $subclasscode . ".php";
				$report["subclassfile"] = $scfile;
				
				if (file_exists($scfile)) {
					include_once $scfile;
				}
				if (class_exists($class_name, false)) {
					return true;
				}
			}
		}
	}
	
	/**
	 * Performs autoload - tries main class then subclass.
	 * 
	 * @param string $cod Class code.
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool True on completion.
	 */
	function do_autoload($cod, $class_name, &$report = []): bool {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		
		$this->do_autoload_base($report);
		
		if ($this->do_autoload_class($cod, $class_name, $report)) {
			return true;
		}
		if ($this->do_autoloadsubclass($cod, $class_name, $report)) {
			return true;
		}
		
		return true;
	}
	
	/**
	 * Extracts the subclass code from a class name.
	 * 
	 * @param string $class_name Full class name.
	 * @return string|false Subclass code or false.
	 */
	function get_sub_class_code($class_name) {
		if (!$class_name || !is_string($class_name)) {
			return false;
		}
		
		$a = explode("_", $class_name);
		if (!isset($a[3]) || !$a[3]) {
			return false;
		}
		
		if ($r = basename($a[3])) {
			return $r;
		}
		return false;
	}
}

/**
 * Sub-Prefix Manager with Path-Based Subclass Support.
 * 
 * Extends subclass support to allow path-based subclass resolution
 * where underscores in class names map to directory structure.
 */
class mw_autoload_subprefmanwithsubclasspathbased extends mw_autoload_subprefmanwithsubclass {
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Sub-prefix identifier.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref, $basepath);
	}
	
	/**
	 * Performs autoload - tries class, subclass, then path-based.
	 * 
	 * @param string $cod Class code.
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool True on success.
	 */
	function do_autoload($cod, $class_name, &$report = []): bool {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		
		$this->do_autoload_base($report);
		
		if ($this->do_autoload_class($cod, $class_name, $report)) {
			return true;
		}
		if ($this->do_autoloadsubclass($cod, $class_name, $report)) {
			return true;
		}
		if ($this->do_autoloadsubclass_path_based($cod, $class_name, $report)) {
			return true;
		}
		
		return true;
	}
	
	/**
	 * Attempts to load a subclass using path-based resolution.
	 * 
	 * Converts underscores in class name to directory separators
	 * to locate the file in the subclasses folder hierarchy.
	 * 
	 * @param string $cod Class code.
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool|void True if subclass was loaded.
	 */
	function do_autoloadsubclass_path_based($cod, $class_name, &$report = []) {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		if (!$class_name = basename($class_name)) {
			return false;
		}
		if (!$complte_pref = $this->get_complete_class_pref()) {
			return false;
		}
		
		$complte_pref .= "_$cod";
		$report["complte_pref"] = $complte_pref;
		$l = strlen($complte_pref);
		
		if (substr($class_name, 0, $l) != $complte_pref) {
			return false;
		}
		if (!$classnopref = substr($class_name, $l + 1)) {
			return false;
		}
		
		$parts = explode("_", $classnopref);
		$filepath = implode("/", $parts);
		$file = $this->basepath . "/$cod/subclasses/" . $filepath . ".php";
		$report["pathbasedfile"] = $file;
		
		if (file_exists($file)) {
			include_once $file;
		}
		
		if (class_exists($class_name, false)) {
			return true;
		}
	}
}

/**
 * File-Based Sub-Prefix Manager.
 * 
 * Simpler autoloader that loads classes from individual files
 * named after the class code (e.g., classname.php).
 */
class mw_autoload_subprefmanfilebased extends mw_autoload_subprefman {
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Sub-prefix identifier.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref, $basepath);
	}
	
	/**
	 * Base autoload preparation (no-op for file-based loading).
	 * 
	 * @param array &$report Reference to autoload report.
	 * @return bool Always returns true.
	 */
	function do_autoload_base(&$report = []): bool {
		return true;
	}
	
	/**
	 * Performs file-based autoload.
	 * 
	 * Loads class from a single PHP file named after the class code.
	 * 
	 * @param string $cod Class code.
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool True on completion.
	 */
	function do_autoload($cod, $class_name, &$report = []): bool {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		
		$this->do_autoload_base($report);
		$file = $this->basepath . "/" . $cod . ".php";
		$report["file"] = $file;
		
		if (file_exists($file)) {
			include_once $file;
		}
		return true;
	}
}

/**
 * File-Based Sub-Prefix Manager with Subclass Support.
 * 
 * Extends file-based loading to support hierarchical subclass structures.
 * Converts underscores in class names to directory paths for loading.
 */
class mw_autoload_subprefmanfilebased_width_subclases extends mw_autoload_subprefmanfilebased {
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Sub-prefix identifier.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref, $basepath);
	}
	
	/**
	 * Gets the directory path for a subclass.
	 * 
	 * Extracts the sub-code and converts underscores to directory separators,
	 * returning the parent directory path.
	 * 
	 * @param string $class_name Full class name.
	 * @return string|false Directory path or false on failure.
	 */
	function get_class_sub_path($class_name) {
		if (!$sub_cod = $this->get_class_sub_code($class_name)) {
			return false;
		}
		
		$parts = explode("_", $sub_cod);
		$filepath = implode("/", $parts);
		
		return dirname($filepath);
	}
	
	/**
	 * Gets the filename for a subclass.
	 * 
	 * Extracts the sub-code and converts to a PHP filename.
	 * 
	 * @param string $class_name Full class name.
	 * @return string|false Filename with .php extension or false on failure.
	 */
	function get_class_file_basename($class_name) {
		if (!$sub_cod = $this->get_class_sub_code($class_name)) {
			return false;
		}
		
		$parts = explode("_", $sub_cod);
		$filepath = implode("/", $parts);
		
		return basename($filepath . ".php");
	}
	
	/**
	 * Base autoload preparation (no-op for this implementation).
	 * 
	 * @param array &$report Reference to autoload report.
	 * @return bool Always returns true.
	 */
	function do_autoload_base(&$report = []): bool {
		return true;
	}
	
	/**
	 * Performs path-based autoload for subclasses.
	 * 
	 * Converts class name parts to directory structure and loads
	 * the corresponding PHP file.
	 * 
	 * @param string $cod Class code.
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool True on completion.
	 */
	function do_autoload($cod, $class_name, &$report = []): bool {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		if (!$class_name = basename($class_name)) {
			return false;
		}
		
		$this->do_autoload_base($report);
		
		if (!$complte_pref = $this->get_complete_class_pref()) {
			return false;
		}
		
		$l = strlen($complte_pref);
		if (substr($class_name, 0, $l) != $complte_pref) {
			return false;
		}
		if (!$classnopref = substr($class_name, $l + 1)) {
			return false;
		}
		
		$parts = explode("_", $classnopref);
		$filepath = implode("/", $parts);
		$file = $this->basepath . "/" . $filepath . ".php";
		$report["file"] = $file;
		
		if (file_exists($file)) {
			include_once $file;
		}
		return true;
	}
}

/**
 * File-Based Sub-Prefix Manager with Alternative Folder Support.
 * 
 * Extends subclass file-based loading to support alternative and default folders.
 * Checks the alternative folder first, then falls back to the default folder.
 * Useful for customization and override scenarios.
 */
class mw_autoload_subprefmanfilebased_width_subclases_alt extends mw_autoload_subprefmanfilebased_width_subclases {
	
	/**
	 * Alternative folder name for customized class files.
	 * 
	 * @var string|null
	 */
	private $alt_folder;
	
	/**
	 * Default folder name for base class files.
	 * 
	 * @var string
	 */
	private $def_folder = "def";
	
	/**
	 * Constructor.
	 * 
	 * @param mw_autoload_manager $mainman Main autoload manager.
	 * @param string $pref Sub-prefix identifier.
	 * @param string $basepath Base path for class files.
	 */
	function __construct($mainman, $pref, $basepath) {
		$this->init($mainman, $pref, $basepath);
	}
	
	/**
	 * Sets the alternative folder for class loading.
	 * 
	 * The alternative folder is checked first before the default folder.
	 * The folder name is validated through the main autoload manager.
	 * 
	 * @param string $alt Alternative folder name.
	 * @return bool True on success, false if validation fails.
	 */
	final function set_alt($alt): bool {
		if (!$alt = $this->mainman->check_pref($alt)) {
			return false;
		}
		$this->alt_folder = $alt;
		return true;
	}
	
	/**
	 * Performs autoload with alternative folder fallback.
	 * 
	 * First attempts to load from the alternative folder (if set),
	 * then falls back to the default folder.
	 * 
	 * @param string $cod Class code.
	 * @param string $class_name Full class name.
	 * @param array &$report Reference to autoload report.
	 * @return bool True on completion.
	 */
	function do_autoload($cod, $class_name, &$report = []): bool {
		if (!$cod) {
			return false;
		}
		if (!$cod = basename($cod)) {
			return false;
		}
		if (!$class_name = basename($class_name)) {
			return false;
		}
		
		$this->do_autoload_base($report);
		
		if (!$complte_pref = $this->get_complete_class_pref()) {
			return false;
		}
		
		$l = strlen($complte_pref);
		if (substr($class_name, 0, $l) != $complte_pref) {
			return false;
		}
		if (!$classnopref = substr($class_name, $l + 1)) {
			return false;
		}
		
		$parts = explode("_", $classnopref);
		$filepath = implode("/", $parts);
		
		// Try alternative folder first
		if ($this->alt_folder) {
			$file = $this->basepath . "/" . $this->alt_folder . "/" . $filepath . ".php";
			$report["file"] = $file;
			$report["altfile"] = $file;
			
			if (file_exists($file)) {
				include_once $file;
				return true;
			}
		}
		
		// Fall back to default folder
		$file = $this->basepath . "/" . $this->def_folder . "/" . $filepath . ".php";
		$report["file"] = $file;
		
		if (file_exists($file)) {
			include_once $file;
		}
		return true;
	}
	
	/**
	 * Gets the private alternative folder name.
	 * 
	 * @return string|null Alternative folder name or null.
	 */
	final function __get_priv_alt_folder(): ?string {
		return $this->alt_folder;
	}
	
	/**
	 * Gets the private default folder name.
	 * 
	 * @return string Default folder name.
	 */
	final function __get_priv_def_folder(): string {
		return $this->def_folder;
	}
}

?>