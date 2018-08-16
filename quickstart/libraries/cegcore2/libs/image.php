<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Image {
	
	public static function compress($source, $destination, $quality){
		$info = getimagesize($source);

		if($info['mime'] == 'image/jpeg'){
			$image = imagecreatefromjpeg($source);
		}elseif($info['mime'] == 'image/gif'){
			$image = imagecreatefromgif($source);
		}elseif($info['mime'] == 'image/png'){
			$image = imagecreatefrompng($source);
		}
		
		return imagejpeg($image, $destination, $quality);
	}
	
	public static function crop($source, $rect, $destination){
		$info = getimagesize($source);

		if($info['mime'] == 'image/jpeg'){
			$image = imagecreatefromjpeg($source);
		}elseif($info['mime'] == 'image/gif'){
			$image = imagecreatefromgif($source);
		}elseif($info['mime'] == 'image/png'){
			$image = imagecreatefrompng($source);
		}
		
		$image = imagecrop($image, $rect);
		
		return imagejpeg($image, $destination);
	}
	
	public static function resize($source, $destination, $newWidth){
		$info = getimagesize($source);

		if($info['mime'] == 'image/jpeg'){
			$image = imagecreatefromjpeg($source);
		}elseif($info['mime'] == 'image/gif'){
			$image = imagecreatefromgif($source);
		}elseif($info['mime'] == 'image/png'){
			$image = imagecreatefrompng($source);
		}
		
		$newHeight = ($info[1] / $info[0]) * $newWidth;
		$tmp = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $info[0], $info[1]);
		
		return imagejpeg($tmp, $destination);
	}
	
}