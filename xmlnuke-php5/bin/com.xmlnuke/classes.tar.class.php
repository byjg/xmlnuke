<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Acknowledgments to: Thiago Bellandi
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

require_once('Archive/Tar.php');

class Tar extends Archive_Tar
{
	
	public $warning;
	
	public $error;
	
	/**
	 * @var Array
	 */
	protected $_directoriesList;

	/**
	 * @var Array
	 */
	protected $_filesList;
	
	/**
	 * Contructor Method
	 *
	 * @param String $name
	 * @param String $directory
	 * @return Tar
	 */
	public function Tar ($p_tarname, $p_compress = null)
	{
		parent::Archive_Tar($p_tarname, $p_compress);
		$this->warning = array();
		$this->error = array();
		$this->setDirectoriesAndFiles();
	}
	
	private function setDirectoriesAndFiles()
	{
		$files = $this->listContent();
		if ($files != 0)
		{
			for ($i=0; $i < sizeof($files); $i++)
			{		
				$file = $files[$i]["filename"];
				
				//verify if what him is installed is a file or directory
				if($files[$i]["typeflag"] == 5)
				{
					$this->setDirectories($file);
				}
				else
				{
					$this->setFiles($file);
				}
			}
		}
	}
	
	/**
	 * List Files
	 *
	 */
	public function listFiles ($paragraph)
	{
		if (($files = $this->listContent()) != 0)
		{
			$paragraph->addXmlnukeObject(new XmlnukeText("Tar File: ",true));
			$paragraph->addXmlnukeObject(new XmlnukeText(" ".$this->_tarname));
			$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
			$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
			
			$paragraph->addXmlnukeObject(new XmlnukeText("*** List of Files in Tar Backup ***",true,false,false,true));
			
			//
			
			for ($i=0; $i < sizeof($files); $i++)
			{
				$paragraph->addXmlnukeObject(new XmlnukeText("Filename: ",true));	
				$paragraph->addXmlnukeObject(new XmlnukeText($files[$i]["filename"]));
				$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
				
//				$text = " - size: ".$files[$i][size];
//				$paragraph->addXmlnukeObject(new XmlnukeText($text,true));
//				$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
//
//				$text = " - mtime :'".$files[$i][mtime]."' (".date("l dS of F Y h:i:s A", $files[$i][mtime]).")";
//				$paragraph->addXmlnukeObject(new XmlnukeText($text,true));
//				$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
//				
//				$text = " - mode :'".$files[$i][mode];
//				$paragraph->addXmlnukeObject(new XmlnukeText($text,true));
//				$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
//				
//				$text = " - uid :'".$files[$i][uid];
//				$paragraph->addXmlnukeObject(new XmlnukeText($text,true));
//				$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
//				
//				$text = " - gid :'".$files[$i][gid];
//				$paragraph->addXmlnukeObject(new XmlnukeText($text,true));
//				$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
//								
//				$text = " - typeflag: ".$files[$i][typeflag];
//				$paragraph->addXmlnukeObject(new XmlnukeText($text,true));
//				$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
//				$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
			}
		
			$text = "*** End of Files ***";
			$paragraph->addXmlnukeObject(new XmlnukeText($text,true));
			$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		}
	}
	
	/**
	 * Add a warning in warning array list
	 *
	 * @param String $p_message
	 */
    public function _warning($p_message)
    {
        $this->warning[] = $p_message;
    }
    
    /**
     * Add a error in error array list
     *
     * @param String $p_message
     */
    function _error($p_message)
    {
        $this->error[] = $p_message;
    }
    
    /**
     * Show the erros
     *
     * @param XmlBLockCollection $block
     */
    public function showErrors($block)
    {
    	if ($this->warning)
    	{
    		$block->addXmlnukeObject(new XmlnukeText("Warning:",true,false,false,true));
    		
    		foreach ($this->warning as $field => $value)
    		{
    			$block->addXmlnukeObject(new XmlnukeText($value,false,false,false,true));
    		}
    	}
    	
    	$block->addXmlnukeObject(new XmlnukeBreakLine());
    	
    	if ($this->error)
    	{
    		$block->addXmlnukeObject(new XmlnukeText("Errors:",true,false,false,true));
    		
    		foreach ($this->error as $field => $value)
    		{
    			$block->addXmlnukeObject(new XmlnukeText($value,false,false,false,true));
    		}
    	}
    	
    	$block->addXmlnukeObject(new XmlnukeBreakLine());
    } 
    
    /**
     * Set the Files Installed
     *
     * @param String $file
     */
    public function setFiles($file)
    {
    	$this->_filesList[] = $file;
    }
    
    /**
     * Get the Files Installed
     *
     * @return String
     */
    public function getFiles()
    {
    	return $this->_filesList;    	
    }
   
    /**
     * Set the Directories Installed
     *
     * @param String $directory
     */
    public function setDirectories($directory)
    {
    	$this->_directoriesList[] = $directory;
    }
    
    /**
     * Get the Files Installed
     *
     * @return Array
     */
    public function getDirectories()
    {
    	return $this->_directoriesList;    	
    }
}
?>