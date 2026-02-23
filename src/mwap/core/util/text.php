<?php
//20260119

/**
 * Converts a value to a number if it is numeric.
 * 
 * @param mixed $val Value to convert.
 * @return int|float|false Returns the number or false if not numeric.
 */
function mw_get_number(mixed $val): int|float|false {
	if (is_numeric($val)) {
		return $val + 0;
	}
	return false;
}

/**
 * Converts a value to string if it is numeric or string.
 * 
 * @param mixed $val Value to convert.
 * @return string|false Returns the string or false if not convertible.
 */
function mw_get_string(mixed $val): string|false {
	if (is_numeric($val) || is_string($val)) {
		return (string) $val;
	}
	return false;
}

/**
 * Escapes special characters for use in JavaScript.
 * 
 * @param string $str String to escape.
 * @return string Escaped string.
 */
function mw_text_nl_js(string $str): string {
	return addcslashes($str, "\\\'\"&\n\r<>");
}

/**
 * Validates that a string is a valid JS object code (without special characters).
 * 
 * @param mixed $str Value to validate.
 * @return string|false Returns the string if valid, false if it contains disallowed characters.
 */
function mw_text_js_check_obj_cod(mixed $str): string|false {
	$str = (string) $str;
	
	if ($str === '') {
		return false;
	}
	
	// Characters not allowed in JS object codes
	$invalidChars = [',', '.', '=', ':', ' '];
	
	foreach ($invalidChars as $char) {
		if (str_contains($str, $char)) {
			return false;
		}
	}
	
	return $str;
}

/**
 * Replaces accented and special characters with ASCII equivalents.
 * 
 * @param string $str String to process.
 * @param string|false $mode Operation mode:
 *   - false: normal for filenames.
 *   - true: for sorting.
 *   - 'upper': uppercase.
 *   - 'lower': lowercase.
 *   - 'upperindex': uppercase without accents for directories.
 * @param string|false $nonalphaandnotlistedto Replacement character for non-alphanumeric.
 * @param bool $returnchtbl If true, returns the character table.
 * @return string|array Processed string or character table.
 */
function mw_text_replace_lngchars(
	string $str, 
	string|bool $mode = false, 
	string|false $nonalphaandnotlistedto = false, 
	bool $returnchtbl = false
): string|array {
	// Accented character tables
	$ch = [
		'A' => ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å'],
		'a' => ['à', 'á', 'â', 'ã', 'ä', 'å'],
		'E' => ['È', 'É', 'Ê', 'Ë'],
		'e' => ['è', 'é', 'ê', 'ë'],
		'f' => ['ƒ'],
		'I' => ['Ì', 'Í', 'Î', 'Ï'],
		'i' => ['ì', 'í', 'î', 'ï'],
		'O' => ['Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø'],
		'o' => ['ò', 'ó', 'ô', 'õ', 'ö', 'ø'],
		'S' => ['Š'],
		's' => ['š'],
		'U' => ['Ù', 'Ú', 'Û', 'Ü'],
		'u' => ['ù', 'ú', 'û', 'ü'],
		'y' => ['ý', 'ÿ'],
		'Y' => ['Ý', 'Ÿ'],
		'Z' => ['Ž'],
		'z' => ['ž']
	];
	
	$vocales = ['a', 'e', 'i', 'o', 'u'];
	$otrascontilde = ['y', 'z', 'S'];
	
	$speciales = [
		'AE' => ['Æ'],
		'ae' => ['æ'],
		'OE' => ['Œ'],
		'oe' => ['œ'],
		'ss' => ['ß']
	];
	
	$speciales2upper = [
		'Æ' => ['æ'],
		'Œ' => ['œ'],
		'Ñ' => ['ñ'],
		'Ð' => ['ð'],
		'Ç' => ['ç']
	];
	
	$speciales2lower = [
		'æ' => ['Æ'],
		'œ' => ['Œ'],
		'ñ' => ['Ñ'],
		'ð' => ['Ð'],
		'ç' => ['Ç']
	];
	
	// Apply transformations according to mode
	switch ($mode) {
		case 'upper':
			$vocalesext = array_merge($vocales, $otrascontilde);
			foreach ($vocalesext as $vocal) {
				if ($min = $ch[$vocal] ?? null) {
					if ($may = $ch[strtoupper($vocal)] ?? null) {
						foreach ($min as $i => $minL) {
							$mayL = $may[$i];
							$ch[$mayL] = [$minL];
						}
					}
					unset($ch[$vocal], $ch[strtoupper($vocal)]);
				}
			}
			$ch = array_merge($ch, $speciales2upper);
			$ch['SS'] = ['ß'];
			break;
			
		case 'lower':
			$vocalesext = array_merge($vocales, $otrascontilde);
			foreach ($vocalesext as $vocal) {
				if ($min = $ch[$vocal] ?? null) {
					if ($may = $ch[strtoupper($vocal)] ?? null) {
						foreach ($min as $i => $minL) {
							$mayL = $may[$i];
							$ch[$minL] = [$mayL];
						}
					}
					unset($ch[$vocal], $ch[strtoupper($vocal)]);
				}
			}
			$ch = array_merge($ch, $speciales2lower);
			break;
			
		case 'upperindex':
			foreach ($vocales as $vocal) {
				$may = strtoupper($vocal);
				$ch[$may] = array_merge($ch[$may] ?? [], $ch[$vocal] ?? []);
				unset($ch[$vocal]);
			}
			$ch = array_merge($ch, $speciales2upper);
			break;
			
		default:
			$ch = array_merge($ch, $speciales);
			if ($mode === true) {
				// Sorting mode
				$ch['Dz'] = ['Ð'];
				$ch['dz'] = ['ð'];
				$ch['Nz'] = ['Ñ'];
				$ch['nz'] = ['ñ'];
				$ch['Zz'] = ['Ç'];
				$ch['zz'] = ['ç'];
			} else {
				// Normal mode
				$ch['D'] = ['Ð'];
				$ch['d'] = ['ð'];
				$ch['N'] = ['Ñ'];
				$ch['n'] = ['ñ'];
				$ch['c'] = ['Ç', 'ç'];
			}
			break;
	}
	
	if ($returnchtbl) {
		return $ch;
	}
	
	// Perform replacements
	foreach ($ch as $replacement => $originals) {
		foreach ($originals as $original) {
			$str = str_replace($original, $replacement, $str);
		}
	}
	
	// Transform to uppercase/lowercase according to mode
	if ($mode === 'upperindex' || $mode === 'upper') {
		$str = strtoupper($str);
	} elseif ($mode === 'lower') {
		$str = strtolower($str);
	}
	
	// Replace non-alphabetic characters if specified
	if ($nonalphaandnotlistedto !== false) {
		$str_a = mb_str_split($str);
		foreach ($str_a as $i => $l) {
			if (!ctype_alpha($l) && !isset($ch[$l])) {
				$str_a[$i] = $nonalphaandnotlistedto;
			}
		}
		$str = implode('', $str_a);
	}
	
	return $str;
}


/**
 * Escapes CDATA for use in XML.
 * 
 * @param string $str String to process.
 * @return string String with escaped CDATA.
 */
function mw_str_parse_cdata(string $str): string {
	return str_replace(']]>', "]]\n>", $str);
}

/**
 * Generates a JavaScript script with an alert message.
 * 
 * @param string|array $msg Message to display in the alert.
 * @return string|null HTML with the script or null if no message.
 */
function mw_text_js_msgalert(string|array $msg): ?string {
	$mstjstxt = mw_array2jsalert($msg);
	
	if (!$mstjstxt) {
		return null;
	}
	
	return "<script type='text/javascript'>\nalert('$mstjstxt');\n</script>";
}

/**
 * Converts a string to plain alphanumeric characters (ASCII).
 * Replaces special and non-alphanumeric characters with underscore.
 * 
 * @param string $str String to process.
 * @return string String with only alphanumeric characters and underscores.
 */
function mw_text_plain_chars(string $str): string {
	$str = mw_text_replace_lngchars($str);
	$result = '';
	
	for ($x = 0, $num = strlen($str); $x < $num; $x++) {
		$caracter = $str[$x];
		$result .= ctype_alnum($caracter) ? $caracter : '_';
	}
	
	return $result;
}

/**
 * Validates an email address.
 * 
 * @param string $address Email address to validate.
 * @param bool $allowEmpty If true, allows empty addresses.
 * @return bool True if the email is valid (or empty when allowed).
 */
function mw_checkemail(string $address, bool $allowEmpty = false): bool {
	if ($address === '') {
		return $allowEmpty;
	}
	
	return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
}
function randPass($len){
	return mw_randPass($len);
}
function mw_randPass($len){
	$pw = ''; 
	for($i=0;$i<$len;$i++) {
		switch(rand(1,3))   {
			case 1: $pw.=chr(rand(49,57));  break; 
			case 2: $pw.=chr(rand(65,90));  break;
			case 3: $pw.=chr(rand(97,122)); break;
		}
	}
	return $pw;
}

?>