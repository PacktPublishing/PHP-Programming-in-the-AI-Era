<?php
namespace Cookbook\View;

use FilterIterator;
use RecursiveDirectoryIterator;
class ChapInfo
{
	#[ChapInfo\getChaps(
		"description:returns a set of links for chapters",
		"param_1: <string> starting directory"		
	)]
	public static function getChaps(string $dir) : string
	{
		$style = 'list-style-type: none;';
		$files = new RecursiveDirectoryIterator($dir . '/src/');
		$filt = new class ($files) extends FilterIterator {
			public function accept() : bool
			{
				$result = FALSE;
				if (parent::current()->isDir()) {
					$fn = trim(parent::current()->getFilename());
					if (str_starts_with($fn, 'Chapter')) {
						$result = TRUE;
					} else {
						$result = FALSE;
					}
				}
				return $result;
			}
		};
		$html = [];
		foreach ($filt as $item) {
			$chap = basename($item);
			$html[] = '<br /><a href="?chap=' . $chap . '">' . $chap . '</a>';
		}
        sort($html);
		return implode(PHP_EOL, $html);
	}
	#[ChapInfo\getChapFiles(
		"description:returns a set of links for files for a given chapter",
		"param_1: <string> starting directory",
		"param_2: <string> name of the chapter"
	)]
	public static function getChapFiles(string $dir, string $chap) 
	{
		$style = 'list-style-type: none;';
		$files = new RecursiveDirectoryIterator($dir . '/src/' . $chap);
		$filt  = new class ($files) extends FilterIterator {
			public function accept() : bool
			{
				return (parent::current()->isFile() 
					    && parent::current()->getExtension() === 'php');
			}
		};
        $mid  = [];
		foreach ($filt as $item) {
			$fn = basename($item);
			$mid[] = '<li><a href="/src/' . $chap . '/' . $fn . '">' . $fn . '</a></li>';
		}
        sort($mid);
		$html = '<ul>';
        $html .= implode(PHP_EOL, $mid);
		$html .= '<a href="/">BACK</a>';
		$html .= '</ul>';
		return $html;
	}
	#[ChapInfo\getChaps("description:runs phpinfo()")]
	public static function getInfo()
	{
		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();
		return $info;
	}
}
