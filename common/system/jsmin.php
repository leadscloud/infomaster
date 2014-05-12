<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * PHP implementation of Douglas Crockford's JSMin.
 *
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.1.1 (2008-03-02)
 * @link https://github.com/rgrove/jsmin-php/
 */
define('ORD_LF',    10);
define('ORD_SPACE', 32);

class JSMin {
    var $a           = '';
    var $b           = '';
    var $input       = '';
    var $inputIndex  = 0;
    var $inputLength = 0;
    var $lookAhead   = null;
    var $output      = '';

    function minify($js) {
        $jsmin = new JSMin($js);
        return $jsmin->min();
    }

    function JSMin() {
        $args = func_get_args();
		call_user_func_array( array(&$this, '__construct'), $args );
	}

    function __construct($input) {
        $this->input = str_replace("\r\n", "\n", $input);
        $this->inputLength = strlen($this->input);
    }

    function action($d) {
        switch ($d) {
            case 1:
                $this->output .= $this->a;

            case 2:
                $this->a = $this->b;

                if ($this->a === "'" || $this->a === '"') {
                    for (; ;) {
                        $this->output .= $this->a;
                        $this->a = $this->get();

                        if ($this->a === $this->b) {
                            break;
                        }

                        if (ord($this->a) <= ORD_LF) {
                            return throw_error('Unterminated string literal.', E_SYS_WARNING);
                        }

                        if ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a = $this->get();
                        }
                    }
                }

            case 3:
                $this->b = $this->next();

                if ($this->b === '/' && (
                        $this->a === '(' || $this->a === ',' || $this->a === '=' ||
                                $this->a === ':' || $this->a === '[' || $this->a === '!' ||
                                $this->a === '&' || $this->a === '|' || $this->a === '?')) {

                    $this->output .= $this->a . $this->b;

                    for (; ;) {
                        $this->a = $this->get();

                        if ($this->a === '/') {
                            break;
                        } elseif ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a = $this->get();
                        } elseif (ord($this->a) <= ORD_LF) {
                            return throw_error('Unterminated regular expression literal.', E_SYS_WARNING);
                        }

                        $this->output .= $this->a;
                    }

                    $this->b = $this->next();
                }
        }
    }

    function get() {
        $c = $this->lookAhead;
        $this->lookAhead = null;

        if ($c === null) {
            if ($this->inputIndex < $this->inputLength) {
                $c = substr($this->input, $this->inputIndex, 1);
                $this->inputIndex += 1;
            } else {
                $c = null;
            }
        }

        if ($c === "\r") {
            return "\n";
        }

        if ($c === null || $c === "\n" || ord($c) >= ORD_SPACE) {
            return $c;
        }

        return ' ';
    }

    function isAlphaNum($c) {
        return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
    }

    function min() {
        $this->a = "\n";
        $this->action(3);

        while ($this->a !== null) {
            switch ($this->a) {
                case ' ':
                    if ($this->isAlphaNum($this->b)) {
                        $this->action(1);
                    } else {
                        $this->action(2);
                    }
                    break;

                case "\n":
                    switch ($this->b) {
                        case '{':
                        case '[':
                        case '(':
                        case '+':
                        case '-':
                            $this->action(1);
                            break;

                        case ' ':
                            $this->action(3);
                            break;

                        default:
                            if ($this->isAlphaNum($this->b)) {
                                $this->action(1);
                            }
                            else {
                                $this->action(2);
                            }
                    }
                    break;

                default:
                    switch ($this->b) {
                        case ' ':
                            if ($this->isAlphaNum($this->a)) {
                                $this->action(1);
                                break;
                            }

                            $this->action(3);
                            break;

                        case "\n":
                            switch ($this->a) {
                                case '}':
                                case ']':
                                case ')':
                                case '+':
                                case '-':
                                case '"':
                                case "'":
                                    $this->action(1);
                                    break;

                                default:
                                    if ($this->isAlphaNum($this->a)) {
                                        $this->action(1);
                                    }
                                    else {
                                        $this->action(3);
                                    }
                            }
                            break;

                        default:
                            $this->action(1);
                            break;
                    }
            }
        }

        return ltrim($this->output);
    }

    function next() {
        $c = $this->get();

        if ($c === '/') {
            switch ($this->peek()) {
                case '/':
                    for (; ;) {
                        $c = $this->get();

                        if (ord($c) <= ORD_LF) {
                            return $c;
                        }
                    }

                case '*':
                    $this->get();

                    for (; ;) {
                        switch ($this->get()) {
                            case '*':
                                if ($this->peek() === '/') {
                                    $this->get();
                                    return ' ';
                                }
                                break;

                            case null:
                                return throw_error('Unterminated comment.', E_SYS_WARNING);
                        }
                    }

                default:
                    return $c;
            }
        }

        return $c;
    }

    function peek() {
        $this->lookAhead = $this->get();
        return $this->lookAhead;
    }
}