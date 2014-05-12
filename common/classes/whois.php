<?php
/**
 * whois.php, version 1.0
 * 
 * Date: 2013/9/6
 */
class whois
{
    private $domain = array();
    private $error = null;
    private $whois_servers = array();
    private $server_save_path = '';
    private $get_contents;
    function __construct ($save_path, $domain = null)
    {
        $this->server_save_path = $save_path;
        $this->retrieve_whois_list();
        if (! is_null($domain))
        {
            $this->set_domain($domain);
        }
    }
    private function reset_domain ()
    {
        $this->domain = array('name' => '' , 'whois_data' => '' , 'ext' => '' , 'exp' => '' , 'creation' => '' , 'age' => '');
    }
    private function retrieve_whois_list ()
    {
        $whois_file = $this->server_save_path . 'whois_server_list.xml';
        if (is_file($whois_file) && filesize($whois_file) > 0)
        {
            $xml = file_get_contents($this->server_save_path . 'whois_server_list.xml');
            foreach (new SimpleXMLIterator($xml) as $server)
            {
                $this->whois_servers[(string) $server->extension] = array('server' => (string) $server->server , 'match' => (string) $server->match_text , 'reg' => (string) $server->creation_date_regex , 'reg_format' => (string) $server->creation_date_format , 'exp' => (string) $server->expiry_date_regex , 'exp_format' => (string) $server->expiry_date_format);
            }
        }
    }
    private function is_valid ($domain)
    {
        if (preg_match('/^[-a-z0-9]+(\.[a-z]+)?\.[a-z]+$/i', $domain) === 0)
        {
            throw new Exception('你输入的域名似乎并不是有效的');//The domain entered does not seem to be valid
        } else
        {
            return true;
        }
    }
    public function set_domain ($domain)
    {
        $this->reset_domain();
        if ($this->is_valid($domain))
        {
            $this->split_domain($domain);
        }
        $this->run_whois();
    }
    private function run_whois ()
    {
        $errno = '';
        $errstr = '';
        $fp = fsockopen($this->get_whois_server($this->domain['ext']), 43, $errno, $errstr, 30);
        if (! $fp)
        {
            echo "$errstr ($errno)<br />\n";
        } else
        {
            $out = $this->domain['name'] . $this->domain['ext'] . "\r\n";
            fwrite($fp, $out);
            while (! feof($fp))
            {
                $this->domain['whois_data'] .= fgets($fp, 128);
            }
            fclose($fp);
        }
    }
    private function split_domain ($domain)
    {
        if ($this->is_valid($domain))
        {
            $domain_name = explode('.', $domain);
            $ext_index = count($domain_name);
            $this->domain['ext'] = '.' . $domain_name[$ext_index - 1];
            unset($domain_name[$ext_index - 1]);
            $this->domain['name'] = implode('.', $domain_name);
        }
    }
    public function get_raw_date(){
        return $this->domain;
    }
    public function get_expiry ()
    {
        if (empty($this->domain['exp']))
        {
            $exp_regexp = $this->whois_servers[$this->domain['ext']]['exp'];
            if (! empty($exp_regexp) && ! is_null($this->domain['exp']))
            {
                $matches = '';
                preg_match('/' . $exp_regexp . '/', $this->domain['whois_data'], $matches);
                if(count($matches) > 0)
                {
                    $format = whois_date_format($this->whois_servers[$this->domain['ext']]['exp_format']);
                    $year = $matches[$format['y']];
                    $month = $matches[$format['m']];
                    $day = $matches[$format['d']];
                    $exp = strtotime($day . '-' . $month . '-' . $year);
                    $this->domain['exp'] = $exp;
                } else
                {
                    $this->domain['exp'] = 'NULL';
                }
                return $this->domain['exp'];
            } else
            {
                $this->domain['exp'] = 'NULL';
                return $this->domain['exp'];
            }
        } else
        {
            return $this->domain['exp'];
        }
    }
    public function get_creation ()
    {
        if (empty($this->domain['creation']))
        {
            $reg_regexp = $this->whois_servers[$this->domain['ext']]['reg'];
            if (! empty($reg_regexp) && ! is_null($this->domain['creation']))
            {
                $matches = '';
                preg_match('/' . $reg_regexp . '/', $this->domain['whois_data'], $matches);
                if(count($matches) > 0)
                {
                    $format = whois_date_format($this->whois_servers[$this->domain['ext']]['reg_format']);
                    $year = $matches[$format['y']];
                    $month = $matches[$format['m']];
                    $day = $matches[$format['d']];
                    $creation = strtotime($day . '-' . $month . '-' . $year);
                    $this->domain['creation'] = $creation;
                } else
                {
                    $this->domain['creation'] = 'NULL';
                }
                return $this->domain['creation'];
            } else
            {
                $this->domain['creation'] = 'NULL';
                return $this->domain['creation'];
            }
        } else
        {
            return $this->domain['creation'];
        }
    }
    public function get_age ()
    {
        if (empty($this->domain['age']) && ! is_null($this->domain['age']))
        {
            $creation = $this->get_creation();
            $age = null;
            if (! is_null($creation))
            {
                $age = time() - $creation;
            }
            $this->domain['age'] = $age;
            return $age;
        } else
        {
            return $this->domain['age'];
        }
    }
    public function get_whois_data ()
    {
        return $this->domain['whois_data'];
    }
    public function get_whois_server ($ext = null)
    {
        if (array_key_exists($ext, $this->whois_servers))
        {
            return $this->whois_servers[$ext]['server'];
        } elseif (is_null($ext))
        {
            return $this->whois_servers[$this->domain['ext']]['server'];
        } else
        {
            throw new Exception('目前不支持此注册商。'); //The registry is currently not supported.
        }
    }
    public function get_supported_tlds ()
    {
        $tlds = array();
        foreach ($this->whois_servers as $key => $value)
        {
            $value;
            $tlds[] = $key;
        }
        return $tlds;
    }
    public function is_error ()
    {
        if (is_null($this->error))
        {
            return false;
        } else
        {
            return false;
        }
    }
    public function is_registered ()
    {
        $regged = stripos($this->domain['whois_data'], $this->whois_servers[$this->domain['ext']]['match']);
        if ($regged === false)
        {
            return false;
        } else
        {
            return true;
        }
    }
    public function update_whois_database ()
    {
        $whois_servers = file_get_contents('http://collectionmanagers.com/whois/whois_server_list.xml');
        file_put_contents($this->server_save_path . 'whois_server_list.xml', $whois_servers);
    }
}