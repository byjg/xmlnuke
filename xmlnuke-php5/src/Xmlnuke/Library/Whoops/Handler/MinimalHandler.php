<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Handler;
use Whoops\Handler\Handler;
use Whoops\Exception\Frame;

/**
 * Catches an exception and converts it to a JSON
 * response. Additionally can also return exception
 * frames for consumption by an API.
 */
class MinimalHandler extends Handler
{
    /**
     * @var bool
     */
    private $returnFrames = false;

    /**
     * @var bool
     */
    private $onlyForAjaxRequests = false;

    /**
     * @param  bool|null $returnFrames
     * @return null|bool
     */
    public function addTraceToOutput($returnFrames = null)
    {
        if(func_num_args() == 0) {
            return $this->returnFrames;
        }

        $this->returnFrames = (bool) $returnFrames;
    }

    /**
     * @param  bool|null $onlyForAjaxRequests
     * @return null|bool
     */
    public function onlyForAjaxRequests($onlyForAjaxRequests = null)
    {
        if(func_num_args() == 0) {
            return $this->onlyForAjaxRequests;
        }

        $this->onlyForAjaxRequests = (bool) $onlyForAjaxRequests;
    }

    /**
     * Check, if possible, that this execution was triggered by an AJAX request.
     *
     * @return bool
     */
    private function isAjaxRequest()
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        ;
    }

    /**
     * @return int
     */
    public function handle()
    {
        $exception = $this->getException();
		echo trim(
		"<style>
			body {
				font-size: 1.2em;
				font-family: sans-serif;
			}
			h2 {
				font-size: 2em;
				margin-bottom: 12px;
			}
		</style>");

        echo '<h2>' . get_class($exception). '</h2>';
		echo $exception->getMessage();
        return Handler::QUIT;		
    }
}
