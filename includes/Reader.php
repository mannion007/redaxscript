<?php
namespace Redaxscript;

use SimpleXMLElement;

/**
 * parent class to load and convert data
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Reader
 * @author Henry Ruhs
 */

class Reader
{
	/**
	 * data object
	 *
	 * @var object
	 */

	protected $_dataObject;

	/**
	 * get the object
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */

	public function getObject()
	{
		return $this->_dataObject;
	}

	/**
	 * get the array
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */

	public function getArray()
	{
		return json_decode(json_encode($this->_dataObject), true);
	}

	/**
	 * get the json
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */

	public function getJSON()
	{
		return json_encode($this->_dataObject);
	}

	/**
	 * get the xml
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */

	public function getXML()
	{
		if (method_exists($this->getObject(), 'asXML'))
		{
			return $this->getObject()->asXML();
		}
		return $this->_convertArrayToObject($this->getArray())->asXML();
	}

	/**
	 * load json from url
	 *
	 * @since 3.1.0
	 *
	 * @param string $url
	 *
	 * @return $this
	 */

	public function loadJSON($url = null)
	{
		$content = $this->load($url);
		$this->_dataObject = json_decode($content);
		return $this;
	}

	/**
	 * load xml from url
	 *
	 * @since 3.0.0
	 *
	 * @param string $url
	 *
	 * @return $this
	 */

	public function loadXML($url = null)
	{
		$content = $this->load($url);
		$this->_dataObject = simplexml_load_string($content);
		return $this;
	}

	/**
	 * load from url
	 *
	 * @since 3.0.0
	 *
	 * @param string $url
	 *
	 * @return string
	 */

	public function load($url = null)
	{
		/* curl */

		if (function_exists('curl_version') && !is_file($url))
		{
			$optionArray =
			[
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_URL => $url,
				CURLOPT_HTTPHEADER =>
				[
					'User-Agent: Redaxscript'
				]
			];
			$curl = curl_init();
			curl_setopt_array($curl, $optionArray);
			$output = curl_exec($curl);
			curl_close($curl);
		}

		/* else fallback */

		else
		{
			$output = file_get_contents($url);
		}
		return $output;
	}

	/**
	 * convert array to object
	 *
	 * @since 3.1.0
	 *
	 * @param array $dataArray
	 * @param object $dataObject
	 *
	 * @return object
	 */

	protected function _convertArrayToObject($dataArray = [], $dataObject = null)
	{
		if (!is_object($dataObject))
		{
			$dataObject = new SimpleXMLElement('<root />');
		}

		/* process data */

		foreach ($dataArray as $key => $value)
		{
			if(is_numeric($key))
			{
				$key = 'children';
			}
			if ($key === '@attributes')
			{
				foreach ($value as $attributeKey => $attributeValue)
				{
					$dataObject->addAttribute($attributeKey, $attributeValue);
				}
			}
			else if (is_array($value))
			{
				$this->_convertArrayToObject($value, $dataObject->addChild($key));
			}
			else
			{
				$dataObject->addChild($key, $value);
			}
		}
		return $dataObject;
	}
}
