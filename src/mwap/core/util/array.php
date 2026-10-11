<?php
/**
 * Array Utility Functions.
 * 
 * Provides helper functions for array manipulation, conversion,
 * and nested key access with dot notation support.
 * 
 * @package meralda
 * @subpackage util
 * @since 2023-10-13
 */

/**
 * Recursively converts an object to an array.
 * 
 * Traverses nested objects and arrays, converting all objects
 * to their array representation using get_object_vars().
 * 
 * @deprecated 2026-10-10 No framework caller remains. Convert objects
 *             locally with get_object_vars() instead (see
 *             mwmod_mw_mail_ui_debug_sendmail::get_php_mailer_info()).
 * 
 * @param mixed $data Object, array, or scalar value to convert.
 * @return mixed Converted array or original scalar value.
 */
function mw_convert_object_to_array($data) {
    if (is_object($data)) {
        $data = get_object_vars($data);
    }

    if (is_array($data)) {
        return array_map(__FUNCTION__, $data);
    } else {
        return $data;
    }
}

/**
 * Gets a value from a nested array using dot notation.
 * 
 * Supports accessing nested values using keys separated by dots.
 * Example: mw_array_get_sub_key($arr, "user.profile.name")
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_array::get_sub_key()
 *             in the mw modules submodule. This shim only delegates; it is
 *             kept for backwards compatibility and slated for removal.
 * 
 * @param array|mw_object_as_array $a Source array or object.
 * @param string $key Dot-separated key path (e.g., "parent.child.value").
 * @return mixed Value at the specified path, or false if not found.
 */
function mw_array_get_sub_key($a, $key = "") {
	return mwmod_mw_util_array::get_sub_key($a, $key);
}

/**
 * Sets a value in a nested array using dot notation.
 * 
 * Creates intermediate arrays as needed. Supports mw_object_as_array objects.
 * Example: mw_array_set_sub_key("user.profile.name", "John", $arr)
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_array::set_sub_key()
 *             in the mw modules submodule. This shim only delegates; it is
 *             kept for backwards compatibility and slated for removal.
 * 
 * @param string|int $key Dot-separated key path.
 * @param mixed $val Value to set.
 * @param array|mw_object_as_array &$a Reference to target array or object.
 * @param bool $reemplazarsinoesarray Whether to replace non-array values with arrays.
 * @return array|false Modified array or false on failure.
 */
function mw_array_set_sub_key($key, $val, &$a, $reemplazarsinoesarray = true) {
	return mwmod_mw_util_array::set_sub_key($key, $val, $a, $reemplazarsinoesarray);
}

/**
 * Checks if an object is an allowed mw_object_as_array instance.
 * 
 * Verifies the object is a subclass of mw_object_as_array and
 * has array access enabled.
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_array::is_obj_array_allowed()
 *             in the mw modules submodule. This shim only delegates; it is
 *             kept for backwards compatibility and slated for removal.
 * 
 * @param mixed $obj Object to check.
 * @return bool True if object is an allowed array-like object.
 */
function mw_array_is_obj_mw_array_allowed($obj): bool {
	return mwmod_mw_util_array::is_obj_array_allowed($obj);
}
/**
 * Converts an array to an HTML unordered list.
 * 
 * Recursively renders nested arrays as nested lists. Supports
 * custom callback functions for rendering.
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_array::to_list()
 *             in the mw modules submodule. This shim only delegates; it is
 *             kept for backwards compatibility and slated for removal.
 * 
 * @param mixed $data Array, object, or scalar data to render.
 * @param array|null $parametros Rendering parameters:
 *        - 'nuevo': bool - Start new list wrapper.
 *        - 'nombre': string - Label for this item.
 *        - 'fnc_si_array': callable - Custom function for arrays.
 *        - 'fnc_si_dato': callable - Custom function for scalar values.
 * @return string HTML list markup.
 */
function mw_array2list($data, $parametros = null): string {
	if (!is_array($parametros)) {
		$parametros = [];
	}
	$params = [];
	$params["new"] = !empty($parametros["nuevo"]);
	$params["name"] = $parametros["nombre"] ?? "";
	if (!empty($parametros["fnc_si_array"])) {
		$params["fnc_if_array"] = $parametros["fnc_si_array"];
	}
	if (!empty($parametros["fnc_si_dato"])) {
		$params["fnc_if_value"] = $parametros["fnc_si_dato"];
	}
	return mwmod_mw_util_array::to_list($data, $params);
}

/**
 * Converts a PHP value to JavaScript literal syntax.
 * 
 * Handles arrays (to objects), strings, numbers, booleans, nulls,
 * and mw_object_as_array instances.
 * 
 * @deprecated 2026-10-10 No framework caller remains. Kept only for
 *             backwards compatibility; slated for removal.
 * 
 * @param mixed $data PHP value to convert.
 * @param bool $addbrline Whether to add line breaks between object properties.
 * @return string JavaScript literal representation.
 */
function mw_array2jsval($data, $addbrline = false): string {
	if (is_numeric($data)) {
		if ($data == ($data + 0)) {
			return (string)($data + 0);
		}
		return "'" . mw_text_nl_js($data) . "'";
	}
	
	if (is_null($data)) {
		return "null";
	}
	
	if (is_bool($data)) {
		return $data ? "true" : "false";
	}
	
	if (is_object($data)) {
		$obj = $data;
		if ($obj) {
			if (mw_array_is_obj_mw_array_allowed($obj)) {
				if (method_exists($obj, 'get_as_js_val')) {
					return $obj->get_as_js_val();
				}
				return mw_array2jsval($obj->get_props_as_array_or_str());
			} elseif (method_exists($obj, '__toString')) {
				return "'" . $obj . "'";
			} else {
				return "''";
			}
		} else {
			return "null";
		}
	}
	
	if (is_string($data)) {
		return "'" . mw_text_nl_js($data) . "'";
	}
	
	if (!is_array($data)) {
		return "null";
	}
	
	$list = [];
	foreach ($data as $k => $v) {
		if ($cod = mw_text_js_check_obj_cod($k)) {
			$list[] = $cod . ":" . mw_array2jsval($v, $addbrline);
		}
	}
	
	$r = "{";
	$sep = $addbrline ? ",\n" : ",";
	$r .= implode($sep, $list);
	$r .= "}";
	
	if ($addbrline) {
		$r .= "\n";
	}
	
	return $r;
}

/**
 * Converts an object to array or string representation.
 * 
 * Handles booleans, mw_object_as_array instances, and objects
 * with __toString method.
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_array::get_obj_as_array_or_str()
 *             in the mw modules submodule. This shim only delegates; it is
 *             kept for backwards compatibility and slated for removal.
 * 
 * @param mixed $data Value to convert.
 * @return mixed Array, string representation, or original value.
 */
function mw_array_get_obj_as_array_or_str($data) {
	return mwmod_mw_util_array::get_obj_as_array_or_str($data);
}
/**
 * Abstract base class for objects that can be used as arrays.
 * 
 * Provides array-like access to object properties with dot notation support.
 * Subclasses can control read/write access through method overrides.
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_objectasarray in the
 *             mw modules submodule. Kept as the legacy ancestor so that
 *             existing subclasses and type checks (is_subclass_of /
 *             instanceof mw_object_as_array) keep working; new code must
 *             extend mwmod_mw_util_objectasarray instead. Slated for removal.
 */
abstract class mw_object_as_array {
	
	/** @var mixed Object properties storage */
	public $props;
	
	/**
	 * Determines if this object allows array-like access.
	 * 
	 * Override in subclasses to enable array operations.
	 * 
	 * @return bool True if array access is allowed.
	 */
	function __mw_array_allow_use_this_object() {
		return false;
	}
	
	/**
	 * Determines if this object allows write operations.
	 * 
	 * @return bool True if write access is allowed.
	 */
	function __mw_array_allow_write() {
		return $this->__mw_array_allow_use_this_object();
	}
	
	/**
	 * Sets all properties.
	 * 
	 * @param mixed $val Properties value.
	 * @return void
	 */
	function set_props($val) {
		$this->props = $val;
	}
	
	/**
	 * Gets all properties.
	 * 
	 * @return mixed Properties value or null.
	 */
	function get_props() {
		return $this->props ?? null;
	}
	
	/**
	 * Gets a property using dot notation.
	 * 
	 * @param string|false $key Dot-separated key path.
	 * @return mixed Value at the specified path.
	 */
	function get_prop_from_key_dot($key = false) {
		$a = $this->get_props();
		if (!$key) {
			return $a;
		}
		return mw_array_get_sub_key($a, $key);
	}
	
	/**
	 * Gets properties as array or string.
	 * 
	 * @return mixed Properties converted to array or string.
	 */
	function get_props_as_array_or_str() {
		$r = $this->get_props();
		return mw_array_get_obj_as_array_or_str($r);
	}
	
	/**
	 * String representation of the object.
	 * 
	 * @return string Class name.
	 */
	function __toString(): string {
		return get_class($this);
	}
	
	/**
	 * Sets a property via mw_array_set_sub_key if write is allowed.
	 * 
	 * @param string $key Dot-separated key path.
	 * @param mixed $val Value to set.
	 * @return mixed Result of set operation or false.
	 */
	function set_prop_from_key_dot_if_allowed_from_mw_array_set_sub_key($key, $val) {
		if (!$this->__mw_array_allow_write()) {
			return false;
		}
		return $this->set_prop_from_key_dot($key, $val);
	}
	
	/**
	 * Sets all properties via mw_array_set_sub_key if write is allowed.
	 * 
	 * @param mixed $val Value to set.
	 * @return mixed Result of set operation or false.
	 */
	function set_all_prop_from_mw_array_set_sub_key($val) {
		if (!$this->__mw_array_allow_write()) {
			return false;
		}
		return $this->set_props($val);
	}
	
	/**
	 * Sets a property using dot notation.
	 * 
	 * @param string $key Dot-separated key path.
	 * @param mixed $val Value to set.
	 * @return bool True on success.
	 */
	function set_prop_from_key_dot($key, $val) {
		if (!$key) {
			return false;
		}
		
		if (!isset($this->props) || !is_array($this->props)) {
			$this->props = [];
		}
		
		mw_array_set_sub_key($key, $val, $this->props);
		return true;
	}
}


/**
 * Outputs an array as an HTML list with optional echo.
 * 
 * Wraps mw_array2list() with a title and container div.
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_array::to_list_echo()
 *             in the mw modules submodule. This shim only delegates; it is
 *             kept for backwards compatibility and slated for removal.
 * 
 * @param mixed $data Array or data to render.
 * @param string $titulo Title/label for the list.
 * @param bool $echo Whether to echo the output.
 * @return string HTML markup.
 */
function mw_array2list_echo($data, $titulo = "DATA", $echo = true) {
	return mwmod_mw_util_array::to_list_echo($data, $titulo, $echo);
}

/**
 * Converts an array to a JavaScript alert-safe string.
 * 
 * Escapes special characters and joins array elements with newlines.
 * 
 * @deprecated 2026-10-11 Sole caller was mw_text_js_msgalert() (also deprecated).
 *             No framework caller remains; slated for removal.
 * 
 * @param mixed $data Array or string to convert.
 * @return string Escaped string for JavaScript alert.
 */
function mw_array2jsalert($data) {
	if (is_array($data)) {
		foreach ($data as $k => $v) {
			$data[$k] = addslashes($v);
		}
		return implode("\\n", $data);
	} else {
		return addslashes($data);
	}
}

/**
 * Converts an array to PHP assignment statements.
 * 
 * Generates PHP code that reconstructs the array structure.
 * 
 * @deprecated 2026-10-10 No framework caller remains. Kept only for
 *             backwards compatibility; slated for removal.
 * 
 * @param mixed $data Array or value to convert.
 * @param string $array_name Base variable name for assignments.
 * @return string PHP code with array assignments.
 */
function mw_array2php($data, $array_name = '$datos') {
	$r = "";
	
	if (is_array($data)) {
		foreach ($data as $k => $v) {
			$r .= mw_array2php($v, $array_name . "['" . $k . "']");
		}
	} else {
		$r = $array_name . "=\"" . addslashes($data) . "\";\n";
	}
	
	return $r;
}

/**
 * Converts an array to URL query string format.
 * 
 * Handles nested arrays with bracket notation.
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_array::to_url_query()
 *             in the mw modules submodule. Kept only for backwards
 *             compatibility; slated for removal.
 * 
 * @param array $data Array to convert.
 * @return string|false URL query string or false on failure.
 */
function mw_array2urlquery($data) {
	if (!is_array($data)) {
		return false;
	}
	
	$args = [];
	mw_array2urlarg($args, $data, "");
	
	if (!is_array($args) || !sizeof($args)) {
		return false;
	}
	
	$a = [];
	foreach ($args as $k => $v) {
		if(!is_null($v)) {
			$v = (string) $v;
			$a[] = $k . "=" . urlencode($v);
		}
	}
	
	return implode("&", $a);
}

/**
 * Recursively builds URL arguments from nested array.
 * 
 * Helper function for mw_array2urlquery().
 * 
 * @deprecated 2026-10-11 Only used by mw_array2urlquery() (also deprecated).
 *             Its logic now lives in mwmod_mw_util_array::to_url_query().
 * 
 * @param array &$arg Reference to arguments array.
 * @param mixed $data Current data to process.
 * @param string|false $array_name Current key path.
 * @return array Arguments array.
 */
function mw_array2urlarg(&$arg, $data, $array_name = false) {
	if (is_array($data)) {
		foreach ($data as $k => $v) {
			if ($array_name) {
				$naname = $array_name . "[" . $k . "]";
			} else {
				$naname = $k;
			}
			mw_array2urlarg($arg, $v, $naname);
		}
	} else {
		if ($array_name) {
			$arg[$array_name] = $data;
		}
	}
	
	return $arg;
}

/**
 * Gets the XML data type string for a PHP value.
 * 
 * Used for XML serialization to preserve type information.
 * Corresponds to the JS function fp_ajax_xml2obj_item.
 * 
 * @deprecated 2026-10-10 No framework caller remains. Kept only for
 *             backwards compatibility; slated for removal.
 * 
 * @param mixed $data Value to check.
 * @return string Data type: "Object", "Bool", "Int", "Numeric", or "String".
 */
function mw_array2xml_str_data_type($data): string {
	if (is_array($data)) {
		return "Object";
	}
	if (is_bool($data)) {
		return "Bool";
	}
	if (is_numeric($data)) {
		if (is_int($data)) {
			return "Int";
		}
		return "Numeric";
	}
	return "String";
}

/**
 * Parses a value for XML node content.
 * 
 * Handles booleans, nulls, numbers, and wraps special characters
 * in CDATA sections when needed.
 * 
 * @deprecated 2026-10-10 No framework caller remains. Kept only for
 *             backwards compatibility; slated for removal.
 * 
 * @param mixed $val Value to parse.
 * @return mixed Formatted value for XML output.
 */
function mw_array2xml_parse_node_string_value($val) {
	if (is_bool($val)) {
		return $val ? 1 : 0;
	}
	
	if (is_null($val)) {
		return null;
	}

	if (is_numeric($val)) {
		return $val;
	}
	
	$cdata = false;
	if (strpos($val, "<") !== false) {
		$cdata = true;
	}
	if (strpos($val, ">") !== false) {
		$cdata = true;
	}
	if (strpos($val, "&") !== false) {
		$cdata = true;
	}
	
	if ($cdata) {
		$r = "<![CDATA[";
		$r .= mw_str_parse_cdata($val);
		$r .= "]]>";
		return $r;
	}
	
	return $val;
}

/**
 * Merges array with default values.
 * 
 * Recursively applies default values where the data is null or missing.
 * 
 * @deprecated 2026-10-11 Superseded by mwmod_mw_util_array::by_default()
 *             in the mw modules submodule. Kept only for backwards
 *             compatibility; slated for removal.
 * 
 * @param mixed $datos Source data array.
 * @param mixed $pordefecto Default values to apply.
 * @param bool $strvacioaNULL Treat empty strings as null.
 * @return mixed Merged data with defaults applied.
 */
function mw_array_bydefault($datos, $pordefecto, $strvacioaNULL = false) {
	if (is_object($datos)) {
		return $datos;
	} elseif (is_object($pordefecto)) {
		return $pordefecto;
	}
	
	if ($strvacioaNULL) {
		if (is_string($datos) && strlen($datos) <= 0) {
			$datos = null;
		}
	}
	
	if (is_array($pordefecto)) {
		if (is_array($datos) || is_null($datos)) {
			foreach ($pordefecto as $key => $val) {
				$datos[$key] = mw_array_bydefault($datos[$key] ?? null, $val, $strvacioaNULL);
			}
		}
	} elseif (!is_null($pordefecto) && is_null($datos)) {
		$datos = $pordefecto;
	}
	
	return $datos;
}

?>