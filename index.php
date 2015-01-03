<?php
/* Facebook Connect for Textcube 1.10
   ----------------------------------
   Version 4.0
   Needlworks development team.

   Creator          : inureyes
   Maintainer       : inureyes

   Created at       : 2010.4.29
   Last modified at : 2014.12.31

 This plugin adds facebook like button on the blog posts.
 For the detail, visit http://forum.tattersite.com/ko

 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function FacebookConnect_addHeader($target) {
	global $blog, $pageTitle,$entries,$defaultURL, $configVal;
	$context = Model_Context::getInstance();
	if(count($entries) == 1) {
		$config = Setting::fetchConfigVal($configVal);
		if(empty($config['Thumbnail'])) $config['Thumbnail'] = true;
		else $config['Thumbnail'] = false;
		$target .= '
	<meta property="og:title" content="'.htmlspecialchars(trim($pageTitle)).'"/>
	<meta property="og:site_name" content="'.htmlspecialchars(trim($blog['title'])).'"/>
		';
//	<meta property="og:image" content=""/>
		if($config['Thumbnail'] == true) {
			$imagePath = FacebookConnect_getAttachmentExtract($entries[0]['content']);
			if(!is_null($imagePath)) {
				$target .= '
	<link rel="image_src" href="'.$context->getProperty('uri.default').'/attach/'.getBlogId().'/'.$imagePath.'" />
';
			}
		}
	}
	return $target;
}

function FacebookConnect_addFBJSAPI($target) {
	$target .= <<<EOS
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/ko_KR/sdk.js#xfbml=1&appId=225673334156927&version=v2.0";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
EOS;
	return $target;
}

function FacebookConnect_addButton($target, $mother) {
	global $defaultURL, $entry, $blog, $configVal;
	$context = Model_Context::getInstance();
	$plink = $defaultURL."/".($blog['useSloganOnPost'] ? 'entry/'.urlencode($entry['slogan']) : $entry['id']);
	$config = Setting::fetchConfigVal($configVal);
	if(empty($config['Locale'])) $config['Locale'] = 'en_US';
	if(empty($config['Layout'])) $config['Layout'] = 'standard';
	if(empty($config['Width']) || !is_numeric($config['Width'])) $config['Width'] = 550;
	//if(defined('__TEXTCUBE_IPHONE__')) $config['Width'] = 310;
	if(empty($config['Height']) || !is_numeric($config['Height'])) $config['Height'] = 50;
	if(empty($config['Font'])) $config['Font'] = 'none';
	if ($context->getProperty('blog.displaymode','desktop') == 'mobile') {
		$config['Width'] = '100%';
	} else if(!empty($config['AdjustWidth']) && $config['AdjustWidth'] == 1) {
		$config['Width'] =  Misc::getContentWidth();	// Fast after Textcube 1.8.4, however not gurantee for 1.8.3 and below.
	}
	if(empty($config['Show'])) $config['Show'] = 'false';
	else if ($config['Show'] == 1) $config['Show'] = 'true';
	else $config['Show'] = 'false';
	if(empty($config['VerbToDisplay'])) $config['VerbToDisplay'] = 'like';
	if(empty($config['ColorScheme'])) $config['ColorScheme'] = 'light';
	// Comment box
	if(empty($config['NumPosts']) || !is_numeric($config['NumPosts'])) $config['NumPosts'] = 2;

	$button = '<div class="fb-like" data-href="'.$plink.'" data-layout="'.$config['Layout'].'" data-action="'.$config['VerbToDisplay'].'" data-show-faces="'.$config['Show'].'" data-share="true"></div>'.CRLF;
	if(!empty($config['useComment'])) {
		$button .= '<div class="fb-comments" data-href="'.$plink.'" data-width="'.$config['Width'].'" data-numposts="'.$config['NumPosts'].'" data-colorscheme="'.$config['ColorScheme'].'"></div>';
	}
	return $target.$button;
}

function FacebookConnect_getAttachmentExtract($content){
	$result = null;
	if(preg_match_all('/\[##_(1R|1L|1C|2C|3C|iMazing|Gallery)\|[^|]*\.(gif|jpg|jpeg|png|bmp|GIF|JPG|JPEG|PNG|BMP)\|.*_##\]/si', $content, $matches)) {
		$split = explode("|", $matches[0][0]);
		$result = $split[1];
	}else if(preg_match_all('/<img[^>]+?src=("|\')?([^\'">]*?)("|\')/si', $content, $matches)) {
		if( stristr($matches[2][0], 'http://') ){
			$result = $matches[2][0];
		}
	}
	return $result;
}

function FacebookConnect_handleconfig($data) {
	return true;	// Validation occurs at runtime.
}
?>
