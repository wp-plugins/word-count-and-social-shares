<h2>CSV FIles:</h2>
<table class="widefat" style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Filename</th>
            <th>Date</th>
            <th>Size</th>
            <th>Options</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach($FILE_LIST as $file){
        ?>
        <tr>
            <td><a href="<?=$plugin_url;?><?=$file['filename'];?>"><?=$file['filename'];?></a></td>
            <td><?=$file['date'];?></td>
            <td><?=$file['size'];?></td>
            <td><a href="?page=social_shares_csv&act=delete&filename=<?=$file['filename'];?>">Delete</a></td>
        </tr>
        <?
        }
        ?>
    </tbody>
</table>