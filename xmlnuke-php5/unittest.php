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
	?>
	<h1>Missing Parameters</h1>
	You must call this page:  unittest.php?list=NAMESPACE.CLASS <br><br>

	<h1>Sample Unit List Class</h1>
	<pre>

	// url: unittest.php?list=byjg.classes.unitlist
	class UnitList
	{
		protected $_context;

		public function __construct($context)
		{
			$this->_context = $context;
		}

		public function getList()
		{
			$arr[] = "byjg.classes.exemplotest";
			return $arr;
		}
	}
	</pre>

	<h1>Sample Test Unit Class</h1>
	<pre>
	/**
	 * NOTE: The class name must end with "Test" suffix.
	 */
	class ExemploTest extends TestCase
	{
		// Run before each test case
		function setUp()
		{
			$this->_banner = new Banner($this->_context);
		}

		// Run end each test case
		function teardown()
		{
			$this->_banner = null;
		}

		// Note: All test cases have to start with "test_" prefix
		function test_obterDadosBannerNaoExiste()
		{
			$it = $this->_banner->obterDadosBanner(24919);
			$this->assert(! $it->hasNext(), "You should not found this banner");
		}
	}
	</pre>

	<?php
	exit;
}
else
{
	try
	{
		$result = PluginFactory::LoadPlugin($list, "", $context);
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
			$testPlugin = PluginFactory::LoadPlugin($arr[$i]);
			if ($testPlugin instanceof TestCase)
			{
				$mainTestSuite->addTest(new TestSuite( get_class($testPlugin) ));
			}
			else
			{
				throw new Exception (get_class($testPlugin) . " need to be an instance of TestCase");
			}
		}
	}

	$testRunner = new TestRunner();
	$testRunner->run( $mainTestSuite );
}
?>
