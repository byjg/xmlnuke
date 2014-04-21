<?php

use Xmlnuke\Core\Wrapper\ChartWrapper;


/**
 * It is necessary include the file xmlnuke.inc.php do process the request.
 */
#############################################
# To create a XMLNuke capable PHP5 page
#
require_once("xmlnuke.inc.php");
#############################################

ChartWrapper::getInstance()->Process();

