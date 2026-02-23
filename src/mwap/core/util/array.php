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
 * @param array|mw_object_as_array $a Source array or object.
 * @param string $key Dot-separated key path (e.g., "parent.child.value").
 * @return mixed Value at the specified path, or false if not found.
 */
function mw_array_get_sub_key($a, $key = "") {
	if (mw_array_is_obj_mw_array_allowed($a)) {
		return $a->get_prop_from_key_dot($key);
	}
	if (!$key) {
		return $a;
	}

	$val = $a;
	$keys1 = explode(".", $key);
	$keys = [];
	$keysleft = [];
	
	foreach ($keys1 as $k) {
		if ($k) {
			$keys[] = $k;
			$keysleft[] = $k;
		}
	}
	
	if (sizeof($keys) == 0) {
		return $val;
	}
	
	foreach ($keys as $k) {
		if (mw_array_is_obj_mw_array_allowed($val)) {
			return $val->get_prop_from_key_dot(implode(".", $keysleft));
		}

		if (!is_array($val)) {
			return false;
		}
		
		array_shift($keysleft);
		
		if (isset($val[$k])) {
			$val = $val[$k];
		} else {
			$val = null;
		}
	}
	
	return $val;
}

/**
 * Sets a value in a nested array using dot notation.
 * 
 * Creates intermediate arrays as needed. Supports mw_object_as_array objects.
 * Example: mw_array_set_sub_key("user.profile.name", "John", $arr)
 * 
 * @param string|int $key Dot-separated key path.
 * @param mixed $val Value to set.
 * @param array|mw_object_as_array &$a Reference to target array or object.
 * @param bool $reemplazarsinoesarray Whether to replace non-array values with arrays.
 * @return array|false Modified array or false on failure.
 */
function mw_array_set_sub_key($key, $val, &$a, $reemplazarsinoesarray = true) {
	if (!$key) {
		return false;
	}
	
	if (mw_array_is_obj_mw_array_allowed($a)) {
		return $a->set_prop_from_key_dot_if_allowed_from_mw_array_set_sub_key($key, $val);
	}

	if (!is_array($a)) {
		$a = [];
	}
	
	if (!is_string($key) && !is_numeric($key)) {
		return false;
	}

	$keys1 = explode(".", $key);
	$keys = [];
	$keysleft = [];
	
	foreach ($keys1 as $k) {
		if ($k) {
			$keys[] = $k;
			$keysleft[] = $k;
		}
	}

	if (sizeof($keys) == 0) {
		return false;
	}
	
	$lastkey = array_pop($keys);
	$cambiar = &$a;

	if (sizeof($keys) > 0) {
		foreach ($keys as $k) {
			array_shift($keysleft);
			
			if (!isset($cambiar[$k])) {
				$cambiar[$k] = [];
			}
			
			if (mw_array_is_obj_mw_array_allowed($cambiar[$k])) {
				return $cambiar[$k]->set_prop_from_key_dot_if_allowed_from_mw_array_set_sub_key(implode(".", $keysleft), $val);
			}
			
			if (!is_array($cambiar[$k])) {
				if ($reemplazarsinoesarray) {
					$cambiar[$k] = [];
				} else {
					return false;
				}
			}
			
			$cambiar1 = &$cambiar[$k];
			$cambiar = &$cambiar1;
		}
	}
	
	if (isset($cambiar[$lastkey])) {
		if (mw_array_is_obj_mw_array_allowed($cambiar[$lastkey])) {
			return $cambiar[$lastkey]->set_all_prop_from_mw_array_set_sub_key($val);
		}
	}

	$cambiar[$lastkey] = $val;
	return $a;
}

/**
 * Checks if an object is an allowed mw_object_as_array instance.
 * 
 * Verifies the object is a subclass of mw_object_as_array and
 * has array access enabled.
 * 
 * @param mixed $obj Object to check.
 * @return bool True if object is an allowed array-like object.
 */
function mw_array_is_obj_mw_array_allowed($obj): bool {
	if (!$obj) {
		return false;
	}
	
	if (is_object($obj)) {
		if (is_subclass_of($obj, "mw_object_as_array")) {
			return $obj->__mw_array_allow_use_this_object();
		}
	}
	
	return false;
}
/**
 * Converts an array to an HTML unordered list.
 * 
 * Recursively renders nested arrays as nested lists. Supports
 * custom callback functions for rendering.
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
	$r = "";
	
	if (!is_array($parametros)) {
		$parametros = [];
	}
	
	if (!empty($parametros["nuevo"])) {
		$r .= "<ul>";
	}
	
	if (is_object($data)) {
		$obj = $data;
		if ($obj) {
			if (mw_array_is_obj_mw_array_allowed($obj)) {
				$data = $obj->get_props_as_array_or_str();
			} elseif (method_exists($obj, '__toString')) {
				$data = $obj . " [" . get_class($obj) . "]";
			} else {
				$data = "[" . get_class($obj) . "]";
			}
		} else {
			$data = "NULL";
		}
	}
	
	if (is_bool($data)) {
		$data = $data ? "true" : "false";
	}
	
	$fnc_si_array = false;
	if (isset($parametros["fnc_si_array"]) && $parametros["fnc_si_array"]) {
		if (is_string($parametros["fnc_si_array"])) {
			if (function_exists($parametros["fnc_si_array"])) {
				$fnc_si_array = $parametros["fnc_si_array"];
			}
		}
	}
	
	$fnc_si_dato = false;
	if (isset($parametros["fnc_si_dato"]) && $parametros["fnc_si_dato"]) {
		if (is_string($parametros["fnc_si_dato"])) {
			if (function_exists($parametros["fnc_si_dato"])) {
				$fnc_si_dato = $parametros["fnc_si_dato"];
			}
		}
	}

	if (is_array($data)) {
		if ($fnc_si_array) {
			$r .= call_user_func($fnc_si_array, $data, $parametros);
		} else {
			$oncl = "";
			$r .= "<li><div $oncl>";
			$r .= $parametros["nombre"] ?? "";
			$r .= "</div>";
			$r .= "<ul>";

			foreach ($data as $key => $val) {
				$parametros_n = $parametros;
				$parametros_n["nuevo"] = 0;
				$parametros_n["nombre"] = $key;
				$r .= mw_array2list($val, $parametros_n);
			}
			
			$r .= "</ul>";
			$r .= "</li>";
		}
	} else {
		if ($fnc_si_dato) {
			$r .= call_user_func($fnc_si_dato, $data, $parametros);
		} else {
			$r .= "<li>";
			$r .= ($parametros["nombre"] ?? "") . " == > " . $data;
			$r .= "</li>";
		}
	}

	if (isset($parametros["nuevo"]) && $parametros["nuevo"]) {
		$r .= "</ul>";
	}
	
	return $r;
}

/**
 * Converts a PHP value to JavaScript literal syntax.
 * 
 * Handles arrays (to objects), strings, numbers, booleans, nulls,
 * and mw_object_as_array instances.
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
 * @param mixed $data Value to convert.
 * @return mixed Array, string representation, or original value.
 */
function mw_array_get_obj_as_array_or_str($data) {
	if (is_bool($data)) {
		return $data ? "true" : "false";
	}

	if (is_object($data)) {
		$obj = $data;
		if ($obj) {
			if (mw_array_is_obj_mw_array_allowed($obj)) {
				$data = $obj->get_props_as_array_or_str();
			} elseif (method_exists($obj, '__toString')) {
				$data = $obj . "";
			} else {
				$data = "";
			}
		} else {
			$data = "NULL";
		}
	}
	
	if (is_bool($data)) {
		$data = $data ? "true" : "false";
	}
	
	return $data;
}
/**
 * Abstract base class for objects that can be used as arrays.
 * 
 * Provides array-like access to object properties with dot notation support.
 * Subclasses can control read/write access through method overrides.
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
 * @param mixed $data Array or data to render.
 * @param string $titulo Title/label for the list.
 * @param bool $echo Whether to echo the output.
 * @return string HTML markup.
 */
function mw_array2list_echo($data, $titulo = "DATA", $echo = true) {
	$parametros["nuevo"] = true;
	$parametros["expandible"] = true;
	$parametros["nombre"] = $titulo;

	$html = "<div align='left'>";
	$html .= mw_array2list($data, $parametros);
	$html .= "</div>";
	
	if ($echo) {
		echo $html;
	}
	
	return $html;
}

/**
 * Converts an array to a JavaScript alert-safe string.
 * 
 * Escapes special characters and joins array elements with newlines.
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
		$a[] = $k . "=" . urlencode($v);
	}
	
	return implode("&", $a);
}

/**
 * Recursively builds URL arguments from nested array.
 * 
 * Helper function for mw_array2urlquery().
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
		if (!is_array($datos)) {
			if (strlen($datos) <= 0) {
				$datos = null;
			}
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