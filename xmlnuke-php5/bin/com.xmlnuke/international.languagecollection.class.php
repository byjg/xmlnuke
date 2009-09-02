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
*LanguageCollection class create a NameValueCollection but only add elements from the current language context
*/
class LanguageCollection
{

	/**
	*@var Context
	*/
	private $_context;
	/**
	*@var array
	*/
	private $_collection = array();
	/**
	*@var bool
	*/
	private $_loadedFromFile = false;
	
	private $_debugInfo = "";

	/**
	*@param Context $context
	*@return void
	*@desc LanguageCollection Constructor
	*/
	public function LanguageCollection($context)
	{
		$this->_context = $context;
	}
	
	/**
	*@param string $lang
	*@param string $key
	*@param string $value
	*@return void
	*@desc Add text to specific language. At runtime will be add only the current language.
	*/
	public function addText($lang, $key, $value)
	{
		if (strtolower($lang) == strtolower($this->_context->Language()->getName()));
		{
			$this->_collection[$key] = $value;
		}
	}

	/**
	*@param string $key
	*@param string $param
	*@return string
	*@desc Get the text from key
	*/
	public function Value($key, $param = "")
	{
		$retword = @$this->_collection[$key];
		if ($retword == null)
		{
			$retword = "[".$key."?]";
		}
		else
		{
			$retword = str_replace("\\n",""."\n",$retword);
		}
		
		if (is_array($param)) 
		{
			return  $this->replaceValues($retword, $param);
		}
		elseif (strlen($param)>0) 
		{
			return  $this->replaceValues($retword, array($param));
		}
		return $retword;
	}

	/**
	*@param string $key
	*@param array $args Array of String
	*@return string
	*@desc Get text from key and replace %s parameters
	*/
	public function ValueArgs($key, $args)
	{
		/*
		* This code uses {0}, {1}, {2}, parameters
		*
		*/
		$word = $this->Value($key);

		return  $this->replaceValues($word,$args);
	}
	
	/**
	*@param string $str
	*@param array $array
	*@return string
	*@desc Replace Values
	*/
	private function replaceValues($str, $array)
	{
		$strLen = strlen ( $str );
		$pos = strpos($str,"{");
		while($pos !== false)
		{
			$key = $str{$pos + 1};
			$value = $array[$key];
			$str = str_replace("{".$key."}",$value,$str);
			$pos = strpos($str,"{",$pos);
		}
		//echo("$str<br>");
		return $str;
	}
	
	/**
	*@param AnydatasetBaseFilenameProcessor $langFile
	*@return void
	*@desc Load Languages
	*/
	public function LoadLanguages($langFile)
	{
		$all = ($langFile->ToString() == "_all");

		if (!$all)
		{
			$this->LoadLanguages(new AnydatasetLangFilenameProcessor("_all", $this->_context));
		}

		$this->_loadedFromFile = false;
		
		$i = 0;
		
		while ($i++ < 2)
		{
			if ( ($langFile->getFilenameLocation() == ForceFilenameLocation::UseWhereExists) || ($langFile->getFilenameLocation() == ForceFilenameLocation::SharedPath) || ($langFile->getFilenameLocation() == ForceFilenameLocation::PrivatePath) )
			{
				if ($i == 1)
				{
					$langFile->setFilenameLocation(ForceFilenameLocation::SharedPath);
				}
				else
				{
					$langFile->setFilenameLocation(ForceFilenameLocation::PrivatePath);
				}
			}
			else
			{
				$i = 2; // Force exit from loop at the end. 
			}

			$this->_debugInfo .= $langFile->ToString() . " in " . $langFile->getFilenameLocation() . "(\"" . $langFile->FullQualifiedNameAndPath() . "\") ";
			if (!$langFile->Exists())
			{
				$this->_debugInfo .= "Does not exists; ";
				continue;
			}
			
			$this->_debugInfo .= "Exists; ";
			
			$curLang = strtolower($this->_context->Language()->getName());
			try 
			{
				$lang = new AnyDataSet( $langFile );
			}
			catch (Exception $e)
			{
				throw new EngineException(800, 'Can\'t load language file "' . $langFile->FullQualifiedName() . '"!! Maybe Invalid Format.'); // 2005-10-19 12:15
			}
			
			$itf = new IteratorFilter();
			$itf->addRelation("LANGUAGE", Relation::Equal, $curLang );
	
			//AnyIterator
			$it = $lang->getIterator($itf);
			if ($it->hasNext())
			{
				//SingleRow
				$sr = $it->moveNext();
				$names = $sr->getFieldNames();			
				foreach($names as $name )
				{
					$this->addText( $curLang, $name, $sr->getField($name) );
				}
				$this->_loadedFromFile = true;
			}
			
		}

	}
	
	/**
	*@param AnydatasetBaseFilenameProcessor $langFile
	*@return 
	*@desc 
	*/
	public function SaveLanguages($langFile)
	{
	}

	/**
	*@param 
	*@return 
	*@desc 
	*/
	public function loadedFromFile()
	{
		return $this->_loadedFromFile;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function Debug()
	{
		Debug::PrintValue($this->_debugInfo);
		Debug::PrintValue($this->_collection);
	}

}

?>
