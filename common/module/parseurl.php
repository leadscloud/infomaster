<?php

/**
 * parseURL class
 *
 * Very simple and basic class for parsing a url. outputs url protocol, host, path and the query string.
 *
 * @version 	0.2
 * @author 		Christian Weber <christian@cw-internetdienste.de>
 * @link		http://www.cw-internetdienste.de
 *
 *  freely distributable under the MIT Licence
 *
 */

class parseURL {
	private		$regex	=	'\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))';

	protected 	$url;
	/**
     * @var PublicSuffixList Public Suffix List
     */
    protected $publicSuffixList;
	//we use get method to get the values of properties
	private 	$protocol;
	private 	$host;
	private 	$path;
	private		$query;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $url
	 * @return void
	 */
	public function __construct($url) {
		//
		preg_match('#^https?://#i', $url, $schemeMatches);
		if (empty($schemeMatches)) {
            $url = 'http://' . $url;
        }

		if(!isset($url)	||	empty($url)	||	trim($url) == ''	||	!is_string($url)	||	!$this->check($url)) {	return false; }

		$this->url	=	$url;
		$this->parseURL();
	}

	/**
	 * check function.
	 *
	 * @access private
	 * @param mixed $url
	 * @return void
	 */
	private function check($url) {
		return 	preg_match('@\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))@',$url);
	}

	/**
	 * parseURL function.
	 *
	 * @access private
	 * @return void
	 */
	private function parseURL() {

		$data	=	parse_url($this->url);


		$this->protocol	=	$data['scheme'];
		$this->host		=	strtolower($data['host']);

		$path			=	(isset($data['path']))	?	$data['path']:'';
		$this->path		=	$this->parsePath($path);

		$query			=	(isset($data['query'])) ? $data['query']:'';
		$this->query	=	$this->parseQuery($query);
	}

	/**
	 * parsePath function.
	 *
	 * @access private
	 * @param mixed $path
	 * @return void
	 */
	private function parsePath($path) {
		$_path	=	array();

		if(!empty($path)) {
			if(substr($path,0,1) 	=== 	'/') 	{	$path	=	substr($path,1);	}
			if(substr($path,-1)		===		'/')	{	$path	=	substr($path,0,-1);	}

			$_path	=	explode('/',$path);
		}

		return $_path;
	}

	/**
	 * parseQuery function.
	 *
	 * @access private
	 * @param mixed $query
	 * @return void
	 */
	private function parseQuery($query) {
		$_query			=	array();

		if(!empty($query)) {
			$_q	=	explode('&',$query);

			foreach($_q as $item) {
				$_item	=	explode('=',$item);
				$_query[$_item[0]]	=	(isset($_item[1])) ? $_item[1]:true;
			}

		}

		return $_query;
	}

	/**
	 * get function.
	 *
	 * @access public
	 * @param mixed $type
	 * @return void
	 */
	public function get($type) {
		switch(strtolower($type)) {
			case 'url':			return $this->get_url();
								break;
			case 'protocol':	return $this->get_protocol();
								break;
			case 'host':		return $this->get_host();
								break;
			case 'path':		return $this->get_path();
								break;
			default:			return false;
								break;
		}
	}

	/**
     * Returns the public suffix portion of provided host
     *
     * @param  string $host host
     * @return string public suffix
     */
    public function getPublicSuffix()
    {
    	$list = include COM_PATH.'/system/public-suffix-list.php';
		$this->publicSuffixList = $list;

    	$host = $this->host;

        if (strpos($host, '.') === 0) {
            return null;
        }

        $host = strtolower($host);
        $parts = array_reverse(explode('.', $host));
        $publicSuffix = array();
        $publicSuffixList = $this->publicSuffixList;

        foreach ($parts as $part) {
            if (array_key_exists($part, $publicSuffixList)
                && array_key_exists('!', $publicSuffixList[$part])) {
                break;
            }

            if (array_key_exists($part, $publicSuffixList)) {
                array_unshift($publicSuffix, $part);
                $publicSuffixList = $publicSuffixList[$part];
                continue;
            }

            if (array_key_exists('*', $publicSuffixList)) {
                array_unshift($publicSuffix, $part);
                $publicSuffixList = $publicSuffixList['*'];
                continue;
            }

            // Added by @goodhabit in https://github.com/jeremykendall/php-domain-parser/pull/15
            // Resolves https://github.com/jeremykendall/php-domain-parser/issues/16
            break;
        }

        // Apply algorithm rule #2: If no rules match, the prevailing rule is "*".
        if (empty($publicSuffix)) {
            $publicSuffix[0] = $parts[0];
        }

        return implode('.', array_filter($publicSuffix, 'strlen'));
    }

	 /**
     * Returns registerable domain portion of provided host
     *
     * Per the test cases provided by Mozilla
     * (http://mxr.mozilla.org/mozilla-central/source/netwerk/test/unit/data/test_psl.txt?raw=1),
     * this method should return null if the domain provided is a public suffix.
     *
     * @param  string $host host
     * @return string registerable domain
     */
    public function getRegisterableDomain()
    {
    	$host = $this->host;

        if (strpos($host, '.') === false) {
            return null;
        }

        $host = strtolower($host);
        $publicSuffix = $this->getPublicSuffix($host);

        if ($publicSuffix === null || $host == $publicSuffix) {
            return null;
        }

        $publicSuffixParts = array_reverse(explode('.', $publicSuffix));
        $hostParts = array_reverse(explode('.', $host));
        $registerableDomainParts = array_slice($hostParts, 0, count($publicSuffixParts) + 1);

        return implode('.', array_reverse($registerableDomainParts));
    }
    /**
     * Returns the subdomain portion of provided host
     *
     * @param  string $host host
     * @return string subdomain
     */
    public function getSubdomain()
    {
    	$host = $this->host;
        $host = strtolower($host);
        $registerableDomain = $this->getRegisterableDomain($host);

        if ($registerableDomain === null || $host == $registerableDomain) {
            return null;
        }

        $registerableDomainParts = array_reverse(explode('.', $registerableDomain));
        $hostParts = array_reverse(explode('.', $host));
        $subdomainParts = array_slice($hostParts, count($registerableDomainParts));

        return implode('.', array_reverse($subdomainParts));
    }

	/**
	 * get_url function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_url(){
    	return $this->url;
	}

	/**
	 * get_protocol function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_protocol(){
	    return $this->protocol;
	}

	/**
	 * get_host function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_host(){
	    return $this->host;
	}

	/**
	 * get_path function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_path(){
	    return $this->path;
	}

}
?>
