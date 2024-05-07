
<div class="myacunt">
    <span>welcome</span>
    <div class="nmeacnt"><a href="<?php echo HTTP_PATH . "user/myprofile" ?>"><?php echo ucfirst($user_detail['first_name']) . " " . ucfirst($user_detail['last_name']); ?></a></div>
    <div class="mleacnt"><?php echo $user_detail['email']; ?> </div>
    <div class="mleacnt"><?php echo $user_detail['mobile']; ?></div>
</div>
