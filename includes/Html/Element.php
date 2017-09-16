<?php
namespace Redaxscript\Html;

/**
 * children class to create a element
 *
 * @since 2.6.0
 *
 * @package Redaxscript
 * @category Html
 * @author Henry Ruhs
 */

class Element extends HtmlAbstract
{
	/**
	 * tag of the element
	 *
	 * @var string
	 */

	protected $_tag = null;

	/**
	 * array of singleton tags
	 *
	 * @var array
	 */

	protected $_singletonTags =
	[
		'area',
		'base',
		'br',
		'col',
		'command',
		'embed',
		'hr',
		'img',
		'input',
		'keygen',
		'link',
		'meta',
		'param',
		'source',
		'track',
		'wbr',
	];

	/**
	 * attributes of the element
	 *
	 * @var array
	 */

	protected $_attributeArray = [];

	/**
	 * stringify the element
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */

	public function __toString() : string
	{
		return $this->render();
	}

	/**
	 * init the class
	 *
	 * @since 2.2.0
	 *
	 * @param string $tag tag of the element
	 * @param array $attributeArray attributes of the element
	 *
	 * @return $this
	 */

	public function init($tag = null, $attributeArray = [])
	{
		$this->_tag = strtolower($tag);

		/* process attributes */

		if (is_array($attributeArray))
		{
			$this->attr($attributeArray);
		}
		return $this;
	}

	/**
	 * copy the element
	 *
	 * @since 2.2.0
	 *
	 * @return $this
	 */

	public function copy()
	{
		return clone $this;
	}

	/**
	 * set the attribute to element
	 *
	 * @since 2.2.0
	 *
	 * @param string|array $attribute key or array of attributes
	 * @param string $value value of the attribute
	 *
	 * @return $this
	 */

	public function attr($attribute = null, $value = null)
	{
		if (is_array($attribute))
		{
			$this->_attributeArray = array_merge($this->_attributeArray, array_map('trim', $attribute));
		}
		else if (strlen($attribute) && strlen($value))
		{
			$this->_attributeArray[$attribute] = trim($value);
		}
		return $this;
	}

	/**
	 * remove the attribute from element
	 *
	 * @since 2.2.0
	 *
	 * @param string $attribute name of attributes
	 *
	 * @return $this
	 */

	public function removeAttr($attribute = null)
	{
		if (is_array($this->_attributeArray) && array_key_exists($attribute, $this->_attributeArray))
		{
			unset($this->_attributeArray[$attribute]);
		}
		return $this;
	}

	/**
	 * add the class to element
	 *
	 * @since 2.2.0
	 *
	 * @param string $className name of the classes
	 *
	 * @return $this
	 */

	public function addClass($className = null)
	{
		$this->_editClass($className, 'add');
		return $this;
	}

	/**
	 * remove the class from element
	 *
	 * @since 2.2.0
	 *
	 * @param string $className name of the classes
	 *
	 * @return $this
	 */

	public function removeClass($className = null)
	{
		$this->_editClass($className, 'remove');
		return $this;
	}

	/**
	 * edit class helper
	 *
	 * @since 2.2.0
	 *
	 * @param string $className name of the classes
	 * @param string $type add or remove
	 */

	protected function _editClass($className = null, $type = null)
	{
		$classArray = array_filter(explode(' ', $className));
		if (is_array($this->_attributeArray) && array_key_exists('class', $this->_attributeArray))
		{
			$attributeClassArray = array_filter(explode(' ', $this->_attributeArray['class']));
		}
		else
		{
			$attributeClassArray = [];
		}

		/* add or remove */

		if (is_array($attributeClassArray) && is_array($classArray))
		{
			if ($type === 'add')
			{
				$attributeClassArray = array_merge($attributeClassArray, $classArray);
			}
			else if ($type === 'remove')
			{
				$attributeClassArray = array_diff($attributeClassArray, $classArray);
			}
			$this->_attributeArray['class'] = implode(' ', array_unique($attributeClassArray));
		}
	}

	/**
	 * set the value to element
	 *
	 * @since 2.2.0
	 *
	 * @param string $value value of the element
	 *
	 * @return $this
	 */

	public function val($value = null)
	{
		$this->_attributeArray['value'] = trim($value);
		return $this;
	}

	/**
	 * set the text to element
	 *
	 * @since 2.2.0
	 *
	 * @param string $text text of the element
	 *
	 * @return $this
	 */

	public function text($text = null)
	{
		if (strip_tags($text))
		{
			$this->_html = strip_tags($text);
		}
		return $this;
	}

	/**
	 * render the element
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */

	public function render() : string
	{
		$output = '<' . $this->_tag;

		/* process attributes */

		foreach ($this->_attributeArray as $attribute => $value)
		{
			if (strlen($attribute) && strlen($value))
			{
				$output .= ' ' . $attribute . '="' . $value . '"';
			}
		}

		/* singleton tag */

		if (in_array($this->_tag, $this->_singletonTags))
		{
			$output .= ' />';
		}

		/* else normal tag */

		else
		{
			$output .= '>' . $this->_html . '</' . $this->_tag . '>';
		}
		return $output;
	}
}
