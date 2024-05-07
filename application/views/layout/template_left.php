<script type="text/javascript">
    $(document).ready(function() {
        $('.showhide').click(function() {
            $(".slidediv").slideToggle();
        });
    });
</script>

<?php
if ($this->uri->segment(2) == "myProfile") {
    $my = "active";
} else {
    $my = "";
}
if ($this->uri->segment(2) == "editProfile") {
    $edit = "active";
} else {
    $edit = "";
}
if ($this->uri->segment(2) == "changepassword") {
    $password = "active";
} else {
    $password = "";
}
?>
<div class="left_menu">
<div class="my_menuses">
<div class="my_menus"><a href="javascript:void(0)" class="showhide">My Dashboard</a></div>
<ul class="slidediv">
<li class="<?php echo $my; ?> "><a href="<?php echo HTTP_PATH; ?>user/myProfile">My Profile</a></li>
<li class="<?php echo $edit; ?> "><a href="<?php echo HTTP_PATH; ?>user/editProfile">Edit Profile</a></li>
<li class="<?php echo $password; ?> "><a href="<?php echo HTTP_PATH; ?>user/changepassword">Change password</a></li>
<li class=""><a href="<?php echo HTTP_PATH; ?>home/logout">Logout </a></li>
</ul>
</div>
    <ul class="man_menu">
    <li class="<?php echo $my; ?> user_icon"><a href="<?php echo HTTP_PATH; ?>user/myProfile">My Profile</a></li>
    <li class="<?php echo $edit; ?> edit_profile"><a href="<?php echo HTTP_PATH; ?>user/editProfile">Edit Profile</a></li>
    <li class="<?php echo $password; ?> icon_pass"><a href="<?php echo HTTP_PATH; ?>user/changepassword">Change password</a></li>
    <li class="deactive"><a href="<?php echo HTTP_PATH; ?>home/logout">Logout </a></li>
    </ul>
</div>