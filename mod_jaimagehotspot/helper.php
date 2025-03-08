<?php
/**
 * ------------------------------------------------------------------------
 * JA Image Hotspot Module for Joomla 2.5 & 3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

class modJaimagehotspotHelper {
	static function file_get_contents_curl($url)
	{
		$ch = curl_init();
		$timeout = 5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        curl_setopt($ch,CURLOPT_HEADER, false);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch,CURLOPT_ENCODING, "UTF-8");
        curl_setopt($ch,CURLOPT_VERBOSE, 1);

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	static function get_domain($url)
	{
	  $pieces = parse_url($url);
	  $domain = isset($pieces['host']) ? $pieces['host'] : '';
	  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
		return $regs['domain'];
	  }
	  return false;
	}
	
	static function websiteLayout($data) {
		$jinput = Factory::getApplication()->input;
		$cutnumber = $jinput->get('cutnumber', 50, 'INT');
		$content_url = $jinput->get('content_url', '#', 'RAW');
		$http = 'http://';
		if (preg_match('/^https/', $content_url)) $http='https://';
		$domain = $http.modJaimagehotspotHelper::get_domain($content_url);
		$domain = rtrim($domain, '/');
		$content_img = $jinput->get('content_img', '', 'RAW');
		echo '<div class="jawb_layout"><a target="_blank" href="'.$content_url.'">';
		if (!empty($content_img)) {
			echo '<img width="100%" src=\''.Uri::base(true).'/'.$content_img.'\' />';
		} elseif (!empty($data['img'])) {
			$img = $data['img'];
			if (!preg_match('/^http/', $data['img']) && !preg_match('/^\/\//', $data['img'])) {
				$img = $domain.'/'.$data['img'];
			}
			echo '<img width="100%" src="'.$img.'" /><br/>';
		}
		echo '<b>'.substr(strip_tags(mb_convert_encoding($data['title'], 'UTF-8', 'auto')),0,$cutnumber).'</b></a><br/>
			'.substr(strip_tags(mb_convert_encoding($data['description'], 'UTF-8', 'auto')),0,$cutnumber).'...
		</div>';
	}
	
	static function videoLayout($content) {
		$jinput = Factory::getApplication()->input;
		$content_img = $jinput->get('content_img', '', 'RAW');
		$title = $jinput->get('title', '', 'RAW');
		echo '<div class="jashowvideo" data-ifr="'.$content.'" style="'.(!empty($content_img) ? '' : 'width:200px;height:200px;').'">
		'.(!empty($content_img) ? '<img width="100%" src=\''.Uri::base(true).'/'.$content_img.'\' />' : '').'
		'.(!empty($title) ? '<b>'.$title.'</b>' : '').'
		</div>';
	}

	static function getcontentAjax() {
		$jinput = Factory::getApplication()->input;
		$content_type = $jinput->get('content_type', 'default', 'RAW');
		$content_url = $jinput->get('content_url', 'default', 'RAW');
		$link = $jinput->get('link', '', 'RAW');
		$content_img = $jinput->get('content_img', '', 'RAW');
		$details = $jinput->get('details', '', 'RAW');
		$vw = $jinput->get('vwidth', 400, 'INT');
		$vh = $jinput->get('vheight', 400, 'INT');
		if ($content_type == 'default' || empty($content_type)) {
			echo '<div class="janone_layout"> 
				'.(!empty($content_img) ? '<img width="100%" src=\''.Uri::base(true).'/'.$content_img.'\' />' : '').'
				'.(!empty($details) ? $details : '').'
			</div>';
			return;
		}
		if (!empty($content_url) && $content_url != '') {
			$content_url = $content_url;
			if ($content_type == 'social') {
				echo '<div class="jasocial_layout">';
				if (preg_match('/facebook/', $content_url)) {
					echo '<iframe src=\'https://www.facebook.com/plugins/page.php?href='.urlencode($content_url).'&tabs=timeline&width=340&height=70&small_header=true&adapt_container_width=true&hide_cover=false&show_facepile=true&appId=403317886486312\' width=\'340\' height=\'70\' style=\'border:none;overflow:hidden\' scrolling=\'no\' frameborder=\'0\' allowTransparency=\'true\'></iframe>';
				}
				if (preg_match('/twitter/', $content_url)) {
					echo '
					<blockquote class="twitter-tweet" data-dnt="true" data-cards="hidden" hide_media="true">
						<a href="'.$content_url.'">
						'.(!empty($content_img) ? '<img width="100%" src=\''.Uri::base(true).'/'.$content_img.'\' />' : '').'
						<p style="text-align:right;">
							<span>'.Text::_('JAI_FOLLOW_ON').'</span>
							<span><img class="social_follow_image" src=\''.Uri::base(true).'/modules/mod_jaimagehotspot/assets/images/twitter-logo.png\' /></span>
						</p>
						</a>
					</blockquote>
					<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';
				}
				if (preg_match('/instagram/', $content_url) || preg_match('/pinterest/', $content_url)) {
					$imgtype = 'pinterest';
					if (preg_match('/instagram/', $content_url)) $imgtype = 'instagram';
					echo '
					<a target="_blank" href="'.$content_url.'">
						'.(!empty($content_img) ? '<img width="100%" src=\''.Uri::base(true).'/'.$content_img.'\' />' : '').'
						<p style="text-align:right;">
							<span>'.Text::_('JAI_FOLLOW_ON').'</span> 
							<span><img class="social_follow_image" src=\''.Uri::base(true).'/modules/mod_jaimagehotspot/assets/images/'.$imgtype.'-logo.png\' /></span>
						</p>
					</a>';
				}
				echo '</div>';
			} elseif ($content_type == 'video') {
				if (preg_match('/youtu/', $content_url)) {
				    $embed = $content_url;
				    // preg_match("/v=([a-zA-Z0-9-_]+)/", $content_url, $m);
					$m = explode('.be/', $embed);
				    if (!empty($m[1])) {
				        $embed = "https://www.youtube.com/embed/".$m[1];
				    }
					 // modJaimagehotspotHelper::videoLayout('<iframe width=\'100%\' height=\''.($vh-60).'\' src=\''.($embed).'?autoplay=1\' frameborder=\'0\' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>');
					 echo '<iframe width="560" height="315" src="'.$embed.'" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
				} elseif (preg_match('/vimeo/', $content_url)) {
					modJaimagehotspotHelper::videoLayout('<iframe src=\''.$content_url.'?autoplay=1\' width=\'100%\' height=\''.($vh-60).'\' frameborder=\'0\' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');
				} else {
					// if mp4 url
					$video_html = '
						<video controls>
						<source src=\''.$content_url.'\' type=\'video/mp4\'>
						Your browser does not support the video tag.
						</video>';
					modJaimagehotspotHelper::videoLayout($video_html);
				}
			} elseif ($content_type == 'website') {
				$html = modJaimagehotspotHelper::file_get_contents_curl($content_url);
				$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
				
				$dom = new DOMDocument();
				@$dom->loadHTML($html);

				$nodes = $dom->getElementsByTagName('title');
				$title = $nodes->item(0)->nodeValue;
				$metas = $dom->getElementsByTagName('meta');
				$description = '';
				$keywords = '';
				for ($i = 0; $i < $metas->length; $i++)
				{
					$meta = $metas->item($i);
					if($meta->getAttribute('name') == 'description')
						$description = $meta->getAttribute('content');
					if($meta->getAttribute('name') == 'keywords')
						$keywords = $meta->getAttribute('content');
					if($meta->getAttribute('property') == 'og:image')
						$ogimg = $meta->getAttribute('content');
				}
				
				$links = $dom->getElementsByTagName('link');
				for ($i = 0; $i < $links->length; $i++)
				{
					$link = $links->item($i);
					if($link->getAttribute('rel') == 'image_src')
						$imagelink = $link->getAttribute('href');
				}
				$arr = array();
				$arr['img']='';
				if (!empty($ogimg)) {
					$arr['img'] = $ogimg;
				} elseif (!empty($imagelink)) {
					$arr['img'] = $imagelink;
				}
				$arr['url'] = $content_url;
				$arr['title'] = $title;
				$arr['description'] = $description;
				$arr['keywords'] = $keywords;
				modJaimagehotspotHelper::websiteLayout($arr);
			} elseif ($content_type == 'default') {
				echo ($content_img=='' ? '' : '<img src="'.Uri::base(true).'/'.$content_img.'" width="100%" />').$details;
			}
		}
	}
}