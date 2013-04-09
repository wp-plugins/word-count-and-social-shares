<?php

Class myDatabase {
    
    private $limit;
    
    /*
     * Construct function
     */
    function __construct($limit) {
        $this->setData($limit);
    }
    
    /*
     *  set data
     */
    public function setData($limit) {
        $this->limit = $limit;
    }
    
    private function all_posts(){
        global $wpdb;
        $myrows = $wpdb->get_results( "SELECT ID, post_title,post_date,post_content FROM ".$wpdb->prefix."posts WHERE post_status = 'publish' AND post_type='post'");
        
        $data = array();
        $article = array();
        
        foreach($myrows as $one_article){
            
            // count words
            $content_text = explode(" ",$one_article->post_content);
            $words_number = count($content_text);
            
            // get url
            $final_url = get_permalink( $one_article->ID );
            
            $data['post_id'] = $one_article->ID;
            $data['post_title'] = $one_article->post_title;
            $data['post_date'] = $one_article->post_date;
            $data['post_word_count'] = $words_number;
            $data['post_url'] = $final_url;
            $article[] = $data;
            
        }
        
        return $article;
        
    }
    
    // update article list
    public function update_article_list(){
        
        global $wpdb;
        
        $all_articles = $this->all_posts();
        
        foreach($all_articles as $one_article){
            if($this->double_post($one_article['post_id'])==0){
                // insert post
                $wpdb->insert($wpdb->prefix . 'social_shares_report', array(
                        'post_url' => $one_article['post_url'],
                        'post_id' => $one_article['post_id'],
                        'post_title' => $one_article['post_title'],
                        'post_date' => $one_article['post_date'],
                        'post_word_count' => $one_article['post_word_count'] ), array('%s', '%d', '%s', '%s', '%d'));
            }
        }
        return true;
    }
    
    // get 50 article by date
    private function get_50_articles($from,$to){
        global $wpdb;
        if($from == null AND $to == null){
            $myrows = $wpdb->get_results( "SELECT id,post_url,check_date,post_date FROM ".$wpdb->prefix."social_shares_report WHERE check_date='' ORDER by id DESC LIMIT 50");
        }
        else {
            $myrows = $wpdb->get_results( "SELECT id,post_url,check_date,post_date FROM ".$wpdb->prefix."social_shares_report WHERE post_date > '".$from."' AND post_date < '".$to."' ORDER by check_date ASC LIMIT 50");
        }
        $data = array();
        $article = array();
        foreach($myrows as $one_article){
            
            $data['article_id'] = $one_article->id;
            $data['post_url'] = $one_article->post_url;
            $data['check_date'] = $one_article->check_date;
            $data['post_date'] = $one_article->post_date;
            $article[] = $data;
            
        }
        return $article;
        
    }
    // update data
    public function update_social_data($google_plus_api,$from=null,$to=null){
        
        $articles = $this->get_50_articles($from,$to);
        
        foreach($articles as $one_article){
            
            // get social media data
            $social = new Social($one_article['post_url']);
            
            $facebook_shares = $social->facebook_shares();
            $tweets = (int)$social->tweets();
            $linkedin_shares = (int)$social->linkedin_shares();
            $google_pluses = (int)$social->google_pluses($google_plus_api);
            
            $total_shares = $facebook_shares['shares']+$facebook_shares['comments']+$facebook_shares['like']+$tweets+$linkedin_shares+$google_pluses;
            $nowis = strtotime(date("d-m-Y H:m:s"));
            
            echo $one_article['article_id'].'---'.$one_article['post_url'].'---'.$one_article['post_date'].'<br>';
            
            global $wpdb;
            $wpdb->query("UPDATE ".$wpdb->prefix."social_shares_report SET 
                     fb_comments = '".$facebook_shares['comments']."',
                     fb_likes = '".$facebook_shares['like']."',
                     fb_shares = '".$facebook_shares['shares']."',
                     tweets = '".$tweets."',
                     linkedin_shares = '".$linkedin_shares."',
                     google_pluses = '".$google_pluses."',
                     total_shares = '".$total_shares."',
                     check_date = '".$nowis."' WHERE id = '".$one_article['article_id']."'");
            
            unset($social);

        }
        
        
        
        return true;
    }
    
    
    // check for double ID in social_shares_report table
    private function double_post($id){
        global $wpdb;
        $number = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."social_shares_report WHERE post_id = '" . $id . "'");
        return $number;
    }
    
    
}
?>
