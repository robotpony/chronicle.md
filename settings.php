<?php /* Chronicle.md - Copyright (C) 2013 Bruce Alderson */

/* Chronicle settings 
	
	TODO:
		- check file contents
		- provide defaults
		- write out if missing
	
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
	
	
	public function __get($name) {
		// TODO - implement for subclass structures
		presto_lib::_trace("Skipping missing '$name' settings (settings file name does not exist)");
 		return "<var class=\"missing\">$name</var>";
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
			
			if (!$config || empty($config)) throw new Exception("Invalid configuration format in $f", 500);
			
			$this->$n = $config;
			print_r(json_decode($config));
		}
		presto_lib::_trace("Loaded '$n' settings file.");
	}
}	
