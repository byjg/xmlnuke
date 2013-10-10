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
namespace Xmlnuke\Util;

use DOMDocument;
use Exception;
use Xmlnuke\Core\Exception\FileUtilException;
use Xmlnuke\Core\Processor\FilenameProcessor;

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
		if ($handle = FileUtil::OpenDirectory($folder))
		{
			while ($file = FileUtil::ReadDirectory($handle))
			{
				if((is_dir($folder.self::Slash().$file))&&(($file != "." && $file != ".." )))
				{
					$array[]=$folder.self::Slash().$file;
				}
			}
			FileUtil::CloseDirectory($handle);
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

		if ($handle = FileUtil::OpenDirectory($folder))
		{
			while ($file = FileUtil::ReadDirectory($handle))
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
			FileUtil::CloseDirectory($handle);
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
	public static function QuickFileRead($file)
	{
		if ($file instanceof FilenameProcessor)
			$filename = $file->FullQualifiedNameAndPath();
		else
			$filename = $file;
		
		return FileUtil::getFileContents($filename);
	}

	/**
	*@param string $file
	*@param string $content
	*@param bool $append Adiciona ao final ou nao.
	*@return void
	*/
	public static function QuickFileWrite($file, $content, $append=false)
	{
		if ($file instanceof FilenameProcessor)
			$filename = $file->FullQualifiedNameAndPath();
		else
			$filename = $file;
		
		$mode = "w+";
		if ($append)
		{
			$mode = "a+";
		}
		$handle = FileUtil::OpenFile($filename, $mode);
		FileUtil::WriteFile($handle, $content);
		FileUtil::CloseFile($handle);
	}

	/**
	 *
	 * @param string $file
	 * @deprecated since version 3.5.1
	 */
	public static function DeleteFileString($file)
	{
		FileUtil::DeleteFile($file);
	}

	/**
	 *
	 * @param string $dir
	 * @throws FileUtilException
	 */
	public static function DeleteDirectory($dir)
	{
		if(!@rmdir($dir))
		{
			throw new FileUtilException("Cannot delete the directory $dir.", 107);
		}
	}

	/**
	 * Rename a Directory
	 *
	 * @param String $old_name
	 * @param String $new_name
	 */
	public static function RenameDirectory($old_name, $new_name)
	{
		if(!rename($old_name, $new_name))
		{
			throw new FileUtilException("Cannot rename the directory $old_name.", 106);
		}
	}

	/**
	 *
	 * @param FilenameProcessor $file
	 * @throws FileUtilException
	 */
	public static function DeleteFile($file)
	{
		if ($file instanceof FilenameProcessor)
			$filename = $file->FullQualifiedNameAndPath();
		else
			$filename = $file;

		if(!@unlink($filename))
		{
			throw new FileUtilException("Cannot delete the file $filename.", 106);
		}
	}

	/**
	 * Rename a File
	 *
	 * @param String $old_name
	 * @param String $new_name
	 */
	public static function RenameFile($old_name, $new_name)
	{
		if(!rename($old_name, $new_name))
		{
			throw new FileUtilException("Cannot rename the file $old_name.", 106);
		}
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
			FileUtil::CreateDirectory($pathname, $mode);
		}
	}

	/**
	 * Create a directory
	 *
	 * @param string $dir
	 * @param int $mode
	 */
	public static function CreateDirectory($dir, $mode = null)
	{
		if(!@mkdir($dir, $mode))
		{
			throw new FileUtilException("Cannot create the directory $dir in mode $mode.", 108);
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
			throw new FileUtilException("Socket error: $errstr ($errno)");
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
	* @return handle \DOMDocument
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




	/**
	 * Open a file from string or URL
	 *
	 * @param string $filename
	 * @param int $mode
	 * @return unknown
	 */
	public static function OpenFile( $filename, $mode )
	{
		if($handle = @fopen( $filename, $mode ))
		{
			return $handle;

		}
		else
		{
			throw new FileUtilException("Cannot open the file $filename in mode $mode", 100);
		}
	}
	public static function CloseFile( $resource )
	{
		if(!@fclose($resource))
		{
			throw new FileUtilException("Failed to close the file.", 101);
		}
	}
	/**
	 * Write in files
	 *
	 * @param resource $handle
	 * @param string $content
	 * @return void
	 */
	public static function WriteFile( $handle, $content )
	{
		if(!@fwrite( $handle, $content ))
		{
			throw new FileUtilException("Failed to write to the file.", 109);
		}
	}
	/**
	 * Read a file
	 *
	 * @param resource $handle
	 * @param int $filesize
	 * @return string
	 */
	public static function ReadFile( $handle, $filesize )
	{
		if($str = @fread( $handle, $filesize ))
		{
			return $str;
		}
		else
		{
			throw new FileUtilException("Cannot read file.");
		}
	}
	/**
	 * Return string file contents
	 *
	 * @param string $filename
	 * @return string
	 */
	protected static function getFileContents( $filename )
	{
		if($str = @file_get_contents($filename))
		{
			return $str;
		}
		else
		{
			throw new FileUtilException("Cannot read the file '$filename'.", 102);
		}
	}
	/**
	 * Open a directory
	 *
	 * @param string $folder
	 * @return string
	 */
	protected static function OpenDirectory($folder)
	{
		if($handle = @opendir($folder))
		{
			return $handle;
		}
		else
		{
			throw new FileUtilException("Cannot open the directory $folder.", 103);
		}
	}
	/**
	 * Read a directory
	 *
	 * @param resource $resource
	 * @return string
	 */
	protected static function ReadDirectory($resource)
	{
		try
		{
			$handle = @readdir($resource);
			return $handle;
		}
		catch (Exception $ex)
		{
			throw new FileUtilException("Cannot read the directory.", 104);
		}
	}
	/**
	 * Close dir
	 *
	 * @param resource $resource
	 * @return void
	 */
	protected static function CloseDirectory($resource)
	{
		if(is_null($resource))
		{
			throw new FileUtilException("Directory resource is null. Cannot close it.", 105);
		}
		else
		{
			closedir($resource);
		}
	}

}
?>
