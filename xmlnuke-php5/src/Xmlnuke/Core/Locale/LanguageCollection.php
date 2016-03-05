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

namespace Xmlnuke\Core\Locale;

use Exception;
use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\IteratorFilter;
use Xmlnuke\Core\Engine\Context;
use ByJG\AnyDataset\Enum\Relation;
use Xmlnuke\Core\Exception\EngineException;
use Xmlnuke\Core\Processor\AnydatasetBaseFilenameProcessor;
use Xmlnuke\Core\Processor\AnydatasetLangFilenameProcessor;
use Xmlnuke\Core\Processor\ForceFilenameLocation;
use Xmlnuke\Util\Debug;
use Xmlnuke\Util\FileUtil;

/**
 * LanguageCollection class create a NameValueCollection but only add elements from the current language context
 * @xmlnuke:nodename l10n
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
	public function __construct()
	{
		$this->_context = Context::getInstance();
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
		if (!isset($this->_collection[$key]))
		{
			$retword = "[".$key."?]";
		}
		else
		{
			$retword = str_replace("\\n",""."\n", $this->_collection[$key]);
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
		foreach($array as $i=>$value)
		{
			$str = str_replace("{" . $i . "}", $value, $str);
		}
		//$str = preg_replace('/\{.*\}/', '', $str);
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
			$this->LoadLanguages(new AnydatasetLangFilenameProcessor("_all"));
		}

		$this->_loadedFromFile = false;

		$paths = array();
		if ($langFile->getFilenameLocation() == ForceFilenameLocation::UseWhereExists || ($langFile->getFilenameLocation() == ForceFilenameLocation::SharedPath))
			$paths[] = $langFile->SharedPath();
		if ($langFile->getFilenameLocation() == ForceFilenameLocation::UseWhereExists || ($langFile->getFilenameLocation() == ForceFilenameLocation::PrivatePath))
			$paths = array_merge($paths, $langFile->PrivatePath());

		foreach ($paths as $path)
		{

			$filename = $path . $langFile->FullQualifiedName();
			
			$this->_debugInfo .= $langFile->ToString() . " in " . $filename . ' ';
			if (!FileUtil::Exists($filename))
			{
				$this->_debugInfo .= "[Does not exists]; \n";
				continue;
			}
			
			$this->_debugInfo .= "[Exists]; \n";
			
			$curLang = strtolower($this->_context->Language()->getName());
			try 
			{
				$lang = new AnyDataset( $filename );
			}
			catch (Exception $e)
			{
				throw new EngineException('Can\'t load language file "' . $langFile->FullQualifiedName() . '"! ' . $e->getMessage());
			}
			
			$itf = new IteratorFilter();
			$itf->addRelation("LANGUAGE",  Relation::EQUAL, $curLang );
	
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

	/**
	 * @xmlnuke:dontcreatenode
	 * @return array
	 */
	public function getCollection()
	{
		return $this->_collection;
	}

}

?>
