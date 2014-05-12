<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * 语言包本地化类
 *
 * 修改自WordPress L10n类
 *
 */
class L10n {
    var $entries = array();
	var $headers = array();
    var $_nplurals,$_gettext_select_plural_form;
    /**
     * 向PO结构新增条目
     *
     * @param  $entry
     * @return bool true on success, false if the entry doesn't have a key
     */
	function add_entry($entry) {
		if (is_array($entry)) {
			$entry = $this->entry($entry);
		}
		$key = $this->entry_key($entry);
		if (false === $key) return false;
		$this->entries[$key] = &$entry;
		return true;
	}

    /**
     * headers 设置与读取函数
     *
     * @param  $header
     * @param  $value
     * @return array|bool
     */
    function headers($header=null, $value=null) {
		// 批量赋值
    	if (is_array($header)) {
    		foreach ($header as $k=>$v) {
                $this->headers[$k] = $v;
                $this->header_after($header);
    		}
            return true;
        }
        // 取值
        if ($header && func_num_args()==1) {
            return isset($this->headers[$header]) ? $this->headers[$header] : false;
        }
        // 单个赋值
        else {
            $this->headers[$header] = $value;
            $this->header_after($header);
            return $value;
        }

	}
    /**
     * 设置header之后执行
     *
     * @param  $header
     * @return void
     */
    function header_after($header) {
        if ('Plural-Forms' == $header) {
			list( $nplurals, $expression ) = $this->nplurals_and_expression_from_header($this->headers('Plural-Forms'));
			$this->_nplurals = $nplurals;
			$this->_gettext_select_plural_form = $this->make_plural_form_function($nplurals, $expression);
		}
    }
    /**
     * 翻译
     *
     * @param  $singular
     * @param  $context
     * @return string
     */
    function translate($singular, $context=null) {
		$entry = $this->entry(array('singular' => $singular, 'context' => $context));
		$translated = $this->translate_entry($entry);
        return ($translated && !empty($translated['translations']))? $translated['translations'][0] : $singular;
	}

    /**
	 * @param array $args associative array, support following keys:
	 * 	- singular (string) -- the string to translate, if omitted and empty entry will be created
	 * 	- plural (string) -- the plural form of the string, setting this will set {@link $is_plural} to true
	 * 	- translations (array) -- translations of the string and possibly -- its plural forms
	 * 	- context (string) -- a string differentiating two equal strings used in different contexts
	 * 	- translator_comments (string) -- comments left by translators
	 * 	- extracted_comments (string) -- comments left by developers
	 * 	- references (array) -- places in the code this strings is used, in relative_to_root_path/file.php:linenum form
	 * 	- flags (array) -- flags like php-format
	 */
	function entry($args=array()) {
		// if no singular -- empty object
		if (!isset($args['singular'])) {
			return;
		}
        $entry = array(
			'is_plural' => false,
			'context' => null,
			'singular' => null,
			'plural' => null,
			'translations' => array(),
			'translator_comments' => '',
			'extracted_comments' => '',
			'references' => array(),
			'flags' => array(),
		);
		// get member variable values from args hash
		foreach ($args as $varname => $value) {
			$entry[$varname] = $value;
		}
		if (isset($args['plural'])) $entry['is_plural'] = true;
		if (!is_array($entry['translations'])) $entry['translations'] = array();
		if (!is_array($entry['references'])) $entry['references'] = array();
		if (!is_array($entry['flags'])) $entry['flags'] = array();
        return $entry;
	}
    /**
	 * Generates a unique key for this entry
	 *
	 * @return string|bool the key or false if the entry is empty
	 */
    function entry_key($entry) {
		if (is_null($entry['singular'])) return false;
		// prepend context and EOT, like in MO files
		return (!isset($entry['context']) || is_null($entry['context'])) ? $entry['singular'] : $entry['context'].chr(4).$entry['singular'];
	}

    function translate_entry($entry) {
		$key = $this->entry_key($entry);
		return isset($this->entries[$key])? $this->entries[$key] : false;
	}

    /**
	 * Given the number of items, returns the 0-based index of the plural form to use
	 *
	 * Here, in the base Translations class, the commong logic for English is implmented:
	 * 	0 if there is one element, 1 otherwise
	 *
	 * This function should be overrided by the sub-classes. For example MO/PO can derive the logic
	 * from their headers.
	 *
	 * @param integer $count number of items
	 */
	function select_plural_form($count) {
		return $this->gettext_select_plural_form($count);
	}

    /**
	 * The gettext implmentation of select_plural_form.
	 *
	 * It lives in this class, because there are more than one descendand, which will use it and
	 * they can't share it effectively.
	 *
	 */
	function gettext_select_plural_form($count) {
		if (!isset($this->_gettext_select_plural_form) || is_null($this->_gettext_select_plural_form)) {
			list( $nplurals, $expression ) = $this->nplurals_and_expression_from_header($this->headers('Plural-Forms'));
			$this->_nplurals = $nplurals;
			$this->_gettext_select_plural_form = $this->make_plural_form_function($nplurals, $expression);
		}
		return call_user_func($this->_gettext_select_plural_form, $count);
	}

    function nplurals_and_expression_from_header($header) {
		if (preg_match('/^\s*nplurals\s*=\s*(\d+)\s*;\s+plural\s*=\s*(.+)$/', $header, $matches)) {
			$nplurals = (int)$matches[1];
			$expression = trim($this->parenthesize_plural_exression($matches[2]));
			return array($nplurals, $expression);
		} else {
			return array(2, 'n != 1');
		}
	}

	/**
	 * Makes a function, which will return the right translation index, according to the
	 * plural forms header
	 */
	function make_plural_form_function($nplurals, $expression) {
		$expression = str_replace('n', '$n', $expression);
		$func_body = "
			\$index = (int)($expression);
			return (\$index < $nplurals)? \$index : $nplurals - 1;";
		return create_function('$n', $func_body);
	}

	/**
	 * Adds parantheses to the inner parts of ternary operators in
	 * plural expressions, because PHP evaluates ternary oerators from left to right
	 *
	 * @param string $expression the expression without parentheses
	 * @return string the expression with parentheses added
	 */
	function parenthesize_plural_exression($expression) {
		$expression .= ';';
		$res = '';
		$depth = 0;
		for ($i = 0; $i < strlen($expression); ++$i) {
			$char = $expression[$i];
			switch ($char) {
				case '?':
					$res .= ' ? (';
					$depth++;
					break;
				case ':':
					$res .= ') : (';
					break;
				case ';':
					$res .= str_repeat(')', $depth) . ';';
					$depth= 0;
					break;
				default:
					$res .= $char;
			}
		}
		return rtrim($res, ';');
	}

	function get_plural_forms_count() {
		return $this->_nplurals;
	}

    function translate_plural($singular, $plural, $count, $context = null) {
		$entry = $this->entry(array('singular' => $singular, 'plural' => $plural, 'context' => $context));
		$translated = $this->translate_entry($entry);
		$index = $this->select_plural_form($count);
		$total_plural_forms = $this->get_plural_forms_count();
		if ($translated && 0 <= $index && $index < $total_plural_forms &&
				is_array($translated['translations']) &&
				isset($translated['translations'][$index]))
			return $translated['translations'][$index];
		else
			return 1 == $count? $singular : $plural;
	}

    function load_file($file){
        $ckey   = sprintf('l10n.%s', $file);
        $cfile  = fcache_file($ckey);
        $cmtime = is_file($cfile) ? filectime($cfile) : time();
        $fmtime = filemtime($file);
        // 缓存修改时间大于源文件修改时间
        if ($cmtime > $fmtime) {
            $result = fcache_get($ckey);
            if (fcache_not_null($result)) {
                $this->entries = $result;
                return true;
            }
        }
		$reader = new L10n_Reader($file);
        if (!$reader->is_resource())
			return false;
        $result = $this->load_mo($reader);
        fcache_set($ckey, $this->entries);
        return $result; 
	}

    function load_mo($reader) {
		$endian_string = $this->get_byteorder($reader->readint32());
		if (false === $endian_string) {
			return false;
		}
        $reader->set_endian($endian_string);

		$endian = ('big' == $endian_string)? 'N' : 'V';

		$header = $reader->read(24);
		if ($reader->strlen($header) != 24)
			return false;

		// parse header
		$header = unpack("{$endian}revision/{$endian}total/{$endian}originals_lenghts_addr/{$endian}translations_lenghts_addr/{$endian}hash_length/{$endian}hash_addr", $header);
		if (!is_array($header))
			return false;

		extract( $header );

		// support revision 0 of MO format specs, only
		if ($revision != 0)
			return false;

		// seek to data blocks
		$reader->seekto($originals_lenghts_addr);

		// read originals' indices
		$originals_lengths_length = $translations_lenghts_addr - $originals_lenghts_addr;
		if ( $originals_lengths_length != $total * 8 )
			return false;

		$originals = $reader->read($originals_lengths_length);
		if ( $reader->strlen($originals) != $originals_lengths_length )
			return false;

		// read translations' indices
		$translations_lenghts_length = $hash_addr - $translations_lenghts_addr;
		if ( $translations_lenghts_length != $total * 8 )
			return false;

		$translations = $reader->read($translations_lenghts_length);
		if ( $reader->strlen($translations) != $translations_lenghts_length )
			return false;

		// transform raw data into set of indices
		$originals    = $reader->str_split( $originals, 8 );
		$translations = $reader->str_split( $translations, 8 );

		// skip hash table
		$strings_addr = $hash_addr + $hash_length * 4;

		$reader->seekto($strings_addr);

		$strings = $reader->read_all();
		$reader->close();

		for ( $i = 0; $i < $total; $i++ ) {
			$o = unpack( "{$endian}length/{$endian}pos", $originals[$i] );
			$t = unpack( "{$endian}length/{$endian}pos", $translations[$i] );
			if ( !$o || !$t ) return false;

			// adjust offset due to reading strings to separate space before
			$o['pos'] -= $strings_addr;
			$t['pos'] -= $strings_addr;

			$original    = $reader->substr( $strings, $o['pos'], $o['length'] );
			$translation = $reader->substr( $strings, $t['pos'], $t['length'] );

			if ('' === $original) {
				$this->headers($this->make_headers($translation));
			} else {
				$entry = $this->make_entry($original, $translation);
				$this->entries[$this->entry_key($entry)] = $entry;
			}
		}

		return true;
	}

    function make_headers($translation) {
		$headers = array();
		// sometimes \ns are used instead of real new lines
		$translation = str_replace('\n', "\n", $translation);
		$lines = explode("\n", $translation);
		foreach($lines as $line) {
			$parts = explode(':', $line, 2);
			if (!isset($parts[1])) continue;
			$headers[trim($parts[0])] = trim($parts[1]);
		}
		return $headers;
	}

    /**
	 * Build a Translation_Entry from original string and translation strings,
	 * found in a MO file
	 *
	 * @static
	 * @param string $original original string to translate from MO file. Might contain
	 * 	0x04 as context separator or 0x00 as singular/plural separator
	 * @param string $translation translation string from MO file. Might contain
	 * 	0x00 as a plural translations separator
	 */
	function make_entry($original, $translation) {
		$entry = array();
		// look for context
		$parts = explode(chr(4), $original);
		if (isset($parts[1])) {
			$original = $parts[1];
			$entry['context'] = $parts[0];
		}
		// look for plural original
		$parts = explode(chr(0), $original);
		$entry['singular'] = $parts[0];
		if (isset($parts[1])) {
			$entry['is_plural'] = true;
			$entry['plural'] = $parts[1];
		}
		// plural translations are also separated by \0
		$entry['translations'] = explode(chr(0), $translation);
		return $entry;
	}

    /**
	 * Merge $other in the current object.
	 *
	 * @param Object &$other Another Translation object, whose translations will be merged in this one
	 * @return void
	 **/
	function merge_with(&$other) {
		foreach( (array)$other->entries as $entry ) {
			$this->entries[$this->entry_key($entry)] = $entry;
		}
	}

    function get_byteorder($magic) {
		// The magic is 0x950412de

		// bug in PHP 5.0.2, see https://savannah.nongnu.org/bugs/?func=detailitem&item_id=10565
		$magic_little = (int) - 1794895138;
		$magic_little_64 = (int) 2500072158;
		// 0xde120495
		$magic_big = ((int) - 569244523) & 0xFFFFFFFF;
		if ($magic_little == $magic || $magic_little_64 == $magic) {
			return 'little';
		} else if ($magic_big == $magic) {
			return 'big';
		} else {
			return false;
		}
	}

}

class L10n_Reader {
    var $_pos, $_f;
    var $endian = 'little';
	function L10n_Reader($file) {
		$this->_f   = fopen($file, 'r');
        $this->_pos = 0;
	}

    /**
	 * Sets the endianness of the file.
	 *
	 * @param $endian string 'big' or 'little'
	 */
	function set_endian($endian) {
		$this->endian = $endian;
	}

    /**
	 * Reads a 32bit Integer from the Stream
	 *
	 * @return mixed The integer, corresponding to the next 32 bits from
	 * 	the stream of false if there are not enough bytes or on error
	 */
	function readint32() {
		$bytes = $this->read(4);
		if (4 != $this->strlen($bytes))
			return false;
		$endian_letter = ('big' == $this->endian)? 'N' : 'V';
		$int = unpack($endian_letter, $bytes);
		return array_shift($int);
	}

	/**
	 * Reads an array of 32-bit Integers from the Stream
	 *
	 * @param integer count How many elements should be read
	 * @return mixed Array of integers or false if there isn't
	 * 	enough data or on error
	 */
	function readint32array($count) {
		$bytes = $this->read(4 * $count);
		if (4*$count != $this->strlen($bytes))
			return false;
		$endian_letter = ('big' == $this->endian)? 'N' : 'V';
		return unpack($endian_letter.$count, $bytes);
	}


	function substr($string, $start, $length) {
		return mb_substr($string, $start, $length, 'ascii');
	}

	function strlen($string) {
		return mb_strlen($string, 'ascii');
	}

	function str_split($string, $chunk_size) {
		if (!function_exists('str_split')) {
			$length = $this->strlen($string);
			$out = array();
			for ($i = 0; $i < $length; $i += $chunk_size)
				$out[] = $this->substr($string, $i, $chunk_size);
			return $out;
		} else {
			return str_split( $string, $chunk_size );
		}
	}


	function pos() {
		return $this->_pos;
	}

	function read($bytes) {
		return fread($this->_f, $bytes);
	}

	function seekto($pos) {
		if ( -1 == fseek($this->_f, $pos, SEEK_SET)) {
			return false;
		}
		$this->_pos = $pos;
		return true;
	}

	function is_resource() {
		return is_resource($this->_f);
	}

	function feof() {
		return feof($this->_f);
	}

	function close() {
		return fclose($this->_f);
	}

	function read_all() {
		$all = '';
		while ( !$this->feof() )
			$all .= $this->read(4096);
		return $all;
	}
}

class L10n_NOOP {
    var $entries;

    function translate($singular, $context=null) {
		return $singular;
	}
    function translate_plural($singular, $plural, $count, $context = null) {
		return 1 == $count? $singular : $plural;
	}
}

/**
 * 加载语言包
 *
 * @param string $domain
 * @param string $mo_dir
 * @return bool
 */
function load_textdomain($domain='default', $mo_dir=null) {
    global $l10n;
    
    $locale = language();
    if (null === $mo_dir) {
        $mo_dir = COM_PATH.'/locale';
    }

    $mofile = rtrim($mo_dir,'/')."/".$locale.".mo";

    if ( !is_readable( $mofile ) ) return false;

    $l10n_mo = new L10n();
    $l10n_mo->load_file($mofile);
    if ( isset( $l10n[$domain] ) ) {
        $l10n_mo->merge_with( $l10n[$domain] );
    }

	$l10n[$domain] = &$l10n_mo;

    return true;
}
/**
 * 返回L10n实例
 *
 * @param string $domain
 * @return object
 */
function &_get_l10n_object( $domain ) {
	global $l10n;
	if ( !isset( $l10n[$domain] ) ) {
        $l10n[$domain] = new L10n_NOOP();
	}
	return $l10n[$domain];
}
/**
 * 翻译函数
 *
 * @param string $text
 * @param string $domain
 * @return string
 */
function __($text, $domain = 'default'){
    $translations = &_get_l10n_object($domain);
    return $translations->translate($text);
}

/**
 * 输出字符串
 *
 * @param string $str
 */
function _e($text, $domain = 'default'){
    echo __($text, $domain);
}
/**
 * 读取翻译gettext的上下文字符串
 *
 * @param string $single Text to translate
 * @param string $context Context information for the translators
 * @param string $domain Optional. Domain to retrieve the translated text
 * @return string Translated context string without pipe
 */
function _x($single, $context, $domain = 'default') {
    $translations = &_get_l10n_object($domain);
    return $translations->translate($single, $context);
}
/**
 * Displays translated string with gettext context
 *
 * @param string $single
 * @param string $context
 * @param string $domain
 * @return void
 */
function _ex($single, $context, $domain = 'default') {
    echo _x($single, $context, $domain);
}
/**
 * Retrieve the plural or single form based on the amount.
 *
 * If the domain is not set in the $l10n list, then a comparison will be made
 * and either $plural or $single parameters returned.
 *
 * If the domain does exist, then the parameters $single, $plural, and $number
 * will first be passed to the domain's ngettext method. Then it will be passed
 * to the 'ngettext' filter hook along with the same parameters. The expected
 * type will be a string.
 *
 * @param string $single The text that will be used if $number is 1
 * @param string $plural The text that will be used if $number is not 1
 * @param int $number The number to compare against to use either $single or $plural
 * @param string $domain Optional. The domain identifier the text should be retrieved in
 * @return string Either $single or $plural translated text
 */
function _n( $single, $plural, $number, $domain = 'default' ) {
	$translations = &_get_l10n_object( $domain ); 
	return $translations->translate_plural( $single, $plural, $number );
}

/**
 * A hybrid of _n() and _x(). It supports contexts and plurals.
 *
 * @see _n()
 * @see _x()
 *
 */
function _nx($single, $plural, $number, $context, $domain = 'default') {
	$translations = &_get_l10n_object( $domain ); 
	return $translations->translate_plural( $single, $plural, $number, $context );
}
