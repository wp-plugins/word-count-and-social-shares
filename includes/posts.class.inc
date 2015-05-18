<?php
set_time_limit(0);
class myPosts{
    
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
    
    /*
     * Get all posts data from database
     */
    public function get_all_posts_from_db(){
        
        global $wpdb;
        $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."social_shares_report ORDER by post_date DESC");
        
        $data = array();
        $article = array();
        
        foreach($myrows as $one_article){
            
            $fbshares = array('shares' => $one_article->fb_shares, 'comments' => $one_article->fb_comments, 'like'=>$one_article->fb_likes);
            
            $data['title'] = $one_article->post_title;
            $data['id'] = $one_article->post_id;
            $data['date'] = $one_article->post_date;
            $data['word_count'] = $one_article->post_word_count;
            $data['facebook_shares'] = $fbshares;
            $data['tweets'] = $one_article->tweets;
            $data['linkedind_shares'] = $one_article->linkedin_shares;
            $data['google_pluses'] = $one_article->google_pluses;
            $data['article_url'] = $one_article->post_url;
            $data['total_shares'] = $one_article->total_shares;
            $article[] = $data;
            
        }
        
        return $article;
    }
    
    
    
    /*
     * Get post data
     */
    public function get_all_posts($from,$to,$site_url,$google_plus_api){
        
        global $wpdb;
        $myrows = $wpdb->get_results( "SELECT ID, post_title,post_date,post_content FROM ".$wpdb->prefix."posts WHERE post_date > '".$from."' AND post_date < '".$to."' AND  post_status = 'publish' AND post_type='post'");
        
        $data = array();
        $article = array();
        
        foreach($myrows as $one_article){
            
            // count words
            $content_text = explode(" ",$one_article->post_content);
            $words_number = count($content_text);
            
            // get url
            $final_url = get_permalink( $one_article->ID );
            
            // get social media data
            $social = new Social($final_url);
            $facebook_shares = $social->facebook_shares();
            $tweets = (int)$social->tweets();
            $linkedin_shares = (int)$social->linkedin_shares();
            $google_pluses = (int)$social->google_pluses($google_plus_api);
            
            $total_shares = $facebook_shares['shares']+$facebook_shares['comments']+$facebook_shares['like']+$tweets+$linkedin_shares+$google_pluses;
            
            
            $data['title'] = $one_article->post_title;
            $data['id'] = $one_article->ID;
            $data['date'] = $one_article->post_date;
            $data['word_count'] = $words_number;
            $data['facebook_shares'] = $facebook_shares;
            $data['tweets'] = $tweets;
            $data['linkedind_shares'] = $linkedin_shares;
            $data['google_pluses'] = $google_pluses;
            $data['article_url'] = $final_url;
            $data['total_shares'] = $total_shares;
            $article[] = $data;
            
            unset($social);
        }
        
        return $article;
    }
    
    
    public function get_all_posts_full_time($site_url,$google_plus_api){
        
        global $wpdb;
        $myrows = $wpdb->get_results( "SELECT ID, post_title,post_date,post_content FROM ".$wpdb->prefix."posts WHERE  post_status = 'publish' AND post_type='post'");
        
        $data = array();
        $article = array();
        
        foreach($myrows as $one_article){
            
            // count words
            $content_text = explode(" ",$one_article->post_content);
            $words_number = count($content_text);
            
            // get url
            $final_url = get_permalink( $one_article->ID );
            
            // get social media data
            $social = new Social($final_url);
            $facebook_shares = $social->facebook_shares();
            $tweets = (int)$social->tweets();
            $linkedin_shares = (int)$social->linkedin_shares();
            $google_pluses = (int)$social->google_pluses($google_plus_api);
            
            $total_shares = $facebook_shares['shares']+$facebook_shares['comments']+$facebook_shares['like']+$tweets+$linkedin_shares+$google_pluses;
            
            
            $data['title'] = $one_article->post_title;
            $data['id'] = $one_article->ID;
            $data['date'] = $one_article->post_date;
            $data['word_count'] = $words_number;
            $data['facebook_shares'] = $facebook_shares;
            $data['tweets'] = $tweets;
            $data['linkedind_shares'] = $linkedin_shares;
            $data['google_pluses'] = $google_pluses;
            $data['article_url'] = $final_url;
            $data['total_shares'] = $total_shares;
            $article[] = $data;
            
            unset($social);
        }
        
        return $article;
    }
    
    
    
    // save data to csv file
    public function SaveCSV($all_posts,$path,$csv_key){
        
        
        $data_arr = array();
        $return_arr = array();
        $string = "Article Title\tArticle URL\tDate\tNumber of words\tFacebook Likes\tFacebook Shares\tFacebook Comments\tTweets\tLinkedin shares\tGoogle Pluses\tTotal Shares\n";
        
        foreach($all_posts as $data){
            $string = $string.$data['title']."\t".$data['article_url']."\t".$data['date']."\t".$data['word_count']."\t".$data['facebook_shares']['like']."\t".$data['facebook_shares']['shares']."\t".$data['facebook_shares']['comments']."\t".$data['tweets']."\t".$data['linkedind_shares']."\t".$data['google_pluses']."\t".$data['total_shares']."\n";
        }
        
        $full_path = $path.'csv_export-'.$csv_key.'.csv';
        
        $save = file_put_contents($full_path, $string);
        if($save){
            return true;
        }
        else {
            return false;
        }
        
    }
    
    // sort and parse chart data
    private function clean_chart_data($total_chart_data){
        ksort($total_chart_data); // sort array by key
        $complete_array = "";
        // create javascript array
        foreach ($total_chart_data as $key => $value) {
            $complete_array = "$complete_array ['$key',$value],";
        }
        // remove , and end of string
        $complete_array = substr($complete_array, 0, -1);
        return $complete_array;
    }
    
    
    // create arrays
    public function total_chart_data($all_posts){
        
        $chart_data_total = array();
        $chart_data_fb_likes = array();
        $chart_data_fb_shares = array();
        $chart_data_fb_comments = array();
        $chart_data_tweets = array();
        $chart_data_linkedind_shares = array();
        $chart_data_google_pluses = array();
        
        foreach($all_posts as $data){
            // get total shares
            $total = $data['facebook_shares']['like']+$data['facebook_shares']['shares']+$data['facebook_shares']['comments']+$data['tweets']+$data['linkedind_shares']+$data['google_pluses'];
            $chart_data_total[$data[word_count]] = $total.','.$data['facebook_shares']['like'].','.$data['facebook_shares']['shares'].','.$data['facebook_shares']['comments'].','.$data['tweets'].','.$data['linkedind_shares'].','.$data['google_pluses'];
            
            $chart_data_fb_likes[$data[word_count]] = $data['facebook_shares']['like'];
            $chart_data_fb_shares[$data[word_count]] = $data['facebook_shares']['shares'];
            $chart_data_fb_comments[$data[word_count]] = $data['facebook_shares']['comments'];
            $chart_data_tweets[$data[word_count]] = $data['tweets'];
            $chart_data_linkedind_shares[$data[word_count]] = $data['linkedind_shares'];
            $chart_data_google_pluses[$data[word_count]] = $data['google_pluses'];

            
        }
        
        $complete_chart_data_total = $this->clean_chart_data($chart_data_total);
        $complete_chart_data_fb_likes = $this->clean_chart_data($chart_data_fb_likes);
        $complete_chart_data_fb_shares = $this->clean_chart_data($chart_data_fb_shares);
        $complete_chart_data_fb_comments = $this->clean_chart_data($chart_data_fb_comments);
        $complete_chart_data_tweets = $this->clean_chart_data($chart_data_tweets);
        $complete_chart_data_linkedind_shares = $this->clean_chart_data($chart_data_linkedind_shares);
        $complete_chart_data_google_pluses = $this->clean_chart_data($chart_data_google_pluses);

        $complete_return = array();
        $complete_return['chart_total'] = $complete_chart_data_total;
        $complete_return['chart_fb_likes'] = $complete_chart_data_fb_likes;
        $complete_return['chart_fb_shares'] = $complete_chart_data_fb_shares;
        $complete_return['chart_fb_comments'] = $complete_chart_data_fb_comments;
        $complete_return['chart_tweets'] = $complete_chart_data_tweets;
        $complete_return['chart_linkedin'] = $complete_chart_data_linkedind_shares;
        $complete_return['chart_google_pluses'] = $complete_chart_data_google_pluses;

        

        return $complete_return;
    }
    
    // overview table data
    function overview($all_posts){
        

        $return_array = array();
        
        $i = 0;
        $total_pluses = 0;
        $total_fb_shares = 0;
        $total_fb_likes = 0;
        $total_fb_comments = 0;
        $total_linkedin = 0;
        $total_gpluses = 0;
        $total_tweets = 0;
        $total_words = 0;
        
        foreach($all_posts as $data){
            
            $total_pluses = $total_pluses+$data['google_pluses'];
            $total_fb_shares = $total_fb_shares+$data['facebook_shares']['shares'];
            $total_fb_likes = $total_fb_likes+$data['facebook_shares']['like'];
            $total_fb_comments = $total_fb_comments+$data['facebook_shares']['comments'];
            $total_linkedin = $total_linkedin+$data['linkedind_shares'];
            $total_tweets = $total_tweets+$data['tweets'];
            $total_words = $total_words+$data['word_count'];
            
            $i++;
        }
        // return array
        $return_array['total_pluses'] = $total_pluses;
        $return_array['total_fb_shares'] = $total_fb_shares;
        $return_array['total_fb_likes'] = $total_fb_likes;
        $return_array['total_fb_comments'] = $total_fb_comments;
        $return_array['total_linkedin'] = $total_linkedin;
        $return_array['total_tweets'] = $total_tweets;
        $return_array['total_words'] = $total_words;
        $return_array['average_words'] = $total_words/$i;

        
        return $return_array;
    }
    
    /*
     * create html for overview table
     */
    public function PDFtable_overview($data){
        
        $html = "";
        $av_words = round($data[average_words],0);
        $html = "<h2>Overview:</h2>
                        <table>
                            <col style='width: 13%'>
                            <col style='width: 13%'>
                            <col style='width: 12%'>
                            <col style='width: 12%'>
                            <col style='width: 13%'>
                            <col style='width: 13%'>
                            <col style='width: 12%'>
                            <col style='width: 12%'>
                            <thead>
                                <tr>
                                    <th>Total Words</th>
                                    <th>Average Words</th>
                                    <th>FB shares</th>
                                    <th>FB likes</th>
                                    <th>FB comments</th>
                                    <th>Linkedin shares</th>
                                    <th>Google +</th>
                                    <th>Tweets</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$data[total_words]</td>
                                    <td>$av_words</td>
                                    <td>$data[total_fb_shares]</td>
                                    <td>$data[total_fb_likes]</td>
                                    <td>$data[total_fb_comments]</td>
                                    <td>$data[total_linkedin]</td>
                                    <td>$data[total_pluses]</td>
                                    <td>$data[total_tweets]</td>
                                </tr>
                           </tbody>
                        </table>
                        <br>
                        <hr>";
        
        return $html;
        
    }
    
     /*
     * create full table
     */
    public function PDFtable_full($data){
        
        $table_body = "";

        
        foreach ($data as $row){
            
            $fb_shares = $row['facebook_shares']['shares'];
            $fb_like = $row['facebook_shares']['like'];
            $fb_comm = $row['facebook_shares']['comments'];
            
            $table_body = "$table_body <tr>
                                            <td>$row[title]</td>
                                            <td>$row[date]</td>
                                            <td>$row[word_count]</td>
                                            <td>$fb_like</td>
                                            <td>$fb_shares</td>
                                            <td>$fb_comm</td>
                                            <td>$row[tweets]</td>
                                            <td>$row[linkedind_shares]</td>
                                            <td>$row[google_pluses]</td>
                                            <td>$row[total_shares]</td>
                                        </tr>";
        }

        $html = "<h2>Report:</h2>
                        <table>
                            <col style='width: 20%'>
                            <col style='width: 10%'>
                            <col style='width: 8%'>
                            <col style='width: 10%'>
                            <col style='width: 10%'>
                            <col style='width: 10%'>
                            <col style='width: 7%'>
                            <col style='width: 8%'>
                            <col style='width: 9%'>
                            <col style='width: 8%'>
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Date</th>
                                    <th>Words</th>
                                    <th>FB likes</th>
                                    <th>FB shares</th>
                                    <th>FB comments</th>
                                    <th>Tweets</th>
                                    <th>Linkedin</th>
                                    <th>Google +</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                $table_body
                           </tbody>
                        </table>
                        <br>
                        <hr>";
        $html = str_replace('"',"'",$html);
        return $html;
        
    }
    
    
}


?>
