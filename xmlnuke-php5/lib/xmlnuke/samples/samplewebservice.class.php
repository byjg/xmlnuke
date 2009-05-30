<?php
class SampleWebservice extends Services_Webservice
{

    /**
    * Return the Status of Service
    * @return string
    */
	public function getStatus()
	{
		return "Sample Is OK";
	}

	/**
	 * Ecoes a text
	 *
	 * @param string $text
	 * @return string
	 */
	public function getEcho($text)
	{
		return "Your text is '$text'";
	}
	
	/**
	 * Return a single array
	 *
	 * @param int $dimension
	 * @return string[]
	 */
	public function getArray($dimension)
	{
		$arr = array();
		
		for($i=0;$i<$dimension;$i++)
		{
			$arr[] = $dimension-$i;
		}
		
		return $arr;
	}

	/**
	 * Return sum of A plus B
	 *
	 * @param int $intA
	 * @param int $intB
	 * @return int
	 */
	public function Sum($intA, $intB)
	{
		return ($intA + $intB);
	}
	
	/**
	 * Join an Array of Strings
	 *
	 * @param string[] $array
	 * @return string
	 */
	public function JoinArray($array)
	{
		return join("|", $array);
	}
}

$myService = new SampleWebservice(
	"http://www.xmlnuke.com",
	"Sample class to create a WebService using XMLNuke facilities. To acess this module you " .
	" *must* call: webservice.php/namespace.webservice",
	array('uri' => 'http://www.xmlnuke.com','encoding'=>SOAP_ENCODED ));
$myService->handle();

?>