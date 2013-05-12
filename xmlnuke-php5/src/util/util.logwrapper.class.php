<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A XML site content management.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*
*  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
*  for more information.
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*/


require_once(PHPXMLNUKEDIR . 'src/modules/log4php/Logger.php');

class LogWrapper
{
	/**
	 *
	 * @param string $logName
	 * @return Logger
	 */
	public static function getLogger($logName = 'default')
	{
		$filename = new LogConfigFilenameProcessor('log4php');

		if (!$filename->Exists())
		{

		}
		else
			Logger::configure($filename->FullQualifiedNameAndPath());
		
		return Logger::getLogger($logName);
	}

	public function trace($message)
	{
		$_args = func_get_args();

		$arg = array_shift($_args);
		$this->_logger->trace($arg);

		if (count($_args) > 0)
			call_user_func_array (array($this, 'trace'), $_args);
	}

	public function debug($message)
	{
		$_args = func_get_args();

		$arg = array_shift($_args);
		$this->_logger->debug($arg);

		if (count($_args) > 0)
			call_user_func_array (array($this, 'debug'), $_args);
	}

	public function info($message)
	{
		$_args = func_get_args();

		$arg = array_shift($_args);
		$this->_logger->info($arg);

		if (count($_args) > 0)
			call_user_func_array (array($this, 'info'), $_args);
	}

	public function warn($message, $throwable = null)
	{
		$this->_logger->warn($message, $throwable);
	}

	public function error($message, $throwable = null)
	{
		$this->_logger->error($message, $throwable);
	}

	public function fatal($message, $throwable = null)
	{
		$this->_logger->fatal($message, $throwable);
	}

}

?>
