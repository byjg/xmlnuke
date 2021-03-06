<?php

namespace Xmlnuke\Core\Locale;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-02-26 at 10:43:55.
 */
class CultureInfoTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var CultureInfo
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new CultureInfo('en-us');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getName
	 * @todo   Implement testGetName().
	 */
	public function testGetName()
	{
		$this->assertEquals('en-us', $this->object->getName());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getLanguage
	 * @todo   Implement testGetLanguage().
	 */
	public function testGetLanguage()
	{
		$this->assertEquals('English - United States', $this->object->getLanguage());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getIsoName
	 * @todo   Implement testGetIsoName().
	 */
	public function testGetIsoName()
	{
		$this->assertEquals('en_US', $this->object->getIsoName());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getIntlCurrencySymbol
	 * @todo   Implement testGetIntlCurrencySymbol().
	 */
	public function testGetIntlCurrencySymbol()
	{
		$this->assertEquals('USD ', $this->object->getIntlCurrencySymbol());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getCurrencySymbol
	 * @todo   Implement testGetCurrencySymbol().
	 */
	public function testGetCurrencySymbol()
	{
		$this->assertEquals('$', $this->object->getCurrencySymbol());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getDecimalPoint
	 * @todo   Implement testGetDecimalPoint().
	 */
	public function testGetDecimalPoint()
	{
		$this->assertEquals('.', $this->object->getDecimalPoint());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getCultureActive
	 * @todo   Implement testGetCultureActive().
	 */
	public function testGetCultureActive()
	{
		$this->assertFalse(!$this->object->getCultureActive());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getRegionalMonthNames
	 * @todo   Implement testGetRegionalMonthNames().
	 */
	public function testGetRegionalMonthNames()
	{
		$this->assertEquals(
			array(
				1=>'January',
				2=>'February',
				3=>'March',
				4=>'April',
				5=>'May',
				6=>'June',
				7=>'July',
				8=>'August',
				9=>'September',
				10=>'October',
				11=>'November',
				12=>'December'
			), $this->object->getRegionalMonthNames());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::formatMoney
	 * @todo   Implement testFormatMoney().
	 */
	public function testFormatMoney()
	{
		// Positive
		$this->assertEquals('$1,234.56', $this->object->formatMoney(1234.56));
		$this->assertEquals('USD 1,234.56', $this->object->formatMoney(1234.56, true));
		$this->assertEquals('$1,235', $this->object->formatMoney(1234.56, false, true));
		$this->assertEquals('USD 1,235', $this->object->formatMoney(1234.56, true, true));

		// Negative
		$this->assertEquals('-$1,234.56', $this->object->formatMoney(-1234.56));
		$this->assertEquals('-USD 1,234.56', $this->object->formatMoney(-1234.56, true));
		$this->assertEquals('-$1,235', $this->object->formatMoney(-1234.56, false, true));
		$this->assertEquals('-USD 1,235', $this->object->formatMoney(-1234.56, true, true));
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getDateFormat
	 * @todo   Implement testGetDateFormat().
	 */
	public function testGetDateFormat()
	{
		$this->assertEquals(\Xmlnuke\Core\Enum\DATEFORMAT::MDY, $this->object->getDateFormat());
	}

	/**
	 * @covers Xmlnuke\Core\Locale\CultureInfo::getDoubleVal
	 * @todo   Implement testGetDoubleVal().
	 */
	public function testGetDoubleVal()
	{
		$this->assertEquals(1234.56, $this->object->getDoubleVal('1234.56'));
		$this->assertEquals(1234.0, $this->object->getDoubleVal('1234,56'));
		$this->assertEquals(1234.0, $this->object->getDoubleVal('1234'));
	}

}
