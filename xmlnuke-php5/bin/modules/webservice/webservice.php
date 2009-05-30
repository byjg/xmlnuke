<?php

ini_set('error_reporting',E_ALL^E_NOTICE);
ini_set('error_log','/var/www/error.log');

/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Easy Web Service (SOAP) creation
 *
 * PHP 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Services
 * @package    Webservice
 * @author     Manfred Weber <weber@mayflower.de>
 * @copyright  2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: webservice.php,v 1.1 2006/02/24 18:57:16 jg Exp $
 * @link       http://dschini.org/Services/
 */

// {{{ abstract class Services_WebService

/**
 * PEAR::Services_Webservice
 *
 * The PEAR::Services_WebService class creates web services from your classes
 *
 * @author  Manfred Weber <weber@mayflower.de>
 * @package Webservices
 * @version
 */
abstract class Services_Webservice
{
    /**
     * Namespace of the webservice
     *
     * @var    string
     * @access public
     */
    public $namespace;

    /**
     * Description of the webservice
     *
     * @var    string
     * @access public
     */
    public $description;

    /**
     * Protocol of the webservice
     *
     * @var    string
     * @access public
     */
    public $protocol;


    /**
     * SOAP-server options of the webservice
     *
     * @var    array
     * @access public
     */
    public $soapServerOptions = array();

    /**
     * SOAP schema related URIs
     *
     * @access private
     */
    const SOAP_XML_SCHEMA_VERSION  = 'http://www.w3.org/2001/XMLSchema';
    const SOAP_XML_SCHEMA_INSTANCE = 'http://www.w3.org/2001/XMLSchema-instance';
    const SOAP_SCHEMA_ENCODING   = 'http://schemas.xmlsoap.org/soap/encoding/';
    const SOAP_XML_SCHEMA_MIME   = 'http://schemas.xmlsoap.org/wsdl/mime/';
    const SOAP_ENVELOP           = 'http://schemas.xmlsoap.org/soap/envelope/';
    const SCHEMA_SOAP_HTTP       = 'http://schemas.xmlsoap.org/soap/http';
    const SCHEMA_SOAP            = 'http://schemas.xmlsoap.org/wsdl/soap/';
    const SCHEMA_WSDL            = 'http://schemas.xmlsoap.org/wsdl/';
    const SCHEMA_WSDL_HTTP       = 'http://schemas.xmlsoap.org/wsdl/http/';
    const SCHEMA_DISCO           = 'http://schemas.xmlsoap.org/disco/';
    const SCHEMA_DISCO_SCL       = 'http://schemas.xmlsoap.org/disco/scl/';
    const SCHEMA_DISCO_SOAP      = 'http://schemas.xmlsoap.org/disco/soap/';

    /**
     * Simple WSDL types
     *
     * @var    array
     * @access private
     */
    private $simpleTypes = array(
        'string', 'int', 'float', 'bool', 'double', 'integer', 'boolean',
        'varstring', 'varint', 'varfloat', 'varbool', 'vardouble',
        'varinteger', 'varboolean');

    /**
     * classes are parsed into struct
     *
     * @var    array
     * @access private
     */
    private $wsdlStruct;

    /**
     * disco dom root node
     * the disco dom object
     *
     * @var    object
     * @access private
     */
    private $disco;

    /**
     * wsdl dom root node
     * the wsdl dom object
     *
     * @var    object
     * @access private
     */
    private $wsdl;

    /**
     * wsdl-definitions dom node
     *
     * @var    object
     * @access private
     */
    private $wsdl_definitions;

    /**
     * Name of the class from which to create a webservice from
     *
     * @var    string
     * @access private
     */
    private $classname;

    /**
     * exclude these methods from webservice
     *
     * @var    array
     * @access private
     */
    private $preventMethods;

    /**
     * error namespace
     *
     * @var    bool
     * @access private
     */
    private $warningNamespace;

    /**
     * error description
     *
     * @var    bool
     * @access private
     */
    private $errorDescription;

    /**
     * constructor
     *
     * @var    string
     * @var    string
     * @var    array
     * @access public
     */
    public function __construct($namespace, $description, $options)
    {
        if (isset($namespace) && $namespace != '') {
            $this->warningNamespace   = false;
            $this->errorDescription = false;
            //$namespace .= (substr($namespace, -1) == '/') ? '' : '/';
        } else {
            $this->warningNamespace   = true;
            $this->errorDescription = true;
            $namespace = 'http://example.org/';
        }
        $this->namespace   = $namespace;
        $this->description = ($description != '') ? $description : 'my example service description';
        $this->soapServerOptions = (isset($options) && count($options) > 0) ? $options : array(
            'uri' => $this->namespace,
            'encoding' => SOAP_ENCODED);
        $this->wsdlStruct = array();
        $this->preventMethods = array(
            '__construct',
            '__destruct',
            'handle');
        $this->protocol = 'http';
    }

    // }}}
    // {{{ handle()
    /**
     * handle
     *
     * @access public
     */
    public function handle()
    {
        switch (strtolower($_SERVER['QUERY_STRING'])){
            case 'wsdl':
                $this->intoStruct();
                $this->handleWSDL();
                break;
            case 'disco':
                $this->intoStruct();
                $this->handleDISCO();
                break;
            default:
                $this->intoStruct();
                if (isset($_SERVER['HTTP_SOAPACTION'])) {
                    $this->createServer();
                } elseif ($_REQUEST['httpmethod']) {
                	$this->handleHTTP();
                } else {
                    $this->handleINFO();
                }
                break;
        }
    }

    // }}}
    // {{{ createServer()
    /**
     * create the soap-server
     *
     * @access private
     */
    private function createServer()
    {
        $server = new SoapServer(null, $this->soapServerOptions);
        $server->SetClass($this->classname);
        $server->handle();
    }
    
    // }}}
    // {{{ handleHTTP()
    /**
     * handle HTTP requests to the class
     *
     * @access private
     */
    private function handleHTTP()
    {
    	try 
    	{
			$method = new ReflectionMethod($this->classname, $_REQUEST["httpmethod"]);
    	}
    	catch (Exception $ex)
    	{
    		echo $this->_httpFailure . "Method does not exists";
    		exit;
    	}	
		if (!$method->isPublic())
		{
			echo $this->_httpFailure . "Method does not exists";
			exit;
		}
		
		$params = $method->getParameters();
		$paramValues = array();
		$missingParams = "";
		foreach ($params as $param)
		{
			$paramName = $param->getName();
			$paramValue = ( isset($_REQUEST[$paramName]) ? $_REQUEST[$paramName] : null);
			if (is_null($paramValue) && $param->isDefaultValueAvailable()) 
			{
				$paramValue = $param->getDefaultValue();
			}
			if (is_null($paramValue))
			{
				$missingParams .= ( ($missingParams == "") ? "" : ", " ) . $paramName;
			}
			else 
			{
				$paramValues[$paramName] = $paramValue;
			}
		}
		if ($missingParams != "")
		{
			echo $this->_httpFailure . "Missing params $missingParams";
		}
		else 
		{
			try 
			{
				$result = $method->invokeArgs($this, $paramValues);
				if (is_array($result))
				{
					$str = sizeof($result);
					foreach ($result as $line)
					{
						$str .= "|$line";
					}
					echo $this->_httpSuccess . "$str";
				}
				elseif (is_object($result))
				{
					echo $this->_httpFailure . "Return type is not supported"; 
				}
				else 
				{
					echo $this->_httpSuccess . $result;
				}
			}
			catch (Exception $ex)
			{
				echo $this->_httpFailure . $ex->getMessage();
			}
		}
    }
    protected $_httpSuccess = "OK|";
    protected $_httpFailure = "ERR|";
    
    // }}}
    // {{{ handleWSDL()
    /**
     * handle wsdl
     *
     * @access private
     */
    private function handleWSDL()
    {
        header('Content-Type: text/xml');
        $this->wsdl = new DOMDocument('1.0' ,'utf-8');
        $this->createWSDL_definitions();
        $this->createWSDL_types();
        $this->createWSDL_messages();
        $this->createWSDL_portType();
        $this->createWSDL_binding();
        $this->createWSDL_service();
        echo $this->wsdl->saveXML();
    }

    // }}}
    // {{{ createDISCO()
    /**
     * handle disco
     *
     * @access private
     */
    private function handleDISCO()
    {
        header('Content-Type: text/xml');
        $this->disco = new DOMDocument('1.0' ,'utf-8');
        $disco_discovery = $this->disco->createElement('discovery');
        $disco_discovery->setAttribute('xmlns:xsi', self::SOAP_XML_SCHEMA_INSTANCE);
        $disco_discovery->setAttribute('xmlns:xsd', self::SOAP_XML_SCHEMA_VERSION);
        $disco_discovery->setAttribute('xmlns', self::SCHEMA_DISCO );
        $disco_contractref = $this->disco->createElement('contractRef');
        $urlBase = $this->protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $disco_contractref->setAttribute('ref', $urlBase . '?wsdl');
        $disco_contractref->setAttribute('docRef', $urlBase);
        $disco_contractref->setAttribute('xmlns', self::SCHEMA_DISCO_SCL);
        $disco_soap = $this->disco->createElement('soap');
        $disco_soap->setAttribute('address', $urlBase);
        $disco_soap->setAttribute('xmlns:q1', $this->namespace);
        $disco_soap->setAttribute('binding', 'q1:' . $this->classname);
        $disco_soap->setAttribute('xmlns', self::SCHEMA_DISCO_SCL);
        $disco_contractref->appendChild($disco_soap);
        $disco_discovery->appendChild($disco_contractref);
        $this->disco->appendChild($disco_discovery);
        echo $this->disco->saveXML();
    }

    // }}}
    // {{{ handleINFO()
    /**
     * handle info-site
     *
     * @access private
     */
    private function handleINFO()
    {
        header('Content-Type: text/html');

        $css = '
body {
    margin: 0px;
    padding: 10px;
    font-family: sans-serif;
}
#header {
    background-color: #339900;
    color: #FFFFFF;
    padding: 5px 10px;
    margin: -10px;
}
h1 {
    font-size: xx-large;
    color: #CCFF99;
}
#header p {
    font-size: large;
}

dt {
    margin-top: 1em;
}

.description {
    padding-left: 1.5em;
    margin-bottom: 1.5em;
}

a:link {
    color: #006600;
}

a:visited {
    color: #030;
}

a:hover {
    color: #003300;
}
';

        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>' . $this->classname . ' WebService</title>
<meta name="generator" content="PEAR::Services_Webservice" />
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<style type="text/css">
' . $css . '
</style>
</head>
<body>
<div id="header">
<h1>' . $this->classname . '</h1>
<p>' . htmlspecialchars($this->description) . '</p>
</div>
<p>The following operations are supported. For a formal definition, please review the <a href="' . htmlentities($_SERVER['PHP_SELF']) . '?WSDL">Service Description</a>.</p>
<ul>';

        foreach ($this->wsdlStruct[$this->classname]['method'] as $methodName => $method) {
            $paramValue = array();
            foreach ($method['var'] AS $methodVars) {
                if (isset($methodVars['param'])) {
                    $paramValue[] = $methodVars['type']
                                     . str_repeat('[]', $methodVars['length'])
                                     . " " . $methodVars['name'];
                }
            }
            $returnValue = array();
            foreach ($method['var'] AS $methodVars) {
                if (isset($methodVars['return'])) {
                    $returnValue[] = $methodVars['type']
                                     . str_repeat('[]', $methodVars['length']);
                }
            }
            echo sprintf('<li><samp><var class="returnedValue">%s</var> <b class="functionName">%s</b>( <var class="parameter">%s</var> )</samp>%s</li>'
                    , implode(',', $returnValue)
                    , $methodName
                    , implode('</var> , <var class="parameter">', $paramValue)
                    , ((empty($method['description'])) ? '' : ('<br /><span class="description">' . htmlspecialchars($method['description']) . '</span>')));
        }
        echo '</ul>
<p><a href="' . htmlentities($_SERVER['PHP_SELF']) . '?DISCO">DISCO</a> makes it possible for clients to reflect against endpoints to discover services and their associated <acronym title="Web Service Description Language">WSDL</acronym> documents.</p>';

        if ($this->warningNamespace == true
            || $this->namespace == 'http://example.org/') {
            echo '
<p class="warning"><strong>This web service is using http://example.org/ as its default namespace.<br />
Recommendation: Change the default namespace before the <acronym title="eXtensible Markup Language">XML</acronym> Web service is made public.</strong></p>

<p>Each XML Web service needs a unique namespace in order for client applications to distinguish it from other services on the Web. http://example.org/ is available for XML Web services that are under development, but published XML Web services should use a more permanent namespace.<br />
Your XML Web service should be identified by a namespace that you control. For example, you can use your company`s Internet domain name as part of the namespace. Although many XML Web service namespaces look like <acronym title="Uniform Resource Locators">URLs</acronym>, they need not point to actual resources on the Web. (XML Web service namespaces are <acronym title="Uniform Resouce Identifiers">URIs</acronym>.)</p>

<p>For more details on XML namespaces, see the <acronym title="World Wide Web Consortium">W3C</acronym> recommendation on <a href="http://www.w3.org/TR/REC-xml-names/">Namespaces in XML</a>.<br />
For more details on <acronym title="Web Service Description Language">WSDL</acronym>, see the <a href="http://www.w3.org/TR/wsdl">WSDL Specification</a>.<br />
For more details on URIs, see <a href="http://www.ietf.org/rfc/rfc2396.txt"><acronym title="Request For Comment">RFC</acronym> 2396</a>.</p>
<p><small>Powered by PEAR <a href="http://pear.php.net/">http://pear.php.net</a></small></p>
</body>
</html>';

        }
    }

    // }}}
    // {{{ intoStruct()
    /**
     * parse classes into struct
     *
     * @access private
     */
    protected function intoStruct()
    {
        $class = new ReflectionObject($this);
        $this->classname = $class->getName();
        $this->classMethodsIntoStruct();
        $this->classStructDispatch();
    }

    // }}}
    // {{{ classStructDispatch()
    /**
     * dispatch types
     *
     * @access private
     */
    protected function classStructDispatch()
    {
        foreach ($this->wsdlStruct[$this->classname]['method'] as $method) {
            foreach ($method['var'] as $var){
                if (($var['class'] == 1 && $var['length'] == 0)
                    || ($var['class'] == 1 && $var['length'] > 0)) {
                    $this->classPropertiesIntoStruct($var['type']);
                }
                if (($var['array'] == 1 && $var['length'] > 0)
                    || ($var['class'] == 1 && $var['length'] > 0)) {
                    $_typensSource = '';
                    for ($i = $var['length']; $i > 0; --$i) {
                        $_typensSource .= 'ArrayOf';
                        $this->wsdlStruct['array'][$_typensSource . $var['type']] = substr($_typensSource, 0, strlen($_typensSource) - 7) . $var['type'];
                    }
                }
            }
        }
    }

    // }}}
    // {{{ classPropertiesIntoStruct()
    /**
     * parse classes properties into struct
     *
     * @var    string
     * @access private
     */
    protected function classPropertiesIntoStruct($className)
    {
        if (!isset($this->wsdlStruct[$className])) {
            $class = new ReflectionClass($className);
            $properties = $class->getProperties();
            $this->wsdlStruct['class'][$className]['property'] = array();
            for ($i = 0; $i < count($properties); ++$i) {
                if ($properties[$i]->isPublic()) {
                    preg_match_all('~@var\s(\S+)~', $properties[$i]->getDocComment(), $var);

                    $_cleanType = str_replace('[]', '', $var[1][0], $_length);
                    $_typens    = str_repeat('ArrayOf', $_length);

                    $this->wsdlStruct['class'][$className]['property'][$properties[$i]->getName()]['type'] =
                            $_cleanType;
                    $this->wsdlStruct['class'][$className]['property'][$properties[$i]->getName()]['wsdltype'] =
                            $_typens.$_cleanType;
                    $this->wsdlStruct['class'][$className]['property'][$properties[$i]->getName()]['length'] =
                            $_length;
                    $this->wsdlStruct['class'][$className]['property'][$properties[$i]->getName()]['array'] =
                            ($_length > 0 && in_array($_cleanType, $this->simpleTypes))
                            ? true : false;
                    $isObject = (!in_array($_cleanType, $this->simpleTypes) && new ReflectionClass($_cleanType))
                            ? true : false;
                    $this->wsdlStruct['class'][$className]['property'][$properties[$i]->getName()]['class'] =
                            $isObject;
                    if ($isObject == true) {
                        $this->classPropertiesIntoStruct($_cleanType);
                    }
                    if ($_length > 0) {
                        $_typensSource = '';
                        for ($j = $_length; $j > 0;  --$j) {
                            $_typensSource .= 'ArrayOf';
                            $this->wsdlStruct['array'][$_typensSource.$_cleanType] =
                                    substr($_typensSource, 0, strlen($_typensSource) - 7) . $_cleanType;
                        }
                    }
                }
            }
        }
    }

    // }}}
    // {{{ classMethodsIntoStruct()
    /**
     * parse classes methods into struct
     *
     * @access private
     */
    protected function classMethodsIntoStruct()
    {
        $class = new ReflectionClass($this->classname);
        $methods = $class->getMethods();
        // params
        foreach ($methods AS $method) {
            if ($method->isPublic()
                && !in_array($method->getName(), $this->preventMethods)) {
                $docComments = $method->getDocComment();
                $_docComments_Description = trim(str_replace('/**', '', substr($docComments, 0, strpos($docComments, '@'))));
                $docComments_Description = trim(substr($_docComments_Description, strpos($_docComments_Description, '*') + 1, strpos($_docComments_Description, '*', 1) - 1));
                $this->wsdlStruct[$this->classname]['method'][$method->getName()]['description'] = $docComments_Description;
                preg_match_all('~@param\s(\S+)~', $docComments, $param);
                preg_match_all('~@return\s(\S+)~', $method->getDocComment(), $return);
                $params = $method->getParameters();
                for ($i = 0; $i < count($params); ++$i) {
                    $_class = $params[$i]->getClass();
                    $_type  = ($_class) ? $_class->getName() : $param[1][$i];

                    $_cleanType = str_replace('[]', '', $_type, $_length);
                    $_typens    = str_repeat('ArrayOf', $_length);

                    $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['name'] =
                            $params[$i]->getName();
                    $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['wsdltype'] =
                            $_typens . $_cleanType;
                    $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['type'] =
                            $_cleanType;
                    $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['length'] =
                            $_length;
                    $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['array'] =
                            ($_length > 0 && in_array($_cleanType, $this->simpleTypes))
                            ? true : false;
                    $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['class'] =
                            (!in_array($_cleanType, $this->simpleTypes) && new ReflectionClass($_cleanType))
                            ? true : false;
                    $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['param'] = true;
                }
                // return
                if (isset($return[1][0])) {
                    $_cleanType = str_replace('[]', '', $return[1][0], $_length);
                } else {
                    $_cleanType = 'void';
                    $_length = 0;
                }
                $_typens = str_repeat('ArrayOf', $_length);

                $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['wsdltype'] =
                        $_typens.$_cleanType;
                $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['type'] = $_cleanType;
                $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['length'] = $_length;
                $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['array'] =
                        ($_length > 0 && $_cleanType != 'void' && in_array($_cleanType, $this->simpleTypes)) ? true : false;
                $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['class'] =
                        ($_cleanType != 'void' && !in_array($_cleanType, $this->simpleTypes) && new ReflectionClass($_cleanType))
                        ? true : false;
                $this->wsdlStruct[$this->classname]['method'][$method->getName()]['var'][$i]['return'] = true;
            }
        }
    }

    // }}}
    // {{{ createWSDL_definitions()
    /**
     * Create the definition node
     *
     * @return void
     */
    protected function createWSDL_definitions()
    {
		/*
		<definitions name="myService"
		    targetNamespace="urn:myService"
		    xmlns:typens="urn:myService"
		    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
		    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
		    xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
		    xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
		    xmlns="http://schemas.xmlsoap.org/wsdl/">
		*/
		
        $this->wsdl_definitions = $this->wsdl->createElement('definitions');
        $this->wsdl_definitions->setAttribute('name', $this->classname);
        $this->wsdl_definitions->setAttribute('targetNamespace', 'urn:'.$this->classname);
        $this->wsdl_definitions->setAttribute('xmlns:typens', 'urn:'.$this->classname);
        $this->wsdl_definitions->setAttribute('xmlns:xsd', self::SOAP_XML_SCHEMA_VERSION);
        $this->wsdl_definitions->setAttribute('xmlns:soap', self::SCHEMA_SOAP);
        $this->wsdl_definitions->setAttribute('xmlns:soapenc', self::SOAP_SCHEMA_ENCODING);
        $this->wsdl_definitions->setAttribute('xmlns:wsdl', self::SCHEMA_WSDL);
        $this->wsdl_definitions->setAttribute('xmlns', self::SCHEMA_WSDL);
        
        //$this->wsdl_definitions->setAttribute('xmlns:mime', self::SOAP_XML_SCHEMA_MIME);
        //$this->wsdl_definitions->setAttribute('xmlns:tns', $this->namespace);
        //$this->wsdl_definitions->setAttribute('xmlns:http', self::SCHEMA_WSDL_HTTP);
        
        $this->wsdl->appendChild($this->wsdl_definitions);
    }

    // }}}
    // {{{ createWSDL_types()
    /**
     * Create the types node
     *
     * @return void
     */
    protected function createWSDL_types()
    {
    	/*
		<types>
        	<xsd:schema xmlns="http://www.w3.org/2001/XMLSchema" targetNamespace="urn:myService"/>
        </types>
    	*/
        $types  = $this->wsdl->createElement('types');
        $schema = $this->wsdl->createElement('xsd:schema');
        $schema->setAttribute('xmlns', self::SOAP_XML_SCHEMA_VERSION );
        $schema->setAttribute('targetNamespace', 'urn:'.$this->classname);
        $types->appendChild($schema);

        // array
        /*
        <xsd:complexType name="ArrayOfclassC">
            <xsd:complexContent>
                <xsd:restriction base="soapenc:Array">
                    <xsd:attribute ref="soapenc:arrayType" wsdl:arrayType="typens:classC[]"/>
                </xsd:restriction>
            </xsd:complexContent>
        </xsd:complexType>
        */
        if (isset($this->wsdlStruct['array'])) {

	        foreach ($this->wsdlStruct['array'] as $source => $target) {
	        	
	        	//<s:complexType name="ArrayOfArrayOfInt">
				//<s:sequence>
				//<s:element minOccurs="0" maxOccurs="unbounded" name="ArrayOfInt" nillable="true" type="tns:ArrayOfInt"/>
				//</s:sequence>
	        	
	            $complexType 	= $this->wsdl->createElement('xsd:complexType');
	            $complexContent = $this->wsdl->createElement('xsd:complexContent');
	            $restriction 	= $this->wsdl->createElement('xsd:restriction');
	            $attribute 		= $this->wsdl->createElement('xsd:attribute');
	            $restriction->appendChild($attribute);
	            $complexContent->appendChild($restriction);
	            $complexType->appendChild($complexContent);
	            $schema->appendChild($complexType);
	            
	            $complexType->setAttribute('name', $source);
	            $restriction->setAttribute('base', 'soapenc:Array');
	            $attribute->setAttribute('ref', 'soapenc:arrayType');

	            try {
	            	$class = new ReflectionClass($target);
	            }catch (Exception $e){}
	            
	            if(in_array($target, $this->simpleTypes)){
		            $attribute->setAttribute('wsdl:arrayType', 'xsd:'.$target.'[]');
	            }elseif(isset($class)){
		            $attribute->setAttribute('wsdl:arrayType', 'typens:'.$target.'[]');
	            }else{
		            $attribute->setAttribute('wsdl:arrayType', 'typens:'.$target.'[]');
	            }
	            unset($class);
	            
	        }
        }
        
        // class
        /*
        <xsd:complexType name="classB">
            <xsd:all>
                <xsd:element name="classCArray" type="typens:ArrayOfclassC" />
            </xsd:all>
        </xsd:complexType>
        */
        if (isset($this->wsdlStruct['class'])) {
            foreach ($this->wsdlStruct['class'] as $className=>$classProperty) {
                $complextype = $this->wsdl->createElement('xsd:complexType');
                $complextype->setAttribute('name', $className);
                $sequence = $this->wsdl->createElement('xsd:all');
                $complextype->appendChild($sequence);
                $schema->appendChild($complextype);
                foreach ($classProperty['property'] as $classPropertyName => $classPropertyValue) {
                    $element = $this->wsdl->createElement('xsd:element');
                    $element->setAttribute('name', $classPropertyName);
                    $element->setAttribute('type', ((in_array($classPropertyValue['wsdltype'], $this->simpleTypes)) 
                    										? 'xsd:' 
                    										: 'typens:') . $classPropertyValue['wsdltype']);
                    $sequence->appendChild($element);
                }
            }
        }

        $this->wsdl_definitions->appendChild($types);
    }

    // }}}
    // {{{ createWSDL_messages()
    /**
     * Create the messages node
     *
     * @return void
     */
    protected function createWSDL_messages()
    {
    	/*
	    <message name="hello">
	        <part name="i" type="xsd:int"/>
	        <part name="j" type="xsd:string"/>
	    </message>
	    <message name="helloResponse">
	        <part name="helloResponse" type="xsd:string"/>
	    </message>
	    */
        foreach ($this->wsdlStruct[$this->classname]['method'] AS $methodName => $method){
            $messageInput = $this->wsdl->createElement('message');
            $messageInput->setAttribute('name', $methodName);
            $messageOutput = $this->wsdl->createElement('message');
            $messageOutput->setAttribute('name', $methodName . 'Response');
            $this->wsdl_definitions->appendChild($messageInput);
            $this->wsdl_definitions->appendChild($messageOutput);

            foreach ($method['var'] as $methodVars) {            	
                if (isset($methodVars['param'])) {
                    $part = $this->wsdl->createElement('part');
                    $part->setAttribute('name', $methodVars['name']);
                    $part->setAttribute('type', (($methodVars['array'] != 1 && $methodVars['class'] != 1)
                        ? 'xsd:' : 'typens:') . $methodVars['wsdltype']);
            		$messageInput->appendChild($part);
                }
                if (isset($methodVars['return'])) {
                    $part = $this->wsdl->createElement('part');
                    $part->setAttribute('name', $methodName.'Response'); //$methodVars['wsdltype']);
                    $part->setAttribute('type', (($methodVars['array'] != 1 && $methodVars['class'] != 1)
                        ? 'xsd:' : 'typens:') . $methodVars['wsdltype']);
            		$messageOutput->appendChild($part);
                }
            }
        }
    }

	// }}}
    // {{{ createWSDL_binding()
    /**
     * Create the binding node
     *
     * @return void
     */
    protected function createWSDL_binding()
    {
    	/*
	    <binding name="myServiceBinding" type="typens:myServicePort">    
	        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
	            <operation name="hello">
	                <soap:operation soapAction="urn:myServiceAction"/>
					<input>
					    <soap:body use="encoded" namespace="urn:myService" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
	                </input>
	                <output>
	                    <soap:body use="encoded" namespace="urn:myService" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
	                </output>
	        </operation>
	    </binding>
	    */
		$binding = $this->wsdl->createElement('binding');
        $binding->setAttribute('name', $this->classname . 'Binding');
        $binding->setAttribute('type', 'typens:' . $this->classname . 'Port');
        $soap_binding = $this->wsdl->createElement('soap:binding');
        $soap_binding->setAttribute('style', 'rpc');
        $soap_binding->setAttribute('transport', self::SCHEMA_SOAP_HTTP);
        $binding->appendChild($soap_binding);
        foreach ($this->wsdlStruct[$this->classname]['method'] AS $methodName => $methodVars) {
            $operation = $this->wsdl->createElement('operation');
            $operation->setAttribute('name', $methodName);
            $binding->appendChild($operation);
            $soap_operation = $this->wsdl->createElement('soap:operation');
            $soap_operation->setAttribute('soapAction', 'urn:'.$this->classname.'Action');
            $operation->appendChild($soap_operation);            
            $input  = $this->wsdl->createElement('input');
            $output = $this->wsdl->createElement('output');
            $operation->appendChild($input);
            $operation->appendChild($output);
            $soap_body = $this->wsdl->createElement('soap:body');
            $soap_body->setAttribute('use', 'encoded');
            $soap_body->setAttribute('namespace', 'urn:'.$this->namespace);
            $soap_body->setAttribute('encodingStyle', self::SOAP_SCHEMA_ENCODING );
            $input->appendChild($soap_body);
            $soap_body = $this->wsdl->createElement('soap:body');
            $soap_body->setAttribute('use', 'encoded');
            $soap_body->setAttribute('namespace', 'urn:'.$this->namespace);
            $soap_body->setAttribute('encodingStyle', self::SOAP_SCHEMA_ENCODING );
            $output->appendChild($soap_body);
        }
        $this->wsdl_definitions->appendChild($binding);
    }

    // }}}
    // {{{ createWSDL_portType()
    /**
     * Create the portType node
     *
     * @return void
     */
    protected function createWSDL_portType()
    {
    	/*
	    <portType name="myServicePort">
	        <operation name="hello">
	            <input message="typens:hello"/>
	            <output message="typens:helloResponse"/>
	        </operation>
	    </portType>
	    */
        $portType = $this->wsdl->createElement('portType');
        $portType->setAttribute('name', $this->classname.'Port');
        foreach ($this->wsdlStruct[$this->classname]['method'] AS $methodName => $methodVars) {
            $operation = $this->wsdl->createElement('operation');
            $operation->setAttribute('name', $methodName);
            $portType->appendChild($operation);

            $documentation = $this->wsdl->createElement('documentation');
            $documentation->appendChild($this->wsdl->createTextNode($methodVars['description']));
            $operation->appendChild($documentation);

            $input  = $this->wsdl->createElement('input');
            $output = $this->wsdl->createElement('output');
            $input->setAttribute('message', 'typens:' . $methodName );
            $output->setAttribute('message', 'typens:' . $methodName . 'Response');
            $operation->appendChild($input);
            $operation->appendChild($output);
        }
        $this->wsdl_definitions->appendChild($portType);
    }

    // }}}
    // {{{ createWSDL_service()
    /**
     * Create the service node
     *
     * @return void
     */
    protected function createWSDL_service()
    {
    	/*
	    <service name="myService">
	        <port name="myServicePort" binding="typens:myServiceBinding">
	            <soap:address location="http://dschini.org/test1.php"/>
	        </port>
	    </service>
        */
        $service = $this->wsdl->createElement('service');
        $service->setAttribute('name', $this->classname);
        $port = $this->wsdl->createElement('port');
        $port->setAttribute('name', $this->classname . 'Port');
        $port->setAttribute('binding', 'typens:' . $this->classname . 'Binding');
        $adress = $this->wsdl->createElement('soap:address');
        $adress->setAttribute('location', $this->protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        $port->appendChild($adress);
        $service->appendChild($port);
        $this->wsdl_definitions->appendChild($service);
    }
}

?>
