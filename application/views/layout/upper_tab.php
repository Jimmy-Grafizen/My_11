<?php
if ($this->uri->segment(1) == "user" AND ( $this->uri->segment(2) == "myProfile" OR $this->uri->segment(2) == "editProfile" OR $this->uri->segment(2) == "changepassword")) {
    $my = 'class="active"';
} else {
    $my = "";
}
if ($this->uri->segment(1) == "classified" AND ( $this->uri->segment(2) == "listingAd" or $this->uri->segment(2) == "bids")) {
    $edit = 'class="active"';
} else {
    $edit = "";
}
if ($this->uri->segment(1) == "user" AND ( $this->uri->segment(2) == "myfavoritelist")) {
    $myfavoritelist = 'class="active"';
} else {
    $myfavoritelist = "";
}
if ($this->uri->segment(1) == "classified" AND ($this->uri->segment(2) == "postad" OR $this->uri->segment(2) == "fillDetail")) {
    $postad = 'class="active"';
} else {
    $postad = "";
}
?>
<div class="right_top">
    <div class="tabs_grp">
        <a href="<?php echo HTTP_PATH ?>classified/postad" <?php echo $postad; ?> >Post An Ad</a>
        <a href="<?php echo HTTP_PATH ?>classified/listingAd" <?php echo $edit; ?> >Manage My Ads</a>
        <a href="<?php echo HTTP_PATH ?>user/myProfile" <?php echo $my; ?>>My Details</a>
        <a href="<?php echo HTTP_PATH ?>user/myfavoritelist" <?php echo $myfavoritelist; ?>>My Favorites</a>
    </div>
    <div class="notification">
        <?php
        $cond = "id = '" . $this->session->userdata("userId") . "'";
        $user_detail = $this->main_model->cruid_select("tbl_users", "*", "", $cond);

        $cond_1 = "user_id = '" . $user_detail['id'] . "'";
        $record = $this->main_model->cruid_select_array("tbl_classifieds", 'id', "", $cond_1);
        $advertisement = count($record);
        ?>
        <p><strong>Hi <?php echo $user_detail['first_name'] ? $user_detail['first_name'] : "N/A" ?>,</strong> you currently have <?php echo $advertisement ? $advertisement : "0"; ?> advert</p>
    </div>
</div>