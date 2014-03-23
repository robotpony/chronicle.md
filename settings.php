<?php /* Chronicle.md - Copyright (C) 2014 Bruce Alderson */

namespace napkinware\chronicle;

use napkinware\presto as presto;

/* Chronicle settings

	TODO:
		- check file contents
		- provide defaults
		- write out if missing

*/
class siteSettings {
	private $files;
	private $n;

	/* Construct the settings object */
	public function __construct() {

		// default settings
		$this->files = array(
			'site' => (object) array(

				'file' => API_BASE.'/site.json',

				'defaults' => (object) array(
					'URL'			=> '',
					'homePosts' 	=> 1,
					'archivePosts' 	=> 10,
					'feedPosts'		=> 10,
					'name' 			=> 'Site name',
					'tagline' 		=> 'This is a tagline',
					'description' 	=> 'This is a description',

					'blog' 			=> '/blog/'
				)
			));

		try {

			$this->loadSettings();

		} catch(Exception $e) {

			presto\trace($e->getMessage());
			throw $e;
		}
	}

	/* Handle missing settings files (last chance) */
	public function __get($n) {
		presto\trace("Skipping missing '$n' settings (file not loaded)");
 		return "[missing file $n]";
	}

	/* ======================== Private helpers ======================== */

	/* Load the settings files */
	private function loadSettings() {

		foreach ($this->files as $n => $f) {

			if (!file_exists($f->file))
				throw new Exception("Missing '$n' settings ($f not found)");

			$config = file_get_contents($f->file);

			if (!$config || empty($config))
				throw new Exception("Empty configuration file $f");

			$this->$n = new settingsFile($config, $n, $f->file, $f->defaults);
		}
		presto\trace("Loaded $n settings.");
	}
}

/* One settings file */
class settingsFile {
	public $d;

	/* Set up the setting object */
	public function __construct($s, $n, $f, $defaults = null) {

		// populate settings

		if (is_string($s))
			$this->d = json_decode($s); // decode from string
		elseif (is_array($s))
			$this->d = (object) $s; // from array, objectize
		elseif (is_object($s))
			$this->d = $s; // from object
		else
			throw new Exception("Unknown configuration format found for $n: [$f] - $s");

		// merge in defaults, if any

		if ( $defaults && (is_object($defaults) || is_array($defaults)) )
			$this->d = (object) array_merge( (array) $defaults, (array) $this->d );
	}

	public function hasData() { return !empty($this->d); }

	// Get a setting
	public function __get($n) {

		if (property_exists($this->d, $n))
			return $this->d->$n;

		presto\trace("Skipping missing '$n' setting (property does not exist)");
 		return "[missing $n]";
	}
}
