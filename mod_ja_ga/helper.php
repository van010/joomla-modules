<?php
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;

require_once (dirname(__FILE__).'/vendors/autoload.php');

class JAGAHelper {
	public function clear_cache(){
		$db = Factory::getDBO();
		$query = "DROP TABLE IF EXISTS #__ja_ga_token";
		$db->setQuery($query);
		$db->execute();
	}
	
	public static function store_token($token) {
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		
		//Create table to save the token if it's not exist
		$q_create = "CREATE TABLE IF NOT EXISTS #__ja_ga_token (id INT NOT NULL , token TEXT NOT NULL);";
		$db->setQuery($q_create);
		$db->execute();
		
		// Check the token is exist or not
		$q_check = $db->getQuery(true);
		$q_check->select('id')->from('#__ja_ga_token');
		$db->setQuery($q_check);
		if ($id = $db->loadResult()) {
			$query->update('#__ja_ga_token')->set('token = ' .$db->quote($token))->where('id = '. (int) $id);
		} else {
			$query->insert('#__ja_ga_token')->columns(array('id','token'))->values('1,'.$db->quote($token));
		}
		
		$db->setQuery($query);
		$db->execute();
	}
	
	public static function get_token() {
		$db = Factory::getDBO();
		try { 
		$query = "SELECT token FROM #__ja_ga_token";
		$db->setQuery($query);		
		$token = $db->loadResult();
		}  
			catch(exception $e) {
			return; 
		}
		return $token;
	}
	
	public function refresh($client, $refresh_token) {
		try {
			$client->refreshToken($refresh_token);
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	/*
	* Method to get Profile Id
	*/
	function getProfile($service, $params){
		try {
			$profiles = $service->management_profiles->listManagementProfiles('~all','~all');
		} catch (Exception $e ) {
			return array('error' => $e->getMessage()) ;
		}
		$items = $profiles->getItems();
		if (count($items)) {
			foreach ($items as $item) {
				$profileId = $item->getId();
				if ($this->prettyDomainName($item->getwebsiteUrl()) == $this->prettyDomainName($params->get('site_url',''))) {
					return $item->getId();
				}
			}
			
			return $profileId;
		}
	}
	
	function prettyDomainName($domain){
		return str_replace(array("https://","http://"," "),"",rtrim($domain,"/"));
	}
	
	public function getReports($params,$fetch_url, $access_token, $metrics='', $current = '') {
		$url = $fetch_url.'&metrics='.$metrics.'&access_token='.$access_token;
		$content = $this->getContent($url);

		if (isset($content['rows'])) {
			if ($current == '') {
        return $content['rows'][0][0];
      } else {
        $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $site_uri = $protocol.'://'.$_SERVER['HTTP_HOST'];
        if (count($content['rows'])) {
          foreach ($content['rows'] as $row) {
            if (substr($current, -1) == '/') {
              if (substr($row[0], -1) != '/') {
                $row[0] .= '/';
              }
            } else {
              if (substr($row[0],-1) == '/') {
                $current .= '/';
              }
            }
            if ($site_uri.$row[0] == $current) {
              return $row[1];
            }
          }
        }
      }
		} else {
			return 0;
		}
	}
	
	private function getContent(&$url) {
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 600);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_ENCODING, '');
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
			if(strpos($url, 'https:') === 0) {
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			}
			$content = curl_exec($ch);
			$response = curl_getinfo( $ch );
			curl_close($ch);

			if ($response['http_code'] == 301 || $response['http_code'] == 302) {
				//follow redirect url
				if(isset($response['url']) && !empty($response['url']) && $response['url'] != $url) {
					$url = $response['url'];
					return $this->getContent($url);
				} elseif (isset($response['redirect_url']) && !empty($response['redirect_url']) && $response['redirect_url'] != $url) {
					$url = $response['redirect_url'];
					return $this->getContent($url);
				}
			}
		} else {
			// curl library is not installed so we better use something else
			$content = @file_get_contents($url);
		}
		
		return json_decode($content, true);
	}
}