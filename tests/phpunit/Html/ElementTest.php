<?php
namespace Redaxscript\Tests\Html;

use Redaxscript\Html;
use Redaxscript\Tests\TestCaseAbstract;

/**
 * ElementTest
 *
 * @since 2.6.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 */

class ElementTest extends TestCaseAbstract
{
	/**
	 * providerCreate
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */

	public function providerCreate() : array
	{
		return $this->getProvider('tests/provider/Html/element_create.json');
	}

	/**
	 * providerAttr
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */

	public function providerAttr() : array
	{
		return $this->getProvider('tests/provider/Html/element_attr.json');
	}

	/**
	 * providerClass
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */

	public function providerClass() : array
	{
		return $this->getProvider('tests/provider/Html/element_class.json');
	}

	/**
	 * providerVal
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */

	public function providerVal() : array
	{
		return $this->getProvider('tests/provider/Html/element_val.json');
	}

	/**
	 * providerText
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */

	public function providerText() : array
	{
		return $this->getProvider('tests/provider/Html/element_text.json');
	}

	/**
	 * testCreate
	 *
	 * @since 2.2.0
	 *
	 * @param string $tag
	 * @param array $attributeArray
	 * @param string $expect
	 *
	 * @dataProvider providerCreate
	 */

	public function testCreate($tag = null, $attributeArray = [], string $expect = null)
	{
		/* setup */

		$element = new Html\Element();
		$element->init($tag, $attributeArray);

		/* actual */

		$actual = $element;

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testCopy
	 *
	 * @since 2.2.0
	 */

	public function testCopy()
	{
		/* setup */

		$element = new Html\Element();
		$element->init('a');
		$elementCopy = $element->copy()->attr('href', 'test');

		/* expect and actual */

		$expect = $element;
		$actual = $elementCopy;

		/* compare */

		$this->assertNotEquals($expect, $actual);
	}

	/**
	 * testAttr
	 *
	 * @since 2.2.0
	 *
	 * @param array $attribute
	 * @param string $expect
	 *
	 * @dataProvider providerAttr
	 */

	public function testAttr($attribute = [], string $expect = null)
	{
		/* setup */

		$element = new Html\Element();
		$element->init('a');

		/* actual */

		$actual = $element->attr($attribute[0], $attribute[1])->removeAttr($attribute[2])->render();

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testClass
	 *
	 * @since 2.2.0
	 *
	 * @param array $className
	 * @param string $expect
	 *
	 * @dataProvider providerClass
	 */

	public function testClass($className = [], string $expect = null)
	{
		/* setup */

		$element = new Html\Element();
		$element->init('a');

		/* actual */

		$actual = $element->addClass($className[0])->addClass($className[1])->removeClass($className[2])->render();

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testVal
	 *
	 * @since 2.6.0
	 *
	 * @param array $value
	 * @param string $expect
	 *
	 * @dataProvider providerVal
	 */

	public function testVal($value = null, string $expect = null)
	{
		/* setup */

		$element = new Html\Element();
		$element->init('input');

		/* actual */

		$actual = $element->val($value);

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testText
	 *
	 * @since 2.6.0
	 *
	 * @param array $text
	 * @param string $expect
	 *
	 * @dataProvider providerText
	 */

	public function testText($text = null, string $expect = null)
	{
		/* setup */

		$element = new Html\Element();
		$element->init('a');

		/* actual */

		$actual = $element->text($text);

		/* compare */

		$this->assertEquals($expect, $actual);
	}
}
