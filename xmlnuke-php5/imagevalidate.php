<?php
#############################################
# To create a XMLNuke capable PHP5 page
#
require_once("xmlnuke.inc.php");
#############################################

require_once(PHPXMLNUKEDIR . "bin/modules/captcha/captcha.class.php");

$context = new Context();

$cq = ($context->ContextValue("xmlnuke.CAPTCHACHALLENGE")!="easy");
$c = intval($context->ContextValue("xmlnuke.CAPTCHALETTERS"));

$captcha = new Captcha($context, $cq, $c);
$captcha->show();
?>