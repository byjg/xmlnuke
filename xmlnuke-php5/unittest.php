<?php
require_once("xmlnuke.inc.php");

$context = new Context();

echo "<html>";
echo "<head>";
echo "<title>PHP-Unit Results</title>";
echo "<link rel='stylesheet' href='common/styles/unittest.css' />";
//include("unittest.css");
//echo "</STYLE>";
echo "</head>";
echo "<body>";

$arr = array();

$list = $context->ContextValue("list");

if ($list == "")
{
	echo "You must call this page:  unittest.php?list=PATH.CLASS <br><br>";
	echo "PATH starts in LIB directory<br>";
	echo "CLASS must have method getList(). This method must have the PATH (from LIB directory) of a Test Unit Class<br><br><br>";
	
	echo "<h1>Sample Test Unit Class</h1>";
	echo "<pre>";
	?>
	// Filename: lib/xmlnuke/BannerTest.php
	class BannerTest extends TestCase 
	{
		protected $_banner;
		protected $_context;
	
		function BannerTest( $name = "BannerTest" ) 
		{
			$this->TestCase( $name );
			global $context;
			$this->_context = $context;
		}
	
		function setUp() 
		{
			$this->_banner = new Banner($this->_context);
		}
	
		function teardown() 
		{
			$this->_banner = null;
		}
	
		
		function test_obterDadosBannerNaoExiste() 
		{
			$it = $this->_banner->obterDadosBanner(24919);
			if ($it->hasNext())
			{
				$this->fail("Nao devia ter encontrado categoria");
			}
			else 
			{
				$this->assert("OK", "OK");
			}
		}
	}
		
	// The line below is very, very important		
	$mainTestSuite->addTest(new TestSuite( "BannerTest" ));	
	<?php
	echo "</pre>";
	echo "<h1>Sample Class</h1>";
	echo "<pre>";
	?>
	// url: unittest.php?list=unitlist
	class UnitList
	{
		protected $_context;
		
		public function __construct($context)
		{
			$this->_context = $context;
		}
		
		public function getList()
		{	
			$path = "lib/jacotei/phpunit/";
			$arr[] = $path."categoriatest.php";
			return $arr;
		}
	}
	<?php
	echo "</pre>";
	exit;
}
else 
{
	$path = "lib".FileUtil::Slash();
	$path .= str_replace('.', FileUtil::Slash(), $list);
	
	$file = basename($path);
	$path = dirname($path);

	try
	{
		require_once($path . FileUtil::Slash() . $file . ".class.php");
		$class = new ReflectionClass($file);
		$result = $class->newInstance($context);
		$arr = $result->getList();
	}
	catch (Exception $e)
	{
		throw new NotFoundException($e->getMessage());
	}
	
	if (!is_array($arr))
	{
		throw new Exception("getList() method must return an array.");
	}
}
?>
<form name="testesuite" method="post">
<input type="hidden" name="submeteu" value="sim">
<input type="hidden" name="qtd" value="<?php echo sizeof($arr);?>">
<?php
if ($context->ContextValue("debug")!="")
{
	$checked="checked";
}
else
{
	$checked="";
}
echo "<input type='checkbox' name='debug' value='true' $checked>";
echo "<font color='red'>Debug</font><br>";
foreach ($arr as $key=>$value)
{
	if ($context->ContextValue("check$key")!="")
	{
		$checked="checked";
	}
	else
	{
		$checked="";
	}
	echo "<input type='checkbox' name='check$key' value='x' $checked>";
	echo "Teste dos métodos da classe " . $arr[$key]."<br>";
}	
?>
<input type="submit">
</form>
<?php

if($context->ContextValue("submeteu")!="")
{
	require_once(PHPXMLNUKEDIR . "bin/modules/phpunit/phpunit.php");
	$mainTestSuite = new TestSuite();
	
	for($i=0;$i<$context->ContextValue("qtd");$i++)
	{
		if ($context->ContextValue("check$i")!="")
		{
			require_once($arr[$i]);
		}
	}
	
	$testRunner = new TestRunner();
	$testRunner->run( $mainTestSuite );
}
?>
