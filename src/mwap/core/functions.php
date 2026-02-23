<?php

/**
 * Gets the global autoload manager instance.
 * 
 * @return mw_autoload_manager|false Returns the autoload manager or false if not initialized.
 */
function mw_get_autoload_manager(): mw_autoload_manager|false {
	if (isset($GLOBALS["__mw_autoload_manager"]) 
		&& is_object($GLOBALS["__mw_autoload_manager"]) 
		&& is_a($GLOBALS["__mw_autoload_manager"], "mw_autoload_manager")
	) {
		return $GLOBALS["__mw_autoload_manager"];
	}
	return false;
}

/**
 * Gets the main application instance.
 * 
 * @return mwmod_mw_ap_apabs|false Returns the main app or false if not initialized.
 */
function mw_get_main_ap(): mwmod_mw_ap_apabs|false {
	if (isset($GLOBALS["__mw_main_ap"]) 
		&& is_object($GLOBALS["__mw_main_ap"]) 
		&& is_a($GLOBALS["__mw_main_ap"], "mwmod_mw_ap_apabs")
	) {
		return $GLOBALS["__mw_main_ap"];
	}
	return false;
}

?>