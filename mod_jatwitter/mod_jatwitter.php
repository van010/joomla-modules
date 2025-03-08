<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;

$jaTwitterPath = JPATH_ROOT . '/modules/mod_jatwitter';

require_once($jaTwitterPath . '/assets/jabehavior.php');
require_once ($jaTwitterPath . '/jatwitter.php');
require_once ($jaTwitterPath . '/helper.php');
require_once ($jaTwitterPath . '/TwitterAPIExchange.php');

include_once($jaTwitterPath . '/assets/asset.php');

// get params.
$is_ajax 			= $params->get('is_ajax');
$taccount 			= $params->get('taccount');
$show_limit 		= $params->get('show_limit',1);
$headingtext 		= $params->get('headingtext');
$showfollowlink 	= $params->get('showfollowlink', 1);
$showtextheading 	= $params->get('showtextheading');
$displayitem 		= $params->get('displayitem');
$showIcon 			= $params->get('show_icon', 1);
$showUsername 		= $params->get('show_username', 1);
$showSource 		= $params->get('show_source', 1);
$apikey 			= $params->get('apikey');
$screenName 		= $params->get('taccount', 'JoomlartDemo');
$layout 			= $is_ajax ? 'default.ajax' : 'default';
$useDisplayAccount 	= $params->get('use_display_taccount', 0);
$useFriends 		= $params->get('use_friends', 0);
$iconsize 			= $params->get('icon_size', 48);
$sizeIconaccount 	= $params->get('size_iconaccount', 48);
$sizeIconfriend 	= $params->get('size_iconfriend', 24);

$consumer_key 		= $params->get('consumer_key', '');
$consumer_secret 	= $params->get('consumer_secret', '');
$oauth_access_token = $params->get('oauth_access_token', '');
$oauth_access_token_secret = $params->get('oauth_access_token_secret', '');

$jatHerlper = new modJaTwitterHelper($consumer_key, $consumer_secret, $oauth_access_token, $oauth_access_token_secret);
// render layout


// enable or disable using cache data
$jatHerlper->setCache($params->get('enable_cache'), $params->get('cache_time'));
// if show account information
if ($useDisplayAccount) {
    $accountInfo = $jatHerlper->getTwitter($screenName, 'show');
}
if ($useFriends) {
    $friends = $jatHerlper->getTwitter($screenName, 'friends', $params->get('max_friends', 10));
}
$list = array();
if ($params->get('show_tweet') == '1') {
	if ($show_limit!=0) {
		$list = $jatHerlper->getTwitter($screenName, "user_timeline", $show_limit, (int) $params->get('tweets_cachetime', 30));
	}
}

$layout = 'default';
include (ModuleHelper::getLayoutPath('mod_jatwitter', $layout));