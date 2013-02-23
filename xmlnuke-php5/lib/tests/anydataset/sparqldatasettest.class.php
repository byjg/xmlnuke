<?php
/**
 * NOTE: The class name must end with "Test" suffix.
 */   
class SparQLDatasetTest extends TestCase
{
	const SPARQL_URL = 'http://rdf.ecs.soton.ac.uk/sparql/';
	protected static $SPARQL_NS = array("foaf" => "http://xmlns.com/foaf/0.1/");

	// Run before each test case
	function setUp()
	{
	}

	// Run end each test case
	function teardown()
	{
	}

	function test_connectSparQLDataSet()
	{
		$dataset = new SparQLDataSet(SparQLDatasetTest::SPARQL_URL, SparQLDatasetTest::$SPARQL_NS);
		$iterator = $dataset->getIterator("SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 5");

		$this->assert($iterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($iterator->hasNext(), "hasNext() method must be true");
		$this->assert($iterator->Count() == 5, "Count() method must return 5");
	}
	
	/**
	 * @AssertIfException DatasetException
	 */
	function test_wrongSparQLDataSet()
	{
		$dataset = new SparQLDataSet("http://localhost/", SparQLDatasetTest::$SPARQL_NS);
		$iterator = $dataset->getIterator("SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 5");

		$this->assert($iterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert(!$iterator->hasNext(), "hasNext() method must be false");
		$this->assert($iterator->Count() == 0, "Count() method must return 0");
	}

	/**
	 * @AssertIfException DatasetException
	 */
	function test_wrongSparQLDataSet2()
	{
		$dataset = new SparQLDataSet(SparQLDatasetTest::SPARQL_URL);
		$iterator = $dataset->getIterator("SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 5");
	}
	
	function test_navigateSparQLDataSet()
	{
		$dataset = new SparQLDataSet(SparQLDatasetTest::SPARQL_URL, SparQLDatasetTest::$SPARQL_NS);
		$iterator = $dataset->getIterator("SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 2");

		$this->assert($iterator->hasNext(), "hasNext() method must be true");
		$this->assert($iterator->Count() == 2, "Count() method must return 2");

		$sr = $iterator->moveNext();
		
		$this->assert($sr->getField("person") == "bb1810500000080", "'person' has to be 'bb1810500000080' and found '" . $sr->getField("person") . "'");
		$this->assert($sr->getField("person.type") == "bnode", "'person.type' has to be 'bnode' and found '" . $sr->getField("person.type") . "'");
		$this->assert($sr->getField("name") == "das05r", "'name' has to be 'das05r' and found '" . $sr->getField("name") . "'");
		$this->assert($sr->getField("name.type") == "literal", "'person.type' has to be 'literal' and found '" . $sr->getField("name.type") . "'");
		$this->assert($sr->getField("name.datatype") == "http://www.w3.org/2001/XMLSchema#string", "'person.datatype' has to be 'http://www.w3.org/2001/XMLSchema#string' and found '" . $sr->getField("name.datatype") . "'");

		$this->assert($iterator->hasNext(), "hasNext() method must be true");
		$sr = $iterator->moveNext();
		
		$this->assert($sr->getField("person") == "bb6810500000080", "'person' has to be 'bb6810500000080' and found '" . $sr->getField("person") . "'");
		$this->assert($sr->getField("person.type") == "bnode", "'person.type' has to be 'bnode' and found '" . $sr->getField("person.type") . "'");
		$this->assert($sr->getField("name") == "Sachin Idgunji", "'name' has to be 'Sachin Idgunji' and found '" . $sr->getField("name") . "'");
		$this->assert($sr->getField("name.type") == "literal", "'person.type' has to be 'literal' and found '" . $sr->getField("name.type") . "'");
		$this->assert($sr->getField("name.datatype") == "http://www.w3.org/2001/XMLSchema#string", "'person.datatype' has to be 'http://www.w3.org/2001/XMLSchema#string' and found '" . $sr->getField("name.datatype") . "'");

		$this->assert(!$iterator->hasNext(), "hasNext() method must be false");
		
	}
	
	function test_capabilities()
	{
		$dataset = new SparQLDataSet(SparQLDatasetTest::SPARQL_URL);

		$caps = $dataset->getCapabilities();
		
		$this->assert($caps["select"][0], "Excepted true for "  . $caps["select"][1] . " (select)");
		$this->assert(!$caps["constant_as"][0], "Excepted false for "  . $caps["constant_as"][1] . " (constant_as)");
		$this->assert(!$caps["math_as"][0], "Excepted false for "  . $caps["math_as"][1] . " (math_as)");
		$this->assert($caps["count"][0], "Excepted true for "  . $caps["count"][1] . " (count)");
		$this->assert(!$caps["sample"][0], "Excepted false for "  . $caps["sample"][1] . " (sample)");
		$this->assert(!$caps["load"][0], "Excepted false for "  . $caps["load"][1] . " (load)");
	}
	
	
}
?>