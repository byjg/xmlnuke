<?php

namespace Xmlnuke\Core\AnyDataset;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-10-22 at 11:33:57.
 */
class AnyDataSetTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var AnyDataSet
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new AnyDataSet;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::XML
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::getDomObject
	 */
	public function testXML()
	{
		$this->object->appendRow();
		$this->object->addField('field', 'value');

		$xmlDom = \Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
			'<?xml version="1.0" encoding="utf-8"?>'
				. '<anydataset>'
				. '<row>'
				. '<field name="field">value</field>'
				. '</row>'
			. '</anydataset>'
		);
		$xmlDomValidate = \Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr($this->object->XML());

		$this->assertEquals($xmlDom, $xmlDomValidate);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::Save
	 * @todo   Implement testSave().
	 */
	public function testSave()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::appendRow
	 * @todo   Implement testAppendRow().
	 */
	public function testAppendRow()
	{
		$qtd = $this->object->getIterator()->Count();
		$this->assertEquals(0, $qtd);

		$this->object->appendRow();
		$qtd = $this->object->getIterator()->Count();
		$this->assertEquals(1, $qtd);

		$this->object->appendRow();
		$qtd = $this->object->getIterator()->Count();
		$this->assertEquals(2, $qtd);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::import
	 * @todo   Implement testImport().
	 */
	public function testImport()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::insertRowBefore
	 * @todo   Implement testInsertRowBefore().
	 */
	public function testInsertRowBefore()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::removeRow
	 * @todo   Implement testRemoveRow().
	 */
	public function testRemoveRow()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::addField
	 * @todo   Implement testAddField().
	 */
	public function testAddField()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::getIterator
	 * @todo   Implement testGetIterator().
	 */
	public function testGetIterator()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::getArray
	 * @todo   Implement testGetArray().
	 */
	public function testGetArray()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\AnyDataSet::Sort
	 * @todo   Implement testSort().
	 */
	public function testSort()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}
