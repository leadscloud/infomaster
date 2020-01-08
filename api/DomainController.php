<?php
include dirname(__FILE__).'/../admin/admin.php';
require_once "spyc.php";

function get_domain_owner($host, $type='网站所属人', $result=null) {
    //开始判断
    $rules = rule_gets($type,'enabled');
    if(!empty($rules)){
        foreach($rules as $rule){
            $is_true = true;
            foreach($rule['pattern'] as $pattern) {
                $pattern = strtolower($pattern);
                if (@preg_match($pattern, $host)==false) {
                    $is_true = false;
                    break;
                }
            }
            $result = $is_true?$rule['result']:$result;
        }
    }
    //如果没有，则查询域名列表domain.php
    // if(!$result){
    //     $domain_list = domain_get_list();
    //     $uri = new parseURL($host);
    //     $registerableDomain = $uri->getRegisterableDomain();

    //     foreach ($domain_list as $author => $domainArray) {
    //         foreach($domainArray as $domain){
    //             if(trim($registerableDomain)==trim($domain)) {
    //                 return  $author;
    //             }
    //         }
    //     }
    // }
    if(!$result){
        $uri = new parseURL($host);
        $db = get_conn();
        $registerableDomain = $uri->getRegisterableDomain();
        $result = $db->result("SELECT `author` FROM `#@_domain` WHERE `status` = 'approved' AND
            domain='{$registerableDomain}'");
    }
    return $result;
}

function get_top_domain($host){
    $uri = new parseURL($host);
    $registerableDomain = $uri->getRegisterableDomain();
    if($registerableDomain){
        return $registerableDomain;
    }
    return $host;
}
class DomainController
{
    /**
     * Returns a JSON string object to the browser when hitting the root of the domain
     *
     * @url GET /
     */
    public function test()
    {
        return "Hello World";
    }
    /**
     * Logs in a user with the given username and password POSTed. Though true
     * REST doesn't believe in sessions, it is often desirable for an AJAX server.
     *
     * @url POST /login
     */
    public function login()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        return array("success" => "Logged in " . $username);
    }
    /**
     * Gets the user by id or current user
     *
     * @url GET /owners/$hostname
     */
    public function getOwner($hostname = null)
    {
        // if ($id) {
        //     $user = User::load($id); // possible user loading method
        // } else {
        //     $user = $_SESSION['user'];
        // }
        $owner = null;
        $domain = get_top_domain($hostname);
        $ip = get_domain_ip($hostname);
        $type = null;
        $name_code = "default";
        $color = null;

        $domain_list = Spyc::YAMLLoad('domain.yaml');
        foreach($domain_list as $name => $hosts){
            foreach ($hosts as $top_domain) {
                if($domain == $top_domain){
                    $owner = $name;
                    $type = "同行业";
                    $name_code = "goal";
                    $color = "#f00";
                    break;
                }
            }
        }

        if(empty($owner)){
            $owner = get_domain_owner($hostname);
            if($owner){
                $type = "本公司网站";
                $name_code = "language";
            }

        }
        return array(
            "status" => 'succeed',
            "name" => $owner,
            "hostname" => $hostname,
            "domain" => $domain,
            "ip" => $ip,
            "type" => $type,
            "location" => null,
            "name_code" => $name_code,
            "color" => $color
        );
    }

    /**
     * Saves a user to the database
     *
     * @url POST /users
     * @url PUT /users/$id
     */
    public function saveUser($id = null, $data)
    {
        // ... validate $data properties such as $data->username, $data->firstName, etc.
        // $data->id = $id;
        // $user = User::saveUser($data); // saving the user to the database
        $user = array("id" => $id, "name" => null);
        return $user; // returning the updated or newly created user object
    }
}