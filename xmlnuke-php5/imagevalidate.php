<?php

use Captcha\Captcha;
use Xmlnuke\Core\Engine\Context;

#############################################
# To create a XMLNuke capable PHP5 page
#
require_once("xmlnuke.inc.php");
#############################################

$context = Context::getInstance();

$cq = ($context->get("xmlnuke.CAPTCHACHALLENGE")!="easy");
$c = intval($context->get("xmlnuke.CAPTCHALETTERS"));

$captcha = new Captcha($context, $cq, $c);
$captcha->show();
?>