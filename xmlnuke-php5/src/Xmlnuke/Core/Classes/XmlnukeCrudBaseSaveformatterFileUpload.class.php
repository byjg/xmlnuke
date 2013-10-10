<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Classes;

use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Processor\ForceFilenameLocation;
use Xmlnuke\Core\Processor\UploadFilenameProcessor;
use Xmlnuke\Util\FileUtil;
use Xmlnuke\Util\ImageUtil;

class  XmlnukeCrudBaseSaveFormatterFileUpload implements IEditListFormatter
{
	/**
	 * @var Context
	 */
	protected $_context = "";
	protected $_path = "";
	protected $_saveAs = "";

	protected $_width = 0;
	protected $_height = 0;

	public function __construct($context, $path, $saveAs = "*")
	{
		$this->_context = $context;
		$this->_path = $path;
		$this->_saveAs = $saveAs;
	}

	public function resizeImageTo($width, $height)
	{
		$this->_width = $width;
		$this->_height = $height;
	}

	public function Format($row, $fieldname, $value)
	{
		$files = $this->_context->getUploadFileNames();

		if ($files[$fieldname] != "")
		{
			$fileProcessor = new UploadFilenameProcessor($this->_saveAs);
			$fileProcessor->setFilenameLocation(ForceFilenameLocation::DefinePath, FileUtil::GetTempDir());

			// Save the files in a temporary directory
			$result = $this->_context->processUpload($fileProcessor, false, $fieldname);

			// Get a way to rename the files
			$fileinfo = pathinfo($result[0]);
			if ($this->_saveAs != "*")
			{
				$path_parts = pathinfo($this->_saveAs);
			}
			else
			{
				$path_parts = pathinfo($result[0]);
			}
			$newName = $this->_path . FileUtil::Slash() .  $path_parts['filename'] . "." . $fileinfo["extension"];

			// Put the image in the right place
			if (strpos(".jpg.gif.jpeg.png", ".".$fileinfo["extension"])===false)
			{
				rename( $result[0]  , $newName  );
			}
			else
			{
				if (($this->_width > 0) || ($this->_height > 0))
				{
					$image = new ImageUtil($result[0]);
					$image->resizeAspectRatio($this->_width, $this->_height, 255, 255, 255)->save($newName);
				}
				else
				{
					rename( $result[0]  , $newName  );
				}
			}
			return $newName;
		}
		else
		{
			return $row->getField($fieldname);
		}
	}
}


?>