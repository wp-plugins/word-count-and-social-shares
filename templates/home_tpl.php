<script type="text/javascript">
      jQuery(document).ready(function(){
      
      
         jQuery('.date-picker').datepicker({
            dateFormat : 'yy-mm-dd'
         });
         
         jQuery('.datatable').dataTable({
                "sPaginationType": "full_numbers",
                "iDisplayLength": 30
            });
         jQuery("#tabs").tabs();
      });
      
</script>


<table>
    <tr>
        <td>
            <h2>Select date range:</h2>
            <form method="post" action="?page=social_shares">
                <div class="description">(date format example: 2013-05-25)</div><br>
                <table class="form-table">
                    <tr>
                        <td>From: </td>
                        <td><input type="text" class="date-picker regular-text" name="date_from"></td>
                    </tr>
                    <tr>
                        <td>To :</td>
                        <td><input type="text" class="date-picker regular-text" name="date_to"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="submit" value="Show Report" class="button-primary"> 
                        </td>
                    </tr>
                </table>
                <br>
                <b>If you don't choose date range, we will show data for last 30 days.</b>
            </form>
        </td>
        <td valign="top">
            <h2>Options:</h2>
            <table>
                <tr>
                    <td>
                        <div class="navdiv">
                            <div class="option_img"><a href="?page=social_shares_config"><img src="<?=plugins_url( 'images/settings.png' , __FILE__ );?>">Settings</a></div>
                            <div class="option_img"><a href="?page=social_shares&act=csv&date_from=<?=$from;?>&date_to=<?=$to;?>"><img src="<?=plugins_url( 'images/text_csv.png' , __FILE__ );?>">Create CSV</a></div>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>    
</table>





<br>
Selected date range: <b><?=$from_show;?> - <?=$to_show;?></b>
<br>
<hr>

<h2>Overview:</h2>
<div class="description">For selected date range</div>
<table class="widefat" style="margin-top: 20px;">
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
            <td><?=$overview_table_data['total_words'];?></td>
            <td><?=round($overview_table_data['average_words'],0);?></td>
            <td><?=$overview_table_data['total_fb_shares'];?></td>
            <td><?=$overview_table_data['total_fb_likes'];?></td>
            <td><?=$overview_table_data['total_fb_comments'];?></td>
            <td><?=$overview_table_data['total_linkedin'];?></td>
            <td><?=$overview_table_data['total_pluses'];?></td>
            <td><?=$overview_table_data['total_tweets'];?></td>
        </tr>
    </tbody>
</table>
<br>
<hr>

<h2>Report:</h2>
<table class="widefat datatable" style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Article</th>
            <th width="130">Date</th>
            <th>Words</th>
            <th>FB likes</th>
            <th>FB Shares</th>
            <th>FB Comments</th>
            <th>Tweets</th>
            <th>Linkedin</th>
            <th>Google pluses</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?
        
        foreach($cache_data as $data){
        ?>
        <tr>
            <td><a href="<?=$data['article_url'];?>" target="_blank"><?=$data['title'];?></a></td>
            <td><?=$data['date'];?></td>
            <td><?=$data['word_count'];?></td>
            <td><?=$data['facebook_shares']['like'];?></td>
            <td><?=$data['facebook_shares']['shares'];?></td>
            <td><?=$data['facebook_shares']['comments'];?></td>
            <td><?=$data['tweets'];?></td>
            <td><?=$data['linkedind_shares'];?></td>
            <td><?=$data['google_pluses'];?></td>
            <td><?=$data['total_shares'];?></td>
        </tr>
        <?
        
        
        }
        ?>
    </tbody>
</table>
<br>
<hr>

<h2>Charts:</h2>


<script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Words', 'Total Shares', 'Facebook Likes','Facebook Shares','Facebook Comments','Tweets','Linked In shares','Google Pluses'],
                <?=$total_chart_data['chart_total'];?>
                ]);

                var options = {
                title: 'Total social media activity:',
                hAxis: {title: 'Number of words',  titleTextStyle: {color: 'blue'}}
                };

                var chart = new google.visualization.AreaChart(document.getElementById('chart_div_total'));
                chart.draw(data, options);
            }
</script>

<script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Words', 'Shares'],
                <?=$total_chart_data['chart_fb_likes'];?>
                ]);

                var options = {
                title: 'Number of facebook likes:',
                hAxis: {title: 'Number of words',  titleTextStyle: {color: 'blue'}}
                };

                var chart = new google.visualization.AreaChart(document.getElementById('chart_div_total_fb_like'));
                chart.draw(data, options);
            }
</script>

<script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Words', 'Shares'],
                <?=$total_chart_data['chart_fb_shares'];?>
                ]);

                var options = {
                title: 'Number of facebook shares:',
                hAxis: {title: 'Number of words',  titleTextStyle: {color: 'blue'}}
                };

                var chart = new google.visualization.AreaChart(document.getElementById('chart_div_total_fb_shares'));
                chart.draw(data, options);
            }
</script>

<script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Words', 'Shares'],
                <?=$total_chart_data['chart_fb_comments'];?>
                ]);

                var options = {
                title: 'Number of facebook commentes:',
                hAxis: {title: 'Number of words',  titleTextStyle: {color: 'blue'}}
                };

                var chart = new google.visualization.AreaChart(document.getElementById('chart_div_total_fb_comments'));
                chart.draw(data, options);
            }
</script>

<script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Words', 'Shares'],
                <?=$total_chart_data['chart_tweets'];?>
                ]);

                var options = {
                title: 'Number of tweets:',
                hAxis: {title: 'Number of words',  titleTextStyle: {color: 'blue'}}
                };

                var chart = new google.visualization.AreaChart(document.getElementById('chart_div_total_tweets'));
                chart.draw(data, options);
            }
</script>

<script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Words', 'Shares'],
                <?=$total_chart_data['chart_linkedin'];?>
                ]);

                var options = {
                title: 'Number of linkedin shares:',
                hAxis: {title: 'Number of words',  titleTextStyle: {color: 'blue'}}
                };

                var chart = new google.visualization.AreaChart(document.getElementById('chart_div_total_linkedin'));
                chart.draw(data, options);
            }
</script>

<script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Words', 'Shares'],
                <?=$total_chart_data['chart_google_pluses'];?>
                ]);

                var options = {
                title: 'Number of google pluses',
                hAxis: {title: 'Number of words',  titleTextStyle: {color: 'blue'}}
                };

                var chart = new google.visualization.AreaChart(document.getElementById('chart_div_total_gplus'));
                chart.draw(data, options);
            }
</script>



 


<div id="chart_div_total" style="height: 600px;"></div>
<div id="chart_div_total_fb_like" style="height: 200px;"></div>
<div id="chart_div_total_fb_shares" style="height: 200px;"></div>
<div id="chart_div_total_fb_comments" style="height: 200px;"></div>
<div id="chart_div_total_tweets" style="height: 200px;"></div>
<div id="chart_div_total_linkedin" style="height: 200px;"></div>
<div id="chart_div_total_gplus" style="height: 200px;"></div>

