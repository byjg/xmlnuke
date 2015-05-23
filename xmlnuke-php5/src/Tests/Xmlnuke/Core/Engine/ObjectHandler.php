<?php

namespace Xmlnuke\Core\Engine;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-04-29 at 18:59:37.
 */
class ObjectHandlerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var ObjectHandler
	 */
	protected $object;

	protected $document;
	protected $root;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->document = \Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr("<root/>");
		$this->root = $this->document->documentElement;
		//$this->object = new ObjectHandler;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_ObjectGetter_1elem()
	{
		$model = new \Tests\Xmlnuke\Sample\ModelGetter(10, 'Joao');

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<ModelGetter>'
					.	'<Id>10</Id>'
					.	'<Name>Joao</Name>'
					. '</ModelGetter>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_ObjectGetter_2elem()
	{
		$model = array(
			new \Tests\Xmlnuke\Sample\ModelGetter(10, 'Joao'),
			new \Tests\Xmlnuke\Sample\ModelGetter(20, 'JG')
		);

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<ModelGetter>'
					.	'<Id>10</Id>'
					.	'<Name>Joao</Name>'
					. '</ModelGetter>'
					. '<ModelGetter>'
					.	'<Id>20</Id>'
					.	'<Name>JG</Name>'
					. '</ModelGetter>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_ObjectPublic_1elem()
	{
		$model = new \Tests\Xmlnuke\Sample\ModelPublic(10, 'Joao');

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Tests_Xmlnuke_Sample_ModelPublic>'
					.	'<Id>10</Id>'
					.	'<Name>Joao</Name>'
					. '</Tests_Xmlnuke_Sample_ModelPublic>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_ObjectPublic_2elem()
	{
		$model = array(
			new \Tests\Xmlnuke\Sample\ModelPublic(10, 'Joao'),
			new \Tests\Xmlnuke\Sample\ModelPublic(20, 'JG')
		);

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Tests_Xmlnuke_Sample_ModelPublic>'
					.	'<Id>10</Id>'
					.	'<Name>Joao</Name>'
					. '</Tests_Xmlnuke_Sample_ModelPublic>'
					. '<Tests_Xmlnuke_Sample_ModelPublic>'
					.	'<Id>20</Id>'
					.	'<Name>JG</Name>'
					. '</Tests_Xmlnuke_Sample_ModelPublic>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_StdClass_1()
	{
		$model = new \stdClass();
		$model->Id = 10;
		$model->Name = 'Joao';

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Id>10</Id>'
					. '<Name>Joao</Name>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_StdClass_Model()
	{
		$model = new \stdClass();
		$model->Id = 10;
		$model->Name = 'Joao';
		$model->Object = new \Tests\Xmlnuke\Sample\ModelGetter(20, 'JG');

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Id>10</Id>'
					. '<Name>Joao</Name>'
					. '<Object>'
					.	'<ModelGetter>'
					.		'<Id>20</Id>'
					.		'<Name>JG</Name>'
					.	'</ModelGetter>'
					. '</Object>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Array_1()
	{
		$model = 
			[
				'Id'=>10,
				'Name'=>'Joao'
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Id>10</Id>'
					. '<Name>Joao</Name>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Array_2()
	{
		$model = 
			[
				'Id'=>10,
				'Name'=>'Joao',
				'Data' =>
					[
						'Code'=>'2',
						'Sector'=>'3'
					]
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Id>10</Id>'
					. '<Name>Joao</Name>'
					. '<Data>'
					.	'<Code>2</Code>'
					.	'<Sector>3</Sector>'
					. '</Data>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_StdClass_Array()
	{
		$model = new \stdClass();
		$model->Obj = 
			[
				'Id'=>10,
				'Name'=>'Joao'
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Obj>'
					.	'<Id>10</Id>'
					.	'<Name>Joao</Name>'
					. '</Obj>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Array_Scalar()
	{
		$model = new \stdClass();
		$model->Obj = 
			[
				10,
				'Joao'
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Obj>10</Obj>'
					. '<Obj>Joao</Obj>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Array_Mixed()
	{
		$model = new \stdClass();
		$model->Obj = 
			[
				10,
				'Joao',
				new \Tests\Xmlnuke\Sample\ModelGetter(20, 'JG')
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Obj>10</Obj>'
					. '<Obj>Joao</Obj>'
					. '<Obj><ModelGetter><Id>20</Id><Name>JG</Name></ModelGetter></Obj>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Array_Array()
	{
		$model = new \stdClass();
		$model->Obj =
			[
				'Item1' =>
					[
						10,
						'Joao'
					],
				'Item2' =>
					[
						20,
						'JG'
					]
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Obj>'
					. '<Item1>10</Item1><Item1>Joao</Item1>'
					. '<Item2>20</Item2><Item2>JG</Item2>'
					. '</Obj>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Array_Array_2 ()
	{
		$model = new \stdClass();
		$model->Obj =
			[
				[
					10,
					'Joao'
				]
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<Obj>'
					. '<scalar>10</scalar>'
					. '<scalar>Joao</scalar>'
					. '</Obj>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Array_Array_3()
	{
		$model =
			[
				[
					'Id'=>10,
					'Name'=>'Joao'
				],
				[
					'Id'=>11,
					'Name'=>'Gilberto'
				],
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');

		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<__object>'
						. '<Id>10</Id>'
						. '<Name>Joao</Name>'
					. '</__object>'
					. '<__object>'
						. '<Id>11</Id>'
						. '<Name>Gilberto</Name>'
					. '</__object>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Collection_DontCreateNode()
	{
		$modellist = new \Tests\Xmlnuke\Sample\ModelList();
		$modellist->addItem(new \Tests\Xmlnuke\Sample\ModelGetter(10, 'Joao'));
		$modellist->addItem(new \Tests\Xmlnuke\Sample\ModelGetter(20, 'JG'));

		$this->object = new ObjectHandler($this->root, $modellist, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<ModelList>'
					.	'<ModelGetter><Id>10</Id><Name>Joao</Name></ModelGetter>'
					.	'<ModelGetter><Id>20</Id><Name>JG</Name></ModelGetter>'
					. '</ModelList>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Collection_CreateNode()
	{
		$modellist = new \Tests\Xmlnuke\Sample\ModelList2();
		$modellist->addItem(new \Tests\Xmlnuke\Sample\ModelGetter(10, 'Joao'));
		$modellist->addItem(new \Tests\Xmlnuke\Sample\ModelGetter(20, 'JG'));

		$this->object = new ObjectHandler($this->root, $modellist, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<ModelList>'
					. '<collection>'
					.	'<ModelGetter><Id>10</Id><Name>Joao</Name></ModelGetter>'
					.	'<ModelGetter><Id>20</Id><Name>JG</Name></ModelGetter>'
					. '</collection>'
					. '</ModelList>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_Collection_SkipParentAndRenameChild()
	{
		$modellist = new \Tests\Xmlnuke\Sample\ModelList3();
		$modellist->addItem(new \Tests\Xmlnuke\Sample\ModelGetter(10, 'Joao'));
		$modellist->addItem(new \Tests\Xmlnuke\Sample\ModelGetter(20, 'JG'));

		$this->object = new ObjectHandler($this->root, $modellist, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<ModelList>'
					.	'<List><Id>10</Id><Name>Joao</Name></List>'
					.	'<List><Id>20</Id><Name>JG</Name></List>'
					. '</ModelList>'
					. '</root>'),
			$this->document
		);
	}

	/**
	 * @covers Xmlnuke\Core\Engine\ObjectHandler::CreateObjectFromModel
	 * @todo   Implement testCreateObjectFromModel().
	 */
	public function testCreateObjectFromModel_OnlyScalarAtFirstLevel()
	{
		$model =
			[
				10,
				'Joao'
			];

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<scalar>10</scalar>'
					. '<scalar>Joao</scalar>'
					. '</root>'),
			$this->document
		);
	}

	public function testEmptyValues()
	{
		$model = new \stdClass();
		$model->varFalse = false;
		$model->varTrue = true;
		$model->varZero = 0;
		$model->varZeroStr = '0';      
		$model->varNull = null;        // Sould not created
		$model->varEmptyString = '';   // Sould not created

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<varFalse>false</varFalse>'
					. '<varTrue>true</varTrue>'
					. '<varZero>0</varZero>'
					. '<varZeroStr>0</varZeroStr>'
					. '</root>'),
			$this->document
		);
	}

	public function testIterator()
	{
		$model = new \Xmlnuke\Core\AnyDataset\AnyDataSet();
		$model->AddField("id", 10);
		$model->AddField("name", 'Testing');

		$model->appendRow();
		$model->AddField("id", 20);
		$model->AddField("name", 'Other');

		$iterator = $model->getIterator();

		$this->object = new ObjectHandler($this->root, $iterator, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<row>'
					. '<field name="id">10</field>'
					. '<field name="name">Testing</field>'
					. '</row>'
					. '<row>'
					. '<field name="id">20</field>'
					. '<field name="name">Other</field>'
					. '</row>'
					. '</root>'),
			$this->document
		);

	}

	public function testLanguageCollection()
	{
		$model = new \Xmlnuke\Core\Locale\LanguageCollection();
		$model->addText(Context::getInstance()->Language()->getName(), 'TEXT_WARNING', 'Aviso');
		$model->addText(Context::getInstance()->Language()->getName(), 'TEXT_NEW', 'Novo');

		$this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
		$result = $this->object->CreateObjectFromModel();

		$this->assertEquals(
			\Xmlnuke\Util\XmlUtil::CreateXmlDocumentFromStr(
				'<root>'
					. '<l10n>'
					. '<TEXT_WARNING>Aviso</TEXT_WARNING>'
					. '<TEXT_NEW>Novo</TEXT_NEW>'
					. '</l10n>'
					. '</root>'),
			$this->document
		);

	}
}
