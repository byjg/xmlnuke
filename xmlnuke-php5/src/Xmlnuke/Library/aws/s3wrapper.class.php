<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
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

class S3Wrapper
{

	/**
	 *
	 * @var AWSAuth
	 */
	protected $_awsAuth = null;
	protected $_bucketName = "";

	public function  __construct($configName, $bucketName)
	{
		$this->_awsAuth = new AWSAuth($configName);
		$this->_bucketName = $bucketName;
	}

	public function defaultFileName($file)
	{
		$uploadName = baseName($file);
	}

	/**
	 *
	 * @return \S3
	 * @throws Exception
	 */
	protected function getInstance()
	{
		// Check for CURL
		if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll'))
			throw new Exception ("ERROR: CURL extension not loaded");

		// Check for Required Parameters
		if (($this->_awsAuth->getAccessKey() == "") || ($this->_awsAuth->getSecretKey() == ""))
			throw new Exception ("ERROR: Required parameters are missing.");

		// Instantiate the class
		return new S3($this->_awsAuth->getAccessKey(), $this->_awsAuth->getSecretKey());
	}

	public function uploadFile($uploadFile, $newFileName = "")
	{

		// Check if our upload file exists
		if (!file_exists($uploadFile) || !is_file($uploadFile))
			throw new Exception ("ERROR: No such file: $uploadFile");

		$s3 = $this->getInstance();

		if ($newFileName == "")
			$newFileName = $this->defaultFileName ($uploadFile);


		// Put our file (also with public read access)
		if ($s3->uploadFile($this->_bucketName, $newFileName, $uploadFile, true))
		{
			return $newFileName;
		} 
		else
		{
			throw new Exception("S3::putObjectFile(): Failed to copy file");
		}

	}

	/**
	 * Se existir, retorna um Array com as propriedades: [time] [hash] [type] [size]
	 * @param string $amazonFileName
	 * @return array
	 */
	public function getObjectInfo($fileName)
	{
		$s3 = $this->getInstance();

		return $s3->getObjectInfo($this->_bucketName, $fileName);
	}


	public function getFileListing($prefix, $maxfiles = 50)
	{
		$s3 = $this->getInstance();

		return $s3->getBucketContents($this->_bucketName, $this->defaultFileName($prefix), null, null, $maxfiles );
	}

}

?>
