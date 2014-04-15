<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes
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

namespace Xmlnuke\Core\AnyDataset;

use Exception;
use InvalidArgumentException;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Exception\DatasetException;
use Xmlnuke\Core\Exception\NotFoundException;
use Xmlnuke\Core\Processor\FilenameProcessor;
use Xmlnuke\Util\FileUtil;

/**
 * @package xmlnuke
 */
class TextFileDataSet
{	
	const CSVFILE = '/[|,;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
	const CSVFILE_SEMICOLON = '/[;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
	const CSVFILE_COMMA = '/[,](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';

	protected $_context = null;
	
	protected $_source;
	
	protected $_fields;
	
	protected $_fieldexpression;
	
	protected $_sourceType;
	

	/**
	 * Text File Data Set
	 *
	 * @param Context $context
	 * @param string $source
	 * @param array $fields
	 * @param string $fieldexpression
	 * @return TextFileDataSet
	 */
	public function __construct($context, $source, $fields, $fieldexpression = null)
	{
		if (is_null($fieldexpression))
			$fieldexpression = TextFileDataSet::CSVFILE;

		if (!is_array($fields))
		{
			throw new InvalidArgumentException("You must define an array of fields.");
		}
		if (!preg_match('~(http|https|ftp)://~', $source))
		{
			if ($source instanceof FilenameProcessor)
			{
				$this->_source = $source->FullQualifiedNameAndPath;
			}
			else 
			{
				$this->_source = $source;
			}	
			if (!FileUtil::Exists($this->_source))
			{
				throw new NotFoundException("The specified file " . $this->_source . " does not exists")	;
			}
			
			$this->_sourceType = "FILE";
		}
		else
		{
			$this->_source = $source;
			$this->_sourceType = "HTTP";
		}
		
		
		$this->_context = $context;		
		$this->_fields = $fields;
		
		if ($fieldexpression == 'CSVFILE')
		{
			$this->_fieldexpression = TextFileDataSet::CSVFILE;
		}
		else
		{
			$this->_fieldexpression = $fieldexpression;		
		}		
	}

	/**
	*@access public
	*@param string $sql
	*@param array $array
	*@return DBIterator
	*/
	public function getIterator()
	{
		$old = ini_set('auto_detect_line_endings', true);
		$handle = @fopen($this->_source, "r");
		ini_set('auto_detect_line_endings', $old);
		if (!$handle)
		{
			throw new DatasetException("TextFileDataSet failed to open resource");
		}
		else
		{
			try
			{
				$it = new TextFileIterator($this->_context, $handle, $this->_fields, $this->_fieldexpression);
				return $it;
			}
			catch (Exception $ex)
			{
				fclose($handle);
			}
		}
	}
	
}
?>
