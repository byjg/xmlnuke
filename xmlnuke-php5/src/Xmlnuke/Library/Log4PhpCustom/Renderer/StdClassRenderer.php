<?php

namespace Log4PhpCustom\Renderer;

/** All renderers must implement the LoggerRenderer interface. */
class StdClassRenderer implements \LoggerRenderer {
    public function render($obj) {
		if (isset($obj->debugXmlnuke))
		{
			$ret = "";
			
			if (isset($obj->title) && $obj->title != '')
				$ret = "<div style='display: block'><b>" . $obj->title . "</b>: </div>";
					
			if ($obj->preserve)
				$ret .= "<pre>" . $obj->contents . "</pre>";
			else
				$ret .= $obj->contents;

			return $ret;
		}
		else
			return print_r($obj, true);
    }
}
