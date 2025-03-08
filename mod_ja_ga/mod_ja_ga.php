<?php 
/**
 * $JA#COPYRIGHT$
 */
 
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;

$basepath = Uri::base(true).'/modules/' . $module->module . '/assets/';
$doc = Factory::getDocument();
$user = Factory::getUser();

// Include the helper and assets
require_once( dirname(__FILE__).'/helper.php' );
include_once(dirname(__FILE__).'/assets/assets.php');

require_once (dirname(__FILE__).'/vendors/autoload.php');

$helper = new JAGAHelper();
$app = Factory::getApplication();
$isHome = ($app->getMenu()->getActive()->home) ? true : false;
$currentPage = Uri::current();

// Set some keys for Google Analytics API.
$apiKey = ($params->get('api_key','') != '') ? $params->get('api_key','') : 'AIzaSyCFfFeP2tEJqMYLj4JmmQVXsF7tYzevWr4';
$clientId = ($params->get('client_id','') != '') ? $params->get('client_id','') : '934858784004-ma41utent9r7njo82a9skgk35i2mq9rf.apps.googleusercontent.com';
$clientSecret = ($params->get('client_secret','') != '') ? $params->get('client_secret','') : '0XdtKngq-OaLza_E2oOKhb1I';

// Create Google Client Object.
$client = new Google_Client();
$client->setAccessType('offline');
$client->setScopes('https://www.googleapis.com/auth/analytics.readonly');
$client->setApplicationName('JA Google Analytics');
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');

// Setup Google Analytics API
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setDeveloperKey($apiKey);

// Create Google Analytics Service Object.
$service = new Google_Service_Analytics($client);
$scriptUri = Uri::current();
if (isset($_REQUEST['ja_refresh'])) {
	$helper->clear_cache();
	header('Location: ' .$scriptUri);
}
if ($helper::get_token()) {
	$token = $helper::get_token();
	$client->setAccessToken($token);
	
	// If the Access Token is expired.
	if ($client->isAccessTokenExpired()) {
		$helper->refresh($client, json_decode($token)->refresh_token);
	}
	$access_token = json_decode($client->getAccessToken(), true)['access_token'];
	
	$profileId = $helper->getProfile($service, $params);
	
	if (!isset($profileId['error'])) {
		$from = $params->get('ja_ga_time','today');
		$to = 'today';

		$fetch_url = 'https://www.googleapis.com/analytics/v3/data/ga?ids=ga:'.$profileId.'&start-date='.$from.'&end-date='.$to;
 		$pageViews = $helper->getReports($params,$fetch_url, $access_token,'ga:pageviews');
 		$bounceRate = $helper->getReports($params,$fetch_url, $access_token, 'ga:bounceRate');
		if ($isHome) {
		  $fetch_users_url = 'https://www.googleapis.com/analytics/v3/data/realtime?ids=ga:'.$profileId.'&metrics=rt:activeUsers&access_token='.$access_token;
//		  $pageViews = $helper->getReports($params,$fetch_url, $access_token,'ga:pageviews');
//		  $bounceRate = $helper->getReports($params,$fetch_url, $access_token, 'ga:bounceRate');
		} else {
		  $fetch_users_url = 'https://www.googleapis.com/analytics/v3/data/realtime?ids=ga:'.$profileId.'&metrics=rt:activeUsers&dimensions=rt:pagePath&access_token='.$access_token;
//		  $pageViews = $helper->getReports($params,$fetch_url, $access_token,'ga:pageviews&dimensions=ga:pagePath&sort=-ga:pageviews', $currentPage);
//		  $bounceRate = $helper->getReports($params,$fetch_url, $access_token, 'ga:bounceRate&dimensions=ga:pagePath&sort=-ga:bounceRate', $currentPage);
		}

		$time = '';
		switch($from) {
			case 'today' :
			default : 
				$time = Text::_('JA_GA_TODAY');
				break;
			case 'yesterday' :
				$time = Text::_('JA_GA_YESTERDAY');
				break;
			case '7daysAgo' :
				$time = Text::_('JA_GA_SINCE_7_DAYS_AGO');
				break;
			case '14daysAgo' :
				$time = Text::_('JA_GA_SINCE_14_DAYS_AGO');
				break;
			case '30daysAgo' :
				$time = Text::_('JA_GA_SINCE_30_DAYS_AGO');
				break;
		}
	} else {
		$error = $profileId['error'];
	}
}

if (!$user->guest) {
	require(ModuleHelper::getLayoutPath('mod_ja_ga'));
}else{
		Factory::getApplication()->enqueueMessage(Text::_('JA_GA_LOGIN_REQUIRED'), 'warning');
}
?>
