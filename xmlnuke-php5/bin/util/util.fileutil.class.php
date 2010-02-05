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

/**
* Generic functions to manipulate Files and system access.
*/
class FileUtil
{
	/**
	*@param string/FilenameProcessor $filename - Path from file
	*@return bool
	*@desc Return true if filename exist
	*/
	public static function Exists($filename)
	{
		if ($filename instanceof FilenameProcessor)
		{
			return file_exists($filename->FullQualifiedNameAndPath());
		}
		else
		{
			return file_exists($filename);
		}
	}

	/**
	*@param string $folder - Full path from directory
	*@return array
	*/
	public static function RetrieveSubFolders($folder)
	{
		$array = null;
		if ($handle = FileUtilKernel::OpenDirectory($folder))
		{
			while ($file = FileUtilKernel::ReadDirectory($handle))
			{
				if((is_dir($folder.self::Slash().$file))&&(($file != "." && $file != ".." )))
				{
					$array[]=$folder.self::Slash().$file;
				}
			}
			FileUtilKernel::CloseDirectory($handle);
		}
		return $array;
	}

	/**
	*@param string $folder - Full path from directory
	*@return array
	*/
	public static function RetrieveFilesFromFolder($folder, $pattern)
	{
		$array = array();
		if (!is_null($pattern))
		{
			$pattern = str_replace(".", "\.", $pattern);
			$pattern = str_replace("?", ".?", $pattern);
			$pattern = str_replace("*", ".*", $pattern);
		}

		if ($handle = FileUtilKernel::OpenDirectory($folder))
		{
			while ($file = FileUtilKernel::ReadDirectory($handle))
			{
				if(!is_dir($folder.self::Slash().$file))
				{
					if(!is_null($pattern) && $pattern!="")
					{
						if (@eregi("$pattern", $file))
						{
							$array[]=$folder.self::Slash().$file;
						}
					}
					else
					{
						$array[]=$folder.self::Slash().$file;
					}
				}
			}
			FileUtilKernel::CloseDirectory($handle);
			return $array;
		}
	}

	/**
	*@param string $FullFileName
	*@return array
	*/
	public static function ExtractFilePath($FullFileName)
	{
		$path = pathinfo($FullFileName);

		return $path["dirname"].self::Slash();

	}

	/**
	*@param string $FullFileName
	*@return array
	*/
	public static function ExtractFileName($FullFileName)
	{
		return basename($FullFileName);
	}

	/**
	*@param string $filename
	*@return array
	*/
	public static function QuickFileRead($filename)
	{
		return FileUtilKernel::getFileContents($filename);
	}

	/**
	*@param string $filename
	*@param string $content
	*@param bool $append Adiciona ao final ou nao.
	*@return void
	*/
	public static function QuickFileWrite($filename, $content, $append=null)
	{
		$operacao = "w";
		if ($append != null)
		{
			$operacao = "a";
		}
		$handle = FileUtilKernel::OpenFile($filename, $operacao);
		FileUtilKernel::WriteFile($handle, $content);
		FileUtilKernel::CloseFile($handle);
	}
	/**
	*@param string $file - Full path from file
	*@return void
	*/
	public static function DeleteFileString($file)
	{
		FileUtilKernel::DeleteFile($file);
	}

	/**
	*@param string $file - Full path from file
	*@return void
	*/
	public static function DeleteDirectory($dir)
	{
		FileUtilKernel::DeleteDirectory($dir);
	}

	/**
	 * Rename a Directory
	 *
	 * @param String $old_name
	 * @param String $new_name
	 */
	public static function RenameDirectory($old_name, $new_name)
	{
		FileUtilKernel::RenameDirectory($old_name, $new_name);
	}

	/**
	*@param FilenameProcessor $file
	*@return void
	*/
	public static function DeleteFile($file)
	{
		FileUtilKernel::DeleteFile($file->FullQualifiedNameAndPath());
	}

	/**
	 * Rename a File
	 *
	 * @param String $old_name
	 * @param String $new_name
	 */
	public static function RenameFile($old_name, $new_name)
	{
		FileUtilKernel::RenameFile($old_name, $new_name);
	}

	/**
	 * Delete File from path
	 *
	 * @param FileNameProcessor $file
	 */
	public static function DeleteFilesFromPath($file)
	{
		$files = self::RetrieveFilesFromFolder($file->PathSuggested(),null);
		foreach ($files as $f)
		{
			if (strpos($f,$file->Extension())!== false)
			{
				self::DeleteFileString($f);
			}
		}
	}

	/**
	* @return string
	* @desc Return slash from a Operational System
	* If OperationSystem is WINDOWS the slash will be '\\'
	* If OperationSystem is UNIX the slash will be '/'
	*/
	public static function Slash()
	{
		return self::isWindowsOS() ? "\\" : "/";
	}

	/**
	 * Return slash to a operational system
	 *
	 * @param string $path
	 * @return string
	 */
	public static function AdjustSlashes($path)
	{

		if (self::isWindowsOS())
		{
			$search = "/";
			$replace = "\\";
		}
		else
		{
			$search = "\\";
			$replace = "/";
		}
		return str_replace($search, $replace, $path);
	}

	/**
	 * Get filr from absolute path
	 *
	 * @param string $absolutepath Absolute path from file
	 * @return string
	 */
	public static function getUriFromFile($absolutepath)
	{
		if (self::isWindowsOS())
		{
			$result = "file:///".$absolutepath;

			$search = "\\";
			$replace = "/";

			$result =str_replace($search, $replace, $result);
		}
		else
		{
			$result = "file://".$absolutepath;
		}
		return $result;

	}
	//OK!
	/**
	 * OS is Microsoft Windows?
	 *
	 * @return bool
	 */
	public static function isWindowsOS()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	*@desc Check and encode UTF-8 encoding document. Return the checked document converted to UTF-8
	*@param string $document
	*@return string
	*/
	public static function CheckUTF8Encode($document)
	{
		if (!self::is_utf8($document))
		{
			$document = utf8_encode($document);
		}
		else
		{
			if (ord(substr($document,0,1)) == 239)
			{
				$document = substr($document, 3);
			}
//			$document = utf8_encode(utf8_decode($document));
		}
		return $document;
	}

	/**
	 * Not implemented
	 * @ignore This function. To do nothing.
	 *
	 * @param DOMDocument $document
	 */
	public static function CheckUTF8Decode($document)
	{
		//nothing
	}

	/**
	*@desc Test if string is UTF-8 encoding
	*@param string $str
	*@return bool
	*/
	public static function is_utf8($str)
	{
	   // values of -1 represent disalloweded values for the first bytes in current UTF-8
	   static $trailing_bytes = array (
	       0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
	       0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
	       0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
	       0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
	       -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
	       -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
	       -1,-1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
	       2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, 3,3,3,3,3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1
	   );

	   $ups = unpack('C*', $str);
	   if (!($aCnt = count($ups))) return true; // Empty string *is* valid UTF-8
	   for ($i = 1; $i <= $aCnt;)
	   {
	       if (!($tbytes = $trailing_bytes[($b1 = $ups[$i++])])) continue;
	       if ($tbytes == -1) return false;

	       $first = true;
	       while ($tbytes > 0 && $i <= $aCnt)
	       {
	           $cbyte = $ups[$i++];
	           if (($cbyte & 0xC0) != 0x80) return false;

	           if ($first)
	           {
	               switch ($b1)
	               {
	                   case 0xE0:
	                       if ($cbyte < 0xA0) return false;
	                       break;
	                   case 0xED:
	                       if ($cbyte > 0x9F) return false;
	                       break;
	                   case 0xF0:
	                       if ($cbyte < 0x90) return false;
	                       break;
	                   case 0xF4:
	                       if ($cbyte > 0x8F) return false;
	                       break;
	                   default:
	                       break;
	               }
	               $first = false;
	           }
	           $tbytes--;
	       }
	       if ($tbytes) return false; // incomplete sequence at EOS
	   }
	   return true;
	}


	/**
	* Create a directory structure recursively
	*
	* @author Aidan Lister <aidan@php.net> (original name mkdirr)
	* @version 1.0.0
	* @param string $pathname - The directory structure to create
	* @param int $mode - Security mode apply in directorys
	* @return bool - Returns TRUE on success, FALSE on failure
	*/
	public static function ForceDirectories($pathname, $mode = 0777)
	{
		// Crawl up the directory tree
		$next_pathname = substr($pathname,0, strrpos($pathname, self::Slash()));
		if ($next_pathname != "")
		{
			self::ForceDirectories($next_pathname, $mode);
		}
		if (!file_exists($pathname))
		{
			FileUtilKernel::CreateDirectory($pathname, $mode);
		}
	}

	/**
	* Remove directorys structure recursively
	*
	* @author dexn (at) metazure.com (original name rmdirr)
	* @version 1.0.0
	* @param string $dir - The directory structure to delete
	* @return void
	*/
	public static function ForceRemoveDirectories($dir)
	{
		if($objs = glob($dir."/*")){
			foreach($objs as $obj) {
				is_dir($obj)? self::ForceRemoveDirectories($obj) : self::DeleteFileString($obj);
			}
		}
		self::DeleteDirectory($dir);
	}

	/**
	* Open a remote document from a specific URL
	* @param string $url
	* @return handle Handle of the document opened
	*/
	public static function OpenRemoteDocument($url)
	{
		// Expression Regular:
		// [1]: http or ftp
		// [2]: Server name
		// [4]: Full Path
		$pat = '/(http|ftp|https):\\/\\/((\\w|\\.)+)/i';
		$urlParts = preg_split($pat, $url, -1,PREG_SPLIT_DELIM_CAPTURE);

		$handle = fsockopen($urlParts[2], 80, $errno, $errstr, 30);
		if (!$handle)
		{
			throw new Exception("Socket error: $errstr ($errno)");
		}
		else
		{
			$out = "GET " . $urlParts[4] . " HTTP/1.1\r\n";
			$out .= "Host: " . $urlParts[2] . "\r\n";
			$out .= "Connection: Close\r\n\r\n";

			fwrite($handle, $out);

			return $handle;
		}
	}

	/**
	* Get the full document opened from OpenRemoteDocument
	* @param handle $handle
	* @return string Remote document
	*/
	public static function ReadRemoteDocument($handle)
	{
		// THIS FUNCTION IS A CRAP!!!!
		// I NEED REFACTORY THIS!
		$retdocument = "";
		$xml = true;
		$canread = true;

		// READ HEADER
		while (!feof($handle))
		{
			$buffer = fgets($handle, 4096);
			if (strpos(strtolower($buffer), "content-type:")!==false)
			{
				$xml = (strpos(strtolower($buffer), "xml")!==false);
				$canread = !$xml;
			}
			if (trim($buffer) == "")
			{
				break;
			}
		}

		// READ CONTENT
		while (!feof($handle))
		{
			$buffer = fgets($handle, 4096);
			if (!$canread && ($buffer[0] != "<"))
			{
				$buffer = "";
			}
			else
			{
				$canread = true;
			}
			$retdocument = $retdocument . $buffer;
		}
		fclose($handle);

		if ($xml)
		{
			$lastvalid = strrpos($retdocument, ">");
			$retdocument = substr($retdocument, 0, $lastvalid+1);
		}

		return $retdocument;
	}

	/**
	* Get a remote document and transform to DomXMLDocument
	* @param string $url
	* @return handle DOMDocument
	*/
	public static function GetRemoteXMLDocument($url)
	{
		// THIS FUNCTION IS A CRAP!!!!
		// I NEED REFACTORY THIS!
		$handle = FileUtil::OpenRemoteDocument($url);
		$xmlString = FileUtil::ReadRemoteDocument($handle);

		$search = array ("'&(quot|#34);'i",                 // Replace HTML entities
		                 "'&(amp|#38);'i",
		                 //"'&(lt|#60);'i",
		                 //"'&(gt|#62);'i",
		                 "'&(nbsp|#160);'i",
		                 "'&(iexcl|#161);'i",
		                 "'&(cent|#162);'i",
		                 "'&(pound|#163);'i",
		                 "'&(copy|#169);'i",
		                 "'&aacute;'i",
		                 "'&eacute;'i",
		                 "'&iacute;'i",
		                 "'&oacute;'i",
		                 "'&uacute;'i",
		                 "'&atilde;'i",
		                 "'&ccedil;'i",
		                 "'&#(\d+);'e");                    // evaluate as php

		$replace = array ("\"",
		                  "&",
		                  //"<",
		                  //">",
		                  " ",
		                  chr(161),
		                  chr(162),
		                  chr(163),
		                  chr(169),
		                  "á",
		                  "é",
		                  "í",
		                  "ó",
		                  "ú",
		                  "ã",
		                  "ç",
		                  "chr(\\1)");


		$xmlString = preg_replace($search, $replace, $xmlString);
		//$xmlString = tidy_parse_string($xmlString, array('output-xml' => TRUE), 'UTF8');

		$xmldoc = XmlUtil::CreateXmlDocumentFromStr($xmlString);

		return $xmldoc;
	}

	public static function ResponseCustomContent($mimeType, $content, $downloadName = "")
	{
		ob_clean();
		header("Content-Disposition: inline" . ($downloadName != "" ?  "; filename=\"" . basename($downloadName) . "\"" : "") );
		header("Content-Type: " . $mimeType . ($downloadName != "" ?  "; name=\"" . basename($downloadName) . "\"" : "") );
		echo $content;
		exit();
	}

	public static function ResponseCustomContentFromFile($mimeType, $filename, $downloadName = "")
	{
		ob_clean();
		if (!FileUtil::Exists($filename))
		{
			header("HTTP/1.1 404 Not Found");
			header("status: 404");
			echo "<h1>404 Not Found</h1>";
			echo "Filename " . basename($filename) . " not found!<br>";
		}
		else
		{
			if ($downloadName == "")
			{
				$downloadName = $filename;
			}
			$downloadName = basename($downloadName);
			header("Content-Disposition: inline; filename=\"" . $downloadName . "\"");
			header("Content-Type: " . $mimeType . "; name=\"" . $downloadName . "\"");
			$content = file_get_contents($filename);
			echo $content;
		}
		exit();
	}

	public static function GetTempDir()
	{
		if ( !function_exists('sys_get_temp_dir'))
		{
			function sys_get_temp_dir()
			{
				if( $temp=getenv('TMP') )        return $temp;
				if( $temp=getenv('TEMP') )        return $temp;
				if( $temp=getenv('TMPDIR') )    return $temp;
				$temp=tempnam(__FILE__,'');
				if (file_exists($temp))
				{
					unlink($temp);
					return dirname($temp);
				}
				return null;
			}
		}
		else
		{
			return realpath(sys_get_temp_dir());
		}
	}

}
?>
