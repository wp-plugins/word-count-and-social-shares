<?php
set_time_limit(0);
class Social {
    
    private $link;
    
    /*
     * Construct function
     */
    function __construct($link) {
        $this->setData($link);
    }
    
    /*
     *  set data
     */
    public function setData($link) {
        $this->link = $link;
    }
    
    /*
     * Curl get data
     */
    private function get_https_data($fullurl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_URL, $fullurl);
        $returned = curl_exec($ch);

        return $returned;
    }
    
    
    /*
     * Get number of facebook shares
     */
    public function facebook_shares(){
        
        $fullurl = "http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls=".urlencode($this->link);
        $content = $this->get_https_data($fullurl);
        $fb_object = json_decode($content);
        
        
        $data = array();
        
        $data['like'] = $fb_object[0]->like_count;
        $data['shares'] = $fb_object[0]->share_count;
        $data['comments'] = $fb_object[0]->comment_count;

        return $data;
    }
    
    /*
     * Get number of tweets
     */
    public function tweets(){
        
        $fullurl = "http://urls.api.twitter.com/1/urls/count.json?url=".urlencode($this->link);
        $content = $this->get_https_data($fullurl);
        $twitter_object = json_decode($content);
        
        return $twitter_object->count;
        
    } 
    
    /*
     * LinkedIN shares
     */
    public function linkedin_shares(){
        
        $fullurl = "http://www.linkedin.com/countserv/count/share?format=json&url=".urlencode($this->link);
        $content = $this->get_https_data($fullurl);
        $linkedin_object = json_decode($content);
        
        return $linkedin_object->count;
        
    }
    
    /*
     * Google pluses
     */
    public function google_pluses($apikey) {
        
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => array('Content-type: application/json'),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $this->link . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => "https://clients6.google.com/rpc?key=$apikey"
        ));
        $res = curl_exec($ch);
        curl_close($ch);

        if ($res) {
            $json = json_decode($res, true);
            return $json[0]['result']['metadata']['globalCounts']['count'];
        }
        return false;
    }
    
} // class end


?>
