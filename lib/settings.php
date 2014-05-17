<?php

namespace robotpony\chronicleMD;

/* ChronicleMD settings */
class settings {

	private static $s = array();

	public static function page_title() {}
	public static function page_description() {}
	public static function page_keywords() {}

	/* Global site settings */
	public static function load($n, $f) {

		if (array_key_exists($n, self::$s))
			return self::$s[$n];

		// cache settings
		self::$s[$n] = self::load_settings_file($n, $f, array(
			'name' 			=> '',
			'tagline' 		=> '',
			'description' 	=> '',
			'keywords' 		=> '',
			'copyright' 		=> '',
			'feedURL' 		=> '',
			'URL' 			=> '',
			'author' 		=> ''

		));
	}

	/* Get a setting by name

	Global settings are set per pageload (see public statics above)

	site\settings::site('name');
	site\settings::blog('max-depth');



	*/
	public static function __callStatic($n, $a) {

		$k = array_pop($a);

		if (empty($k))
			return "{Missing setting key for $n()}";

		if (array_key_exists($n, self::$s)) {
			if (array_key_exists($k, self::$s[$n]))
				return self::$s[$n][$k];

			return "{Missing $n('$k')}";
		}

		return "{No settings for {$n}";
	}


	/**/

	private static function load_settings_file($section, $filename, $defaults = array()) {
		if ($section === 'site') $section = '';

		if (!($f = realpath(BLOG_ROOT."/$section/$filename"))) {
			notate("Settings for $section/$filename not found.");
			return $defaults;
		}

		$f = file_get_contents($f);
		$settings = json_decode($f);

		return (array) $settings;
	}
}
