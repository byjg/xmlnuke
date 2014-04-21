<?php

use Xmlnuke\Core\Wrapper\SOAPWrapper;


/**
 * It is necessary include the file xmlnuke.inc.php do process the request.
 */
#############################################
# To create a XMLNuke capable PHP5 page
#
require_once("xmlnuke.inc.php");
#############################################

SOAPWrapper::getInstance()->Process();
