<?php
/**
 * Novutec Domain Tools
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   Novutec
 * @package    DomainParser
 * @copyright  Copyright (c) 2007 - 2013 Novutec Inc. (http://www.novutec.com)
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * @namespace Novutec\WhoisParser
 */
namespace Novutec\WhoisParser;

/**
 * Template for IANA #120
 *
 * @category   Novutec
 * @package    WhoisParser
 * @copyright  Copyright (c) 2007 - 2013 Novutec Inc. (http://www.novutec.com)
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class Template_Gtld_xinnet extends AbstractTemplate
{

    /**
	 * Blocks within the raw output of the whois
	 * 
	 * @var array
	 * @access protected
	 */
    protected $blocks = array(1 => '/Registrant:(.*?)(?=Administrative Contact)/is', 
            2 => '/Administrative Contact:(.*?)(?=Technical Contact)/is', 
            3 => '/Technical Contact:(?>[\x20\t]*)(.*?)(?=Billing Contact)/is', 
            4 => '/Billing Contact:(?>[\x20\t]*)(.*?)$/is');

    /**
	 * Items for each block
	 * 
	 * @var array
	 * @access protected
	 */
    protected $blockItems = array(
            1 => array(
                    '/^(?>[\x20\t]*)Organization(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:organization', 
                    '/^(?>[\x20\t]*)Name(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:name', 
                    '/^(?>[\x20\t]*)Province\/State(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:state', 
                    '/^(?>[\x20\t]*)Address(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:address', 
                    '/^(?>[\x20\t]*)City(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:city', 
                    '/^(?>[\x20\t]*)Postal Code(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:zipcode', 
                    '/^(?>[\x20\t]*)Country(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:country', 
                    '/^(?>[\x20\t]*)Phone Number(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:phone', 
                    '/^(?>[\x20\t]*)Fax(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:fax', 
                    '/^(?>[\x20\t]*)Email(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:owner:email'), 
            2 => array(
                    '/^(?>[\x20\t]*)Organization(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:organization', 
                    '/^(?>[\x20\t]*)Name(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:name', 
                    '/^(?>[\x20\t]*)Province\/State(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:state', 
                    '/^(?>[\x20\t]*)Address(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:address', 
                    '/^(?>[\x20\t]*)City(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:city', 
                    '/^(?>[\x20\t]*)Postal Code(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:zipcode', 
                    '/^(?>[\x20\t]*)Country(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:country', 
                    '/^(?>[\x20\t]*)Phone Number(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:phone', 
                    '/^(?>[\x20\t]*)Fax(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:fax', 
                    '/^(?>[\x20\t]*)Email(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:admin:email'), 
            3 => array(
                    '/^(?>[\x20\t]*)Organization(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:organization', 
                    '/^(?>[\x20\t]*)Name(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:name', 
                    '/^(?>[\x20\t]*)Province\/State(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:state', 
                    '/^(?>[\x20\t]*)Address(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:address', 
                    '/^(?>[\x20\t]*)City(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:city', 
                    '/^(?>[\x20\t]*)Postal Code(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:zipcode', 
                    '/^(?>[\x20\t]*)Country(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:country', 
                    '/^(?>[\x20\t]*)Phone Number(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:phone', 
                    '/^(?>[\x20\t]*)Fax(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:fax', 
                    '/^(?>[\x20\t]*)Email(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:tech:email'), 
            4 => array(
                    '/^(?>[\x20\t]*)Organization(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:organization', 
                    '/^(?>[\x20\t]*)Name(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:name', 
                    '/^(?>[\x20\t]*)Province\/State(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:state', 
                    '/^(?>[\x20\t]*)Address(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:address', 
                    '/^(?>[\x20\t]*)City(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:city', 
                    '/^(?>[\x20\t]*)Postal Code(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:zipcode', 
                    '/^(?>[\x20\t]*)Country(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:country', 
                    '/^(?>[\x20\t]*)Phone Number(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:phone', 
                    '/^(?>[\x20\t]*)Fax(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:fax', 
                    '/^(?>[\x20\t]*)Email(?>[\x20\t]*):(?>[\x20\t]*)(.+)$/im' => 'contacts:billing:email'));
}