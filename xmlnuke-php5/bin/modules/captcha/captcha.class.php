<?php
/**
 * Captcha image generator. Orginal Alejandro Fernandez Moraga
 * Changed by Joao Gilberto Magalhaes on May/2009 for XMLNuke Project
 *
 * @author Alejandro Fernandez Moraga
 * @see http://www.moraga.com.br/89/captcha-em-php
 */

class Captcha {

	private $text;
	private $image;
	private $question;

	/**
	 * @param Context $context
	 * @param bool $useQuestion
	 * @param int $characters
	 */
	public function __construct($context, $useQuestion = true, $characters=5)
	{
		$mywords = LanguageFactory::GetLanguageCollection($context, LanguageFileTypes::OBJECT, "captcha");

		if ($characters < 5) $characters = 5;

		$font = array();
		$font[] = PHPXMLNUKEDIR . 'bin/modules/captcha/arial_black.ttf';
		$font[] = PHPXMLNUKEDIR . 'bin/modules/captcha/elephant.ttf';
		$font[] = PHPXMLNUKEDIR . 'bin/modules/captcha/distress.ttf';
		$font[] = PHPXMLNUKEDIR . 'bin/modules/captcha/dsmoster.ttf';
		$font2 = PHPXMLNUKEDIR . 'bin/modules/captcha/arial.ttf';

		$pos_ini = 20;
		$font_len = 32+rand(-3, 3);

		$letters = array(
			array('A', 'E', 'I', 'O', 'U'),
			array('B', 'C', 'D', 'F', 'G', 'H', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'X', 'Z')
		);

		$colors = array(
			array(20, 35, 40),
			array(235+rand(-20, 20), 0, 0),
			array(0, 235+rand(-20, 20), 0),
			array(0, 0, 235+rand(-20, 20))
		);
		$colors_nam = array($mywords->Value('BLACK'), $mywords->Value('RED'), $mywords->Value('GREEN'), $mywords->Value('BLUE'));
		$colors_len = count($colors);

		$maxwidth = $characters * $font_len + $pos_ini * 2;
		$this->image = imagecreatetruecolor($maxwidth, 80);
		$backColor = imagecolorallocate($this->image, 230+rand(-20, 20), 230+rand(-20, 20), 230+rand(-20, 20));
		imagefilledrectangle($this->image, 0, 0, $maxwidth, 80, $backColor);


		$avail = array(array() /* letters */, array() /* colors */);

		$generatedText = "";

		for ($i=0; $i < $characters; $i++) {
			$letter_type = rand(0, 1);
			$letter = $letters[$letter_type][rand(0, count($letters[$letter_type]) - 1)];
			$generatedText .= $letter;

			if (empty($avail[0][$letter_type]))
				$avail[0][$letter_type] = $letter;
			else
				$avail[0][$letter_type] .= $letter;

			$color = rand(0, $colors_len - 1);
			if (empty($avail[1][$color]))
				$avail[1][$color] = $letter;
			else
				$avail[1][$color] .= $letter;
			list($r, $g, $b) = $colors[$color];

			imagettftext($this->image, 30, rand(-35, 35),
				$pos_ini + ($font_len * $i), 45,
					imagecolorallocate($this->image, $r, $g, $b), $font[rand(0, count($font)-1)], $letter);
		}

		if ($useQuestion)
		{
			$ask = rand(0, 1);
			$options = array_keys($avail[$ask]);
			$type = $options[rand(0, count($options) - 1)];
			$this->text = $avail[$ask][$type];
			$question = $ask ? $colors_nam[$type] : ($type ? $mywords->Value("CONSONANT") : $mywords->Value("VOWEL"));
			$this->question = $mywords->Value("CHALLENGEQUESTION", $question);
		}
		else
		{
			$this->text = $generatedText;
			$this->question = $mywords->Value("SIMPLEQUESTION");
		}

		// Question
		$startx = 5+rand(-4,6);
		$starty = 72+rand(-4,4);
		imagettftext($this->image, 10.5, 0, $startx, $starty, imagecolorallocate($this->image, 0, 0, 0), $font2, $this->question);
		$color = imagecolorallocate($this->image, $r*rand(0,1), $g*rand(0,1), $b*rand(0,1));
		imagettftext($this->image, 10.5, -0.5*(1-rand(0,1)), $startx+1, $starty+1, $color, $font2, $this->question);

		for ($i=0;$i<10;$i++)
		{
			$x1 = rand(0, $maxwidth);
			$y1 = rand(0, 70);
			$x2 = rand(0, $maxwidth);
			$y2 = rand(0, 70);
			$color = imagecolorallocate($this->image, 255*rand(0,1), 255*rand(0,1), 255*rand(0,1));
			imageline($this->image, $x1, $y1, $x2, $y2, $color);
		}

		$x1 = rand(0, 30);
		$y1 = rand(65, 75);
		$x2 = rand($maxwidth-50, $maxwidth);
		$y2 = rand(65, 75);
		$color = imagecolorallocate($this->image, 255*rand(0,1), 255*rand(0,1), 255*rand(0,1));
		imageline($this->image, $x1, $y1, $x2, $y2, $color);

	}

	public function text() {
		return strtolower($this->text);
	}

	public function question() {
		return $this->question;
	}

	public function show() {
		ob_clean();
		header('Pragma: no-cache');
		header('Cache-Control: private, no-cache, no-cache="Set-Cookie", proxy-revalidate');
		header('Content-type: image/png');
		imagepng($this->image);
		imagedestroy($this->image);
		$_SESSION['captchaimg'] = $this->text();
	}

	public static function TextIsValid($text)
	{
		$captchaimg = $_SESSION['captchaimg'];
		$_SESSION['captchaimg'] = null;
		return (strtolower($text) == $captchaimg);
	}
}

/*
$captcha = new Captcha(false, 5);

// inicia a sessão
session_start();

//echo $captcha->question() . " = " . $captcha->text();
$captcha->show();
*/

/**
-----------------------------------------------
$captcha = new Captcha();

// inicia a sessão
session_start();

// Grava a resolução em sessão
$_SESSION['captcha'] = $captcha->text();

$captcha->show();
-----------------------------------------------
**/



/**
-----------------------------------------------
<img id="captcha" src="Captcha.php">
<input type="button" value="Problemas com a imagem?" onclick="javascript: document.getElementById('captcha').src = 'Captcha.php?' + Math.random() ">
-----------------------------------------------
**/

?>
