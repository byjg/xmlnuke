<?php
require_once("compact.php");

if ($argc == 3)
{
	$filelist = array();
	$filename = $argv[1];
	echo "\nProcessing join.\n";
	echo "Path: $argv[2]\n\n";
	echo "-------\n";
	echo "Stage 1\n";
	echo "-------\n";
	find_required_php($filename, &$filelist, $argv[2]);

	echo "-------\n";
	echo "Stage 2\n";
	echo "-------\n";
	$handle = fopen("joinned.php", "w+");
	foreach ($filelist as $file)
	{
		echo "Joinning $file...\n";
		$filecontent = file_get_contents($file);
		$filecontent = preg_replace('/require_once\s*\(["\'](.*)["\']\)\s*;/i', '', $filecontent);
		//$filecontent = CompactPhp($filecontent);
		fwrite($handle, $filecontent);
	}
	fclose($handle);
}
else
{
	$message =
		"Wrong parameters count. You need pass the file you want to join and PATH for PHP files\n\n" .
		"Example:\n" .
		"   join start-php-file.php pathlist:pathlist:pathlist\n\n";

	die( $message );
}

function find_required_php($filename, &$filelist, $pathlist)
{
	$pathar = explode(":", $pathlist);
	$found = false;
	foreach ($pathar as $path)
	{
		//echo "++".$path."/".$filename."\n";
		if (file_exists($path."/".$filename))
		{
			$found = true;
			break;
		}
	}
	if (!$found)
	{
		echo "Not Found: $filename\n";
		return;
	}
	$path .= "/";
	
	
	if (!array_key_exists($path . $filename, $filelist))
	{
		echo "Processing $path$filename... \n";
		$filelist[$path . $filename] = $path . $filename;
	}
	else
	{
		echo "Already added: $path$filename\n";
		return;
	}
	$lines = file($path . $filename);
	$requires = preg_grep('/require_once/i', $lines);
	foreach($requires as $item)
	{
		$comment = strpos($item, "//");
		if ($comment === false)
		{
			$comment = strpos($item, "/*");
			if ($comment === false)
			{
				$sep = "\"";
				$sepStart = strpos($item, $sep);
				if ($sepStart === false)
				{
					$sep = "'";
					$sepStart = strpos($item, $sep);
					if ($sepStart === false)
					{
						echo "Warning: $item \n";
						continue;
					}
				}
				$sepEnd = strpos($item, $sep, $sepStart + 1);
				$item_require = substr($item, $sepStart+1, $sepEnd-$sepStart-1);
				find_required_php($item_require, &$filelist, $pathlist);
			}
			else
			{
				echo "Commented: $item \n";
			}
		}
		else
		{
			echo "Commented: $item \n";
		}
	}

}

?>
