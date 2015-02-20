<?php
/**
 * FAT PONY SOCIAL SHARE PLUGIN
 * @author      Erica Franz | Fat Pony
 * @link        http://fatpony.me/
 * @copyright   Copyright (c) 2014, Fat Pony
 * @license     GPL-2.0+
 * @version     1.0.1
 *
 * Adapted from the Social Share Starter Plugin by KK
 * @link        http://newinternetorder.com/giveaway-heres-why-social-share-counters-suck-plus-what-i-can-give-you-that-doesnt/
 */

//* Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) exit;

//* Executed Output
function fatpony_sharecount_execute() {
	$s_number = intval(fatpony_sharecount_calculate());
        
	return $s_number;
}

//* Let's calculate the shares for each social network
function fatpony_sharecount_calculate() {
	$new_shares = 0;
	$real_shares_count = 0;
	
	if(function_exists('curl_version')) {
		$version = curl_version();
		$bitfields = array(
			'CURL_VERSION_IPV6', 
			'CURLOPT_IPRESOLVE'
		);

		foreach($bitfields as $feature) {
			if($version['features'] & constant($feature)) {
				// Replace https with http so we can count both ssl and non-ssl shared urls
				$us_permalink = str_replace( 'https://', 'http://', get_permalink() );
				
				// Get the https (default) url
				$real_shares = new shareCount( get_permalink() );
				
				// Get the http url
				$us_real_shares = new shareCount( $us_permalink );
				
				$real_shares_count += $real_shares->get_tweets();
				$real_shares_count += $us_real_shares->get_tweets();
				$real_shares_count += $real_shares->get_fb();
				$real_shares_count += $us_real_shares->get_fb();
				$real_shares_count += $real_shares->get_linkedin();
				$real_shares_count += $us_real_shares->get_linkedin();
				$real_shares_count += $real_shares->get_plusones();
				$real_shares_count += $us_real_shares->get_plusones();
				$real_shares_count += $real_shares->get_pinterest();
				$real_shares_count += $us_real_shares->get_pinterest();
				break;
			}
		}
	}
	
	$total_shares = $new_shares + $real_shares_count;
	
	return $total_shares;
}

//* Uses a modified PHP Social Share Count Class by Sunny Verma - http://toolspot.org
class shareCount {

	private $url,$timeout;

	function __construct($url,$timeout=10) {
		$this->url=rawurlencode($url);
		$this->timeout=$timeout;
	}

	function get_tweets() {
		$json_string = $this->file_get_contents_curl('http://urls.api.twitter.com/1/urls/count.json?url=' . $this->url);
		
		if($json_string === false) return 0;
		
		$json = json_decode($json_string, true);
		
		return isset($json['count'])?intval($json['count']):0;
	}

	function get_linkedin() {
		$json_string = $this->file_get_contents_curl('http://www.linkedin.com/countserv/count/share?url='.$this->url.'&format=json');
		
		if($json_string === false) return 0;
		
		$json = json_decode($json_string, true);
		
		return isset($json['count'])?intval($json['count']):0;
	}

	function get_fb() {
		$json_string = $this->file_get_contents_curl('http://graph.facebook.com/?id='.$this->url);
		
		if($json_string === false) return 0;
		
		$json = json_decode($json_string, true);
		
		//return isset($json[0]['total_count'])?intval($json[0]['total_count']):0;
		return isset($json['shares'])?intval($json['shares']):0;
	}

	function get_plusones() {
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"'.rawurldecode($this->url).'","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		
		$curl_results = curl_exec($curl);
		
		curl_close($curl);
		
		if($curl_results === false) return 0;
		
		$json = json_decode($curl_results, true);
		return isset($json[0]['result']['metadata']['globalCounts']['count'])?intval( $json[0]['result']['metadata']['globalCounts']['count'] ):0;
	}
	
	function get_pinterest() {
		$return_data = $this->file_get_contents_curl('http://api.pinterest.com/v1/urls/count.json?url='.$this->url);
		
		if($return_data === false) return 0;
		
		$json_string = preg_replace('/^receiveCount\((.*)\)$/', "\\1", $return_data);
		$json = json_decode($json_string, true);
		
		return isset($json['count'])?intval($json['count']):0;
	}

	private function file_get_contents_curl($url, $maxredirect = null) {
		$ch = curl_init();
		$mr = $maxredirect === null ? 5 : intval($maxredirect);
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		 
		if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) { 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0); 
			curl_setopt($ch, CURLOPT_MAXREDIRS, $mr); 
		} else { 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
			if ($mr > 0) { 
				$newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
	
				$rch = curl_copy_handle($ch); 
				curl_setopt($rch, CURLOPT_HEADER, true); 
				curl_setopt($rch, CURLOPT_NOBODY, true); 
				curl_setopt($rch, CURLOPT_FORBID_REUSE, false); 
				curl_setopt($rch, CURLOPT_RETURNTRANSFER, true); 
				do { 
					curl_setopt($rch, CURLOPT_URL, $newurl); 
					$header = curl_exec($rch); 
					if (curl_errno($rch)) { 
						$code = 0; 
					} else { 
						$code = curl_getinfo($rch, CURLINFO_HTTP_CODE); 
						if ($code == 301 || $code == 302) { 
							preg_match('/Location:(.*?)\n/', $header, $matches); 
							$newurl = trim(array_pop($matches)); 
						} else { 
							$code = 0; 
						} 
					} 
				} while ($code && --$mr); 
				curl_close($rch); 
				if (!$mr) { 
					if ($maxredirect === null) { 
						trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING); 
					} else { 
						$maxredirect = 0; 
					} 
					return false; 
				} 
				curl_setopt($ch, CURLOPT_URL, $newurl); 
			} 
		} 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4'))
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		
		$cont = curl_exec($ch);
		
		curl_close($ch);
		
		return $cont;
	}
}

//* Create the shortcode - outputs the number only, no fluff or flair
function fatpony_sharecount_shortcode_handler($atts, $content=null) {
	return fatpony_sharecount_execute();
}
add_shortcode('fatpony_sharecount', 'fatpony_sharecount_shortcode_handler');