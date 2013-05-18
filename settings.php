<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */

/* Chronicle settings 
	
	
*/
class settings {
	private $files;

	public function __construct() {		
		$this->files = array(
			'site' => API_BASE.'/site.json');
			
		try {
			$this->loadSettings();		
		} catch(Exception $e) {
			presto_lib::_trace($e->getMessage());
			throw $e;
		}
	}
	
	public function __get($k) {
		presto_lib::_trace("Skipping missing '$k' value from loaded settings (not found)", $f);
 		return "<var class=\"missing\">$k</var>";
	}
	
	/* Private members */

	// Load the settings files
	private function loadSettings() {

		foreach ($this->files as $n => $f) {

			if (!file_exists($f))
				throw new Exception("Missing '$n' settings ($f not found)", 500);

			$config = file_get_contents($f);
			if (empty($config)) throw new Exception("Empty configuration file $f", 500);
			
			$config = json_decode($config);
			if (empty($config)) throw new Exception("Invalid configuration format in $f", 500);
			
			$this->$n = json_decode($config);
		}
		presto_lib::_trace("Loaded '$n' settings file.");
	}
}	
