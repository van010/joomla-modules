<?php
/**
 * $JA#COPYRIGHT$
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * JATwitter class.
 */
class JATwitter
{

	//OAuth settings
	var $consumer_key = ''; //Consumer key
	var $consumer_secret = ''; //Consumer secret
	var $oauth_access_token = ''; //OAuth Access Token
	var $oauth_access_token_secret = ''; //OAuth Access Token secret

	/**
     * @var string $_screenName
     *
     * @access public.
     */
	var $_screenName = '';

	/**
     * @var string $_format format of return data;
     *
     * @access protected
     */
	var $_format = 'json';

	/**
     * @var string $_auth;
     *
     * @access protected
     */
	var $_auth = '';

	/**
     * @var integer $_status status of response
     *
     * @access protected
     */
	var $_status = '';

	/**
     * @var stream $_output  data of response
     *
     * @access protected.
     */
	var $_output = '';

	/**
     * @var string $_message message of reponse.
     *
     * @access protected.
     */
	var $_message = '';


	/**
     * set username and password which using for authencate
     *
     * @param string $username
     * @param string $password
     * @return JATwitter.
     */
	function setAuth($consumer_key, $consumer_secret, $oauth_access_token, $oauth_access_token_secret)
	{
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->oauth_access_token = $oauth_access_token;
		$this->oauth_access_token_secret = $oauth_access_token_secret;
	}


	/**
     * set sreen name same as twitter username
     *
     * @param string $screenName
     * @return JATwitter
     */
	function setScreenName($screenName)
	{
		$this->_screenName = $screenName;
		return $this;
	}


	/**
     * set format of return data
     *
     * @param string $format
     * @return JATwitter
     */
	function setFormat($format)
	{
		$this->_format = $format;
		return $this;
	}


	/**
     * get tweets base on method request
     *
     * @param string $method
     * @param integer $count default equal 10 item
     * @return boolean if have problem with request service, else return string.
     */
	function getTweets($method, $count = 10)
	{
		// find url request
		if (empty($this->consumer_key) || empty($this->consumer_secret)) {
			return;
		}
		
		$apiMethods = array(
			'user_timeline' => 'https://api.twitter.com/1.1/statuses/home_timeline.json',
			'followers' => 'https://api.twitter.com/1.1/followers/ids.json.json',
			'friends' => 'https://api.twitter.com/1.1/friends/ids.json',
			'show' => 'https://api.twitter.com/1.1/users/show.json'
		);
		
		$url = '';
		$params = '';
		switch ($method)
		{
			case 'show':
				$url = 'https://api.twitter.com/1.1/users/show.json';
				$params = '?screen_name='.$this->_screenName;
				break;
			case 'user_timeline':
				$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
				$params = '?screen_name='.$this->_screenName.'&count='.$count;
				break;
			case 'friends':
				$url = 'https://api.twitter.com/1.1/friends/list.json';
				$params = '?screen_name='.$this->_screenName.'&count='.$count;
				break;
			case 'followers':
				$url = 'https://api.twitter.com/1.1/followers/list.json';
				$params = '?screen_name='.$this->_screenName.'&count='.$count;
				break;
		}
		
		// marke request twitter api;
		if (!empty($url)) {

			$settings = array(
				'consumer_key' => $this->consumer_key,
				'consumer_secret' => $this->consumer_secret,
				'oauth_access_token' => $this->oauth_access_token,
				'oauth_access_token_secret' => $this->oauth_access_token_secret
			);

			$requestMethod = 'GET';
			$twitter = new TwitterAPIExchange($settings);
			$result = $twitter->setGetfield($params)
					->buildOauth($url, $requestMethod)
					->performRequest();
			
			$obj = json_decode($result);
			
			if(!$obj) {
				$this->_message = Text::_('ERROR_SERVER_RESPONSE');
				return false;
			}
			if(isset($obj->errors) && count($obj->errors)) {	
				$this->_message = Text::_('ERROR_SERVER_RESPONSE').': '.$obj->errors[0]->message;
				return false;	
			}
			
			return $this->parseData($method, $obj);

		}
		return null;
	}


	/**
     * only parser json which response from api method.
     */
	function parseData($method, $obj)
	{
		return $this->callMethod("parser" . ucfirst($method), $obj);
	}


	/**
     * magic method
     *
     * @param string method  method is calling
     * @param string $params.
     * @return unknown
     */
	function callMethod($method, $params)
	{
		if (method_exists($this, $method)) {
			if (is_callable(array($this, $method))) {
				return call_user_func(array($this, $method), $params);
			}
		}
		return false;
	}


	/**
     * get data for element's 'attribute
     *
     * @param XML Attribute of XML
     * @return string
     */
	function getData($obj)
	{
		return @(string) $obj;
	}


	/**
     * only parser xml which response from api method "show", it contain user's data
     *
     * @param JSimpleXML $xml
     * @return stdClas
     */
	function parserShow($obj)
	{
		return $obj;
	}


	/**
     * only parser xml which response from api method "user_timeline", it contain twitters
     *
     * @param JSimpleXML $xml
     * @return array.
     */
	function parserUser_timeline($items)
	{
		$out = array();
		foreach ($items as $item) {
			if (!isset($item->id))
			continue;
			$obj = new stdClass();
			$user = $item->user;
			$obj->id = $this->getData($item->id);
			$obj->source = $this->getData($item->source);
			$obj->created_at = $this->getData($item->created_at);
			$obj->text = $this->getData($item->text);
			$obj->name = $this->getData($user->name);
			$obj->screen_name = $this->getData($user->screen_name);
			$obj->profile_image_url = $this->getData($user->profile_image_url);
			$out[] = $obj;
		}
		return $out;
	}


	/**
     * only parser xml which response from api method "friend", it contain friends' data
     *
     * @param JSimpleXML $xml
     * @return array.
     */
	function parserFriends($friends)
	{
		$out = array();
		foreach ($friends->users as $friend) {
			$out[] = $this->parserShow($friend);
		}

		return $out;
	}


	/**
     * only parser xml which response from api method "followers", it contain friends' data
     *
     * @param JSimpleXML $xml
     * @return array.
     */
	function parserFollowers($friends)
	{
		$out = array();
		foreach ($friends->users as $friend) {
			$out[] = $this->parserShow($friend);
		}
		return $out;
	}


	/**
     * get message of reponse
     *
     * @return string;
     */
	function getMessage()
	{
		return $this->_message;
	}
}