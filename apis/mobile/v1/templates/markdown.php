#<?= $template_data["title"] .PHP_EOL ?>
<?= 'BASE URL => '.$template_data["baseurl"] .PHP_EOL ?>
<?php
$i=1;
foreach($template_data["contents"] as $data) { ?>

<?=$i?>  Name : <?= $data["doc"]["name"] .PHP_EOL ?>
   Url : <?= $data["doc"]["url"] .PHP_EOL ?>
   Method : <?= $data["doc"]["method"] .PHP_EOL ?>
   Params : <?= $data["doc"]["params"] .PHP_EOL ?>
   Headers Params : <?= $data["doc"]["headers"] .PHP_EOL ?>

<?php $i++;?>
<?="--------------------------------------"?>



<?php } ?>
