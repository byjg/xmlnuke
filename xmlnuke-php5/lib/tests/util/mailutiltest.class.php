<?php
/**
 * NOTE: The class name must end with "Test" suffix.
 */
class MailUtilTest extends TestCase
{
	const EMAIL_OK = 'joao@byjg.com.br';
	const EMAIL_NOK_1 = 'joao@byjg.com.';
	const EMAIL_NOK_2 = 'joao@byjg. com';
	const EMAIL_NOK_3 = 'joao@local';
	const EMAIL_NOK_4 = 'joao@byjg.111';
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
		$this->assert(MailUtil::isValidEmail(self::EMAIL_OK), self::EMAIL_OK . " should be valid!");
		$this->assert(!MailUtil::isValidEmail(self::EMAIL_NOK_1), self::EMAIL_NOK_1 . " should not be valid!");
		$this->assert(!MailUtil::isValidEmail(self::EMAIL_NOK_2), self::EMAIL_NOK_2 . " should not be valid!");
		$this->assert(!MailUtil::isValidEmail(self::EMAIL_NOK_3), self::EMAIL_NOK_3 . " should not be valid!");
		$this->assert(!MailUtil::isValidEmail(self::EMAIL_NOK_4), self::EMAIL_NOK_4 . " should not be valid!");
		$this->assert(!MailUtil::isValidEmail(self::EMAIL_NOK_5), self::EMAIL_NOK_5 . " should not be valid!");
	}
	
	function test_GetFullEmailName()
	{
		$this->assert(MailUtil::getFullEmailName("Joao", "joao@byjg.com.br") == '"Joao" <joao@byjg.com.br>', "When name is passed is wrong");
		$this->assert(MailUtil::getFullEmailName("", "joao@byjg.com.br") == 'joao@byjg.com.br', "When name is not passed is wrong (1)");
		$this->assert(MailUtil::getFullEmailName(null, "joao@byjg.com.br") == 'joao@byjg.com.br', "When name is not passed is wrong (2)");
	}
	
	function test_GetEmailPair()
	{
		$pair = MailUtil::getEmailPair('"Name" <email@domain.com>');
		$this->assert($pair["name"] == 'Name', "First extraction: Name is wrong. Found: '" . $pair["name"] .  "' ");
		$this->assert($pair["email"] == 'email@domain.com', "First extraction: Email is wrong. Found: '" . $pair["email"] .  "' ");
		
		$pair = MailUtil::getEmailPair('"" <email@domain.com>');
		$this->assert($pair["name"] == '', "Second extraction: Name is wrong. Found: '" . $pair["name"] .  "' ");
		$this->assert($pair["email"] == 'email@domain.com', "Second extraction: Email is wrong. Found: '" . $pair["email"] .  "' ");
		
		$pair = MailUtil::getEmailPair('<email@domain.com>');
		$this->assert($pair["name"] == '', "Third extraction: Name is wrong. Found: '" . $pair["name"] .  "' ");
		$this->assert($pair["email"] == 'email@domain.com', "Third extraction: Email is wrong. Found: '" . $pair["email"] .  "' ");

		$pair = MailUtil::getEmailPair('email@domain.com');
		$this->assert($pair["name"] == '', "Fourth extraction: Name is wrong. Found: '" . $pair["name"] .  "' ");
		$this->assert($pair["email"] == 'email@domain.com', "Fourth extraction: Email is wrong. Found: '" . $pair["email"] .  "' ");

		$pair = MailUtil::getEmailPair('"João" <email@domain.com>');
		$this->assert($pair["name"] == '=?iso-8859-1?Q?Jo=E3o?=', "Fifth extraction: Name is wrong. Found: '" . $pair["name"] .  "' ");
		$this->assert($pair["email"] == 'email@domain.com', "Fifth extraction: Email is wrong. Found: '" . $pair["email"] .  "' ");
		
	}
	
	function test_SmtpParts()
	{
		$smtp = array(
			"localhost" => array("localhost"),
			"smtp://host.com.br" => array( "", "smtp", "", "", "host.com.br", ""),
			"smtp://host.com.br:45" => array( "", "smtp", "", "", "host.com.br", "45"),
			"smtp://user@host.com.br:45" => array( "Wrong SMTP server definition"),
			"smtp://user:pass@host.com.br:45" => array( "", "smtp", "user", "pass", "host.com.br", "45"),
			"smtp://us#$%er:pa!*&\$ss@host.com.br:45" => array( "", "smtp", "us#$%er", "pa!*&\$ss", "host.com.br", "45"),
			"ssl://host.com.br" => array( "", "ssl", "", "", "host.com.br", ""),
			"ssl://host.com.br:45" => array( "", "ssl", "", "", "host.com.br", "45"),
			"ssl://user@host.com.br:45" => array( "Wrong SMTP server definition"),
			"ssl://user:pass@host.com.br:45" => array( "", "ssl", "user", "pass", "host.com.br", "45"),
			"ssl://us#$%er:pa!*&\$ss@host.com.br:45" => array( "", "ssl", "us#$%er", "pa!*&\$ss", "host.com.br", "45"),
		);
		
		foreach ($smtp as $key=>$value)
		{
			Context::getInstance()->putValue("xmlnuke.SMTPSERVER", $key);
			try
			{
				$mail = new MailEnvelopeMock();
				$parts = MailEnvelopeMock::getSmtpParts();
				for($i=0;$i<count($value);$i++)
				{
					$this->assert($value[$i] == $parts[$i], $key . " => Found '{$value[$i]}' Expected '{$parts[$i]}' ");
				}
			}
			catch (Exception $ex)
			{
				$this->assert($ex->getMessage() == $value[0], "Exception throwed but different message: " . $ex->getMessage());
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