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
*This class is parent of the core engine functions
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class XMLNukeKernel
{
	
}
/**
*This exception occurs when the requested module not found
* Erros range 10 and 29
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class FileUtilKernel extends XMLNukeKernel 
{
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
			throw new FileUtilException(100, "Not able to open the file $filename in mode $mode");
		}
	}
	public static function CloseFile( $resource )
	{       
		if(!@fclose($resource))
		{
			throw new FileUtilException(101, "Failed when trying to close file.");
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
			throw new FileUtilException(109, "Failed when trying to write in file.");
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
			throw new FileUtilException(102, "Not able to read file.");
		}
	}
	/**
	 * Return string file contents
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function getFileContents( $filename )
	{
		if($str = @file_get_contents($filename))
		{
			return $str;
		}
		else
		{
			throw new FileUtilException(102, "Not able to read file '$filename'.");
		}
	}
	/**
	 * Open a directory
	 *
	 * @param string $folder
	 * @return string
	 */
	public static function OpenDirectory($folder)
	{
		if($handle = @opendir($folder))
		{
			return $handle;
		}
		else
		{
			throw new FileUtilException(103, "Not able to open the directory $folder.");
		}
	}
	/**
	 * Read a directory
	 *
	 * @param resource $resource
	 * @return string
	 */
	public static function ReadDirectory($resource)
	{
		try 
		{
			$handle = @readdir($resource);
			return $handle;
		}
		catch (Exception $ex)
		{
			throw new FileUtilException(104, "Not able to read the directory.");
		}
	}
	/**
	 * Close dir
	 *
	 * @param resource $resource
	 * @return void
	 */
	public static function CloseDirectory($resource)
	{
		if(is_null($resource))
		{
			throw new FileUtilException(105, "The directory was not found to try to close.");
		}
		else 
		{
			closedir($resource);
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
			throw new FileUtilException(106, "Not able to rename the file $old_name.");
		}		
	}	
	
	/**
	 * Delete a file
	 *
	 * @param string $filename
	 */
	public static function DeleteFile($filename)
	{
		if(!@unlink($filename))
		{
			throw new FileUtilException(106, "Not able to delete the file $filename.");
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
			throw new FileUtilException(106, "Not able to rename the directory $old_name.");
		}		
	}
	
	/**
	 * Delete a directory
	 *
	 * @param string $dir
	 */
	public static function DeleteDirectory($dir)
	{
		if(!@rmdir($dir))
		{
			throw new FileUtilException(107, "Not able to delete the directory $dir.");
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
			throw new FileUtilException(108, "Not able to create the directory $dir in mode $mode.");
		}
	}
}
/**
*This exception occurs when the requested module not found
* Errors range 30 and 50
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class XmlUtilKernel extends XMLNukeKernel 
{
	/**
	*@desc Try to load xml document from a string
	*@param DOMDocument &$document
	*@param string $xml
	*@return void
	*/
	public static function LoadXMLDocument( &$document, $xml )
	{
		if(@!$document->loadXML($xml))
		{
			throw new XmlUtilException(254, "Not able to load the XML document.");
		}
	}
	/**
	*@desc Try to save xml document to a file
	*@param DOMDocument &$document
	*@return void
	*/
	public static function SaveXMLDocument( &$document , $filename)
	{   
		if (!($document instanceof DOMDocument))
		{
			throw new XmlUtilException(255, "Object isn't a DOMDocument."); // Document não é um documento XML
		}
		else 
		{
			$ret = @$document->save($filename);
			if ($ret === false)
			{
				throw new XmlUtilException(256, "Not able to save XML Document in $filename."); // Não foi possível gravar o arquivo: PERMISSÂO ou CAMINHO não existe;
			}
		}
	}
	/**
	*@desc Try get Owner document from a DOMElement node
	*@param DOMNode $node
	*@return DOMDocument
	*/
	public static function getOwnerDocument( $node )
	{       
		if (!($node instanceof DOMNode))
		{
			throw new XmlUtilException(257, "Object isn't a DOMNode. Found object class type: " . get_class($node));
		}
		return $node->ownerDocument;
	}
	
	/**
	*@desc Create a document child node from a DOMElement node
	*@param DOMElement $node
	*@param string $name - New name of the node
	*@return DOMElement 
	*/
	public static function createChildNode( $node, $name )
	{       
		$owner = self::getOwnerDocument($node);
		$newnode = $owner->createElement($name);
		if($newnode === false)
		{
			throw new XmlUtilException(258, "Failed when trying to create DOMElement.");
		}
		return $newnode;
	}
}
?>
