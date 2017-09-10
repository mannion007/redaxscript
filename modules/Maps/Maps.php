<?php
namespace Redaxscript\Modules\Maps;

use Redaxscript\Head;
use Redaxscript\Html;

/**
 * integrate google maps
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

class Maps extends Config
{
	/**
	 * array of the module
	 *
	 * @var array
	 */

	protected static $_moduleArray =
	[
		'name' => 'Maps',
		'alias' => 'Maps',
		'author' => 'Redaxmedia',
		'description' => 'Integrate Google Maps',
		'version' => '3.3.0'
	];

	/**
	 * renderStart
	 *
	 * @since 3.0.0
	 */

	public function renderStart()
	{
		if (!$this->_registry->get('adminParameter'))
		{
			/* link */

			$link = Head\Link::getInstance();
			$link
				->init()
				->appendFile('modules/Maps/dist/styles/maps.min.css');

			/* script */

			$script = Head\Script::getInstance();
			$script
				->init('foot')
				->appendFile($this->_configArray['apiUrl'] . '?key=' . $this->_configArray['apiKey'])
				->appendFile('modules/Maps/assets/scripts/init.js')
				->appendFile('modules/Maps/dist/scripts/maps.min.js');
		}
	}

	/**
	 * render
	 *
	 * @since 3.0.0
	 *
	 * @param integer $latitude
	 * @param integer $longitude
	 * @param integer $zoom
	 *
	 * @return string
	 */

	public function render($latitude = 0, $longitude = 0, $zoom = 1)
	{
		$mapElement = new Html\Element();
		$mapElement->init('div',
		[
			'class' => $this->_configArray['className'],
			'data-latitude' => is_numeric($latitude) ? $latitude : null,
			'data-longitude' => is_numeric($longitude) ? $longitude : null,
			'data-zoom' => is_numeric($zoom) ? $zoom : null
		]);

		/* collect output */

		$output = $mapElement;
		return $output;
	}
}
