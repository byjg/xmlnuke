<?php

use Xmlnuke\Core\Classes\MailEnvelope;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Util\MailUtil;
/**
 * NOTE: The class name must end with "Test" suffix.
 */
class MailUtilTest extends PHPUnit_Framework_TestCase
{
	const EMAIL_OK = 'joao@byjg.com.br';
	const EMAIL_NOK_1 = 'joao@byjg.com.';
	const EMAIL_NOK_2 = 'joao@byjg@com';
	const EMAIL_NOK_3 = 'joao @ local-';
	const EMAIL_NOK_4 = 'joao@byjg(.111';
	const EMAIL_NOK_5 = 'joao-byjg.com';
	

	// Run before each test case
	function setUp()
	{
	}

	// Run end each test case
	function teardown()
	{
	}

	function test_IsValidEmail()
	{
		$this->assertTrue(MailUtil::isValidEmail(self::EMAIL_OK));
		$this->assertTrue(!MailUtil::isValidEmail(self::EMAIL_NOK_1));
		$this->assertTrue(!MailUtil::isValidEmail(self::EMAIL_NOK_2));
		$this->assertTrue(!MailUtil::isValidEmail(self::EMAIL_NOK_3));
		$this->assertTrue(!MailUtil::isValidEmail(self::EMAIL_NOK_4));
		$this->assertTrue(!MailUtil::isValidEmail(self::EMAIL_NOK_5));
	}
	
	function test_GetFullEmailName()
	{
		$this->assertEquals(MailUtil::getFullEmailName("Joao", "joao@byjg.com.br"), '"Joao" <joao@byjg.com.br>');
		$this->assertEquals(MailUtil::getFullEmailName("", "joao@byjg.com.br"), 'joao@byjg.com.br');
		$this->assertEquals(MailUtil::getFullEmailName(null, "joao@byjg.com.br"), 'joao@byjg.com.br');
	}
	
	function test_GetEmailPair()
	{
		$pair = MailUtil::getEmailPair('"Name" <email@domain.com>');
		$this->assertEquals($pair["name"], 'Name');
		$this->assertEquals($pair["email"], 'email@domain.com');
		
		$pair = MailUtil::getEmailPair('"" <email@domain.com>');
		$this->assertEquals($pair["name"], '');
		$this->assertEquals($pair["email"], 'email@domain.com');
		
		$pair = MailUtil::getEmailPair('<email@domain.com>');
		$this->assertEquals($pair["name"], '');
		$this->assertEquals($pair["email"], 'email@domain.com');

		$pair = MailUtil::getEmailPair('email@domain.com');
		$this->assertEquals($pair["name"], '');
		$this->assertEquals($pair["email"], 'email@domain.com');

		$pair = MailUtil::getEmailPair('"João" <email@domain.com>');
		$this->assertEquals($pair["name"], '=?iso-8859-1?Q?Jo=E3o?=');
		$this->assertEquals($pair["email"], 'email@domain.com');
		
	}
	
	function test_SmtpParts()
	{
		$smtp = array(
			"localhost" => array( "protocol" => "mail", "server" => "localhost"),
			"smtp://host-test" => array( "protocol" => "smtp", "server" => "host-test"),
			"smtp://host.com.br" => array( "protocol" => "smtp", "server" => "host.com.br"),
			"smtp://host.com.br:45" => array( "protocol" => "smtp", "server" => "host.com.br", "port" => "45"),
			"smtp://user@host.com.br:45" => array( "InvalidArgumentException" ),
			"smtp://user:pass@host.com.br:45" => array( "server" => "smtp", "user" => "user", "pass" => "pass", "server" => "host.com.br", "port" => "45"),
			"smtp://us#$%er:pa!*&\$ss@host.com.br:45" => array( "protocol" => "smtp", "user" => "us#$%er", "pass" => "pa!*&\$ss", "server" => "host.com.br", "port" => "45"),
			"smtp://us:er:pass@host.com.br:45" => array( "protocol" => "smtp", "user" => "us:er", "pass" => "pass", "server" => "host.com.br", "port" => "45"),
			"ssl://host.com.br" => array( "protocol" => "ssl", "server" => "host.com.br"),
			"ssl://host.com.br:45" => array( "protocol" => "ssl", "server" => "host.com.br", "port" => "45"),
			"ssl://user@host.com.br:45" => array( "InvalidArgumentException" ),
			"ssl://user:pass@host.com.br:45" => array( "protocol" => "ssl", "user" => "user", "pass" => "pass", "server" => "host.com.br", "port" => "45"),
			"ssl://us#$%er:pa!*&\$ss@host.com.br:45" => array( "protocol" => "ssl", "user" => "us#$%er", "pass" => "pa!*&\$ss", "server" => "host.com.br", "port" => "45"),
		);
		
		foreach ($smtp as $tested=>$expected)
		{
			Context::getInstance()->set("xmlnuke.SMTPSERVER", $tested);
			try
			{
				$mail = new MailEnvelopeMock();
				$parts = MailEnvelopeMock::getSmtpParts();

				foreach ($expected as $key => $value)
				{
					$this->assertEquals($value, $parts[$key]);
				}
			}
			catch (Exception $ex)
			{
				$this->assertEquals(get_class($ex), $expected[0]);
			}
		}
	}
	
}

class MailEnvelopeMock extends MailEnvelope
{
	public static function getSmtpParts()
	{
		return MailEnvelope::$_smtpParts;
	}
	
	public function __construct($to = "", $subject = "", $body = "", $isHtml = false) 
	{
		MailEnvelope::$_smtpParts = null;
		parent::__construct($to, $subject, $body, $isHtml);
	}
}
?>