<?php
/*
SoapWrapper - Wrapper for SoapClient

Copyright (c) 2008 João Gilberto Magalhães

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

class SoapClientWrapper
{
	protected static $_readed = false;
	
	/**
	 * Enter description here...
	 *
	 * @param string $wsdl
	 * @param bool $forceNuSoap
	 * @return SoapClient
	 */
	public static function GetService($wsdl, $forceNuSoap = false)
	{
		if (extension_loaded("soap") && !$forceNuSoap)
		{
			Debug::PrintValue("Local");
			$client = new SoapClient($wsdl);
			return $client;
		}
		else 
		{
			Debug::PrintValue("NuSoap");
			if (!SoapClientWrapper::$_readed)
			{
				//Debug::PrintValue("Read NuSoap");
		    	require_once("nusoap.php");
		    	SoapClientWrapper::$_readed = true;
			}
			$client = new nusoap_client($wsdl, 'wsdl');
			$proxy = $client->getProxy();
			return $proxy;
		}
	}
	
}

?>