<h1>Word Count and Social Shares</h1>
<?php
// show credits
$credits = wcass_get_credits();
if(isset($credits->href) && isset($credits->anchor) && !isset($credits->banner)){
    echo 'Plugin by <a href="'.$credits->href.'" target="_blank"><small>'.$credits->anchor.'</small></a>';
} else if(isset($credits->href) && isset($credits->anchor) && isset($credits->banner)){
     echo '<a href="'.$credits->href.'" target="_blank"><img src="'.$credits->banner.'" alt="'.$credits->anchor.'"></a>';
}