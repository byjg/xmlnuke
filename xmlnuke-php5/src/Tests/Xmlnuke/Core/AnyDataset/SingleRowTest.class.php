<?php

namespace Xmlnuke\Core\AnyDataset;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-02-21 at 15:30:10.
 */
class SingleRowTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var SingleRow
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new SingleRow;
	}

	protected function fill()
	{
		$this->object->AddField('field1', '10');
		$this->object->AddField('field1', '20');
		$this->object->AddField('field1', '30');
		$this->object->AddField('field2', '40');
		$this->object->acceptChanges();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::AddField
	 * @todo   Implement testAddField().
	 */
	public function testAddField()
	{
		$this->object->AddField('field1', '10');
		$this->assertEquals(
			array(
				'field1'=>10
			), $this->object->getRawFormat());

		$this->object->AddField('field1', '20');
		$this->assertEquals(
			array(
				'field1'=> array(10, 20)
			), $this->object->getRawFormat());

		$this->object->AddField('field1', '30');
		$this->assertEquals(
			array(
				'field1'=> array(10, 20, 30)
			), $this->object->getRawFormat());

		$this->object->AddField('field2', '40');
		$this->assertEquals(
			array(
				'field1'=> array(10, 20, 30),
				'field2'=> 40
			), $this->object->getRawFormat());

		$this->object->AddField('field1', '20');
		$this->assertEquals(
			array(
				'field1'=> array(10, 20, 30, 20),
				'field2'=> 40
			), $this->object->getRawFormat());

	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::getField
	 * @todo   Implement testGetField().
	 */
	public function testGetField()
	{
		$this->fill();

		$this->assertEquals(10, $this->object->getField('field1'));
		$this->assertEquals(10, $this->object->getField('field1'));  // Test it again, because is an array
		$this->assertEquals(40, $this->object->getField('field2'));
		$this->assertEquals(null, $this->object->getField('not-exists'));
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::getFieldArray
	 * @todo   Implement testGetFieldArray().
	 */
	public function testGetFieldArray()
	{
		$this->fill();

		$this->assertEquals(array(10, 20, 30), $this->object->getFieldArray('field1'));
		$this->assertEquals(array(40), $this->object->getFieldArray('field2'));
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::getFieldNames
	 * @todo   Implement testGetFieldNames().
	 */
	public function testGetFieldNames()
	{
		$this->fill();

		$this->assertEquals(array('field1', 'field2'), $this->object->getFieldNames());
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::setField
	 * @todo   Implement testSetField().
	 */
	public function testSetField()
	{
		$this->fill();

		$this->object->setField('field1', 70);
		$this->assertEquals(70, $this->object->getField('field1'));

		$this->object->setField('field2', 60);
		$this->assertEquals(60, $this->object->getField('field2'));

		$this->object->setField('field3', 50);
		$this->assertEquals(50, $this->object->getField('field3'));
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::removeFieldName
	 * @todo   Implement testRemoveFieldName().
	 */
	public function testRemoveFieldName()
	{
		$this->fill();

		$this->object->removeFieldName('field1');
		$this->assertEquals(null, $this->object->getField('field1'));
		$this->assertEquals(40, $this->object->getField('field2'));
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::removeFieldName
	 * @todo   Implement testRemoveFieldName().
	 */
	public function testRemoveFieldName2()
	{
		$this->fill();

		$this->object->removeFieldName('field2');
		$this->assertEquals(10, $this->object->getField('field1'));
		$this->assertEquals(null, $this->object->getField('field2'));
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::removeFieldNameValue
	 * @todo   Implement testRemoveFieldNameValue().
	 */
	public function testRemoveFieldNameValue()
	{
		$this->fill();

		$this->object->removeFieldNameValue('field1', 20);
		$this->assertEquals(array(10, 30), $this->object->getFieldArray('field1'));

		$this->object->removeFieldNameValue('field2', 100);
		$this->assertEquals(40, $this->object->getField('field2')); // Element was not removed

		$this->object->removeFieldNameValue('field2', 40);
		$this->assertEquals(null, $this->object->getField('field2'));
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::setFieldValue
	 * @todo   Implement testSetFieldValue().
	 */
	public function testSetFieldValue()
	{
		$this->fill();

		$this->object->setFieldValue('field2', 100, 200);
		$this->assertEquals(40, $this->object->getField('field2')); // Element was not changed

		$this->object->setFieldValue('field2', 40, 200);
		$this->assertEquals(200, $this->object->getField('field2'));

		$this->object->setFieldValue('field1', 500, 190);
		$this->assertEquals(array(10, 20, 30), $this->object->getFieldArray('field1')); // Element was not changed

		$this->object->setFieldValue('field1', 20, 190);
		$this->assertEquals(array(10, 190, 30), $this->object->getFieldArray('field1'));
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::getDomObject
	 * @todo   Implement testGetDomObject().
	 */
	public function testGetDomObject()
	{
		$this->fill();

		$dom = \Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
			"<row>"
				. "<field name='field1'>10</field>"
				. "<field name='field1'>20</field>"
				. "<field name='field1'>30</field>"
				. "<field name='field2'>40</field>"
			. "</row>"
		);

		$this->assertEquals($dom, $this->object->getDomObject());
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::getOriginalRawFormat
	 * @todo   Implement testGetOriginalRawFormat().
	 */
	public function testGetOriginalRawFormat()
	{
		$this->fill();

		$this->object->setField('field2', 150);
		$this->assertEquals(array('field1'=>array(10,20,30), 'field2'=>40), $this->object->getOriginalRawFormat());
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::hasChanges
	 * @todo   Implement testHasChanges().
	 */
	public function testHasChanges()
	{
		$this->fill();

		$this->assertFalse($this->object->hasChanges());
		$this->object->setField('field2', 150);
		$this->assertTrue($this->object->hasChanges());
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::acceptChanges
	 * @todo   Implement testAcceptChanges().
	 */
	public function testAcceptChanges()
	{
		$this->fill();

		$this->object->setField('field2', 150);
		$this->assertEquals(array('field1'=>array(10,20,30), 'field2'=>40), $this->object->getOriginalRawFormat());
		$this->object->acceptChanges();
		$this->assertEquals(array('field1'=>array(10,20,30), 'field2'=>150), $this->object->getOriginalRawFormat());
	}

	/**
	 * @covers Xmlnuke\Core\AnyDataset\SingleRow::rejectChanges
	 * @todo   Implement testRejectChanges().
	 */
	public function testRejectChanges()
	{
		$this->fill();

		$this->object->setField('field2', 150);
		$this->assertEquals(array('field1'=>array(10,20,30), 'field2'=>150), $this->object->getRawFormat());
		$this->assertEquals(150, $this->object->getField('field2'));
		$this->object->rejectChanges();
		$this->assertEquals(array('field1'=>array(10,20,30), 'field2'=>40), $this->object->getRawFormat());
		$this->assertEquals(40, $this->object->getField('field2'));
	}

}