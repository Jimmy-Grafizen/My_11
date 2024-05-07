<?php
if ($this->uri->segment(2) == 'editProfile') {
    $editprofile = "active";
} else {
    $editprofile = "";
}
if ($this->uri->segment(2) == 'myprofile') {
    $myprofile = "active";
} else {
    $myprofile = "";
}
if ($this->uri->segment(2) == 'changePassword') {
    $changepassword = "active";
} else {
    $changepassword = "";
}
if ($this->uri->segment(2) == 'mywishlist') {
    $mywishlist = "active";
} else {
    $mywishlist = "";
}

if ($this->uri->segment(2) == 'enquiries') {
    $enquiries = "active";
} else {
    $enquiries = "";
}
?>
<div class="sidelftbx">
    <div class="qucklnkbx">
        <div class="qckhdr">Quick Links</div>
        <ul class="jsdenav">
            <li><a class="<?php echo $myprofile; ?>" href="<?php echo HTTP_PATH; ?>user/myprofile/"><i class="fa fa-user"></i><span>My Profile</span></a></li>
            <li><a class="<?php echo $editprofile; ?>" href="<?php echo HTTP_PATH; ?>user/editProfile/"><i class="fa fa-edit"></i><span>Edit Profile</span></a></li>
            <li><a class="<?php echo $changepassword; ?>" href="<?php echo HTTP_PATH; ?>user/changePassword/"><i class="fa fa-lock"></i><span>Change Password</span></a></li>
            <li><a class="<?php echo $enquiries; ?>" href="<?php echo HTTP_PATH; ?>user/enquiries/"><i class="fa fa-comments"></i><span>Enquiries</span></a></li>
            <li><a class="<?php echo $mywishlist; ?>" href="<?php echo HTTP_PATH; ?>user/mywishlist"><i class="fa fa-heart"></i><span>My Wishlist</span></a></li>
            <li><a href="<?php echo HTTP_PATH; ?>home/logout/"><i class="fa fa-sign-out"></i><span>Logout</span></a></li>
        </ul>
    </div>
    <!--    <div class="vrfctnkbx">
            <div class="qckhdr">Verification</div>
            <div class="noticbx">
                <div class="noticlst">No verifications yet</div>
                <div class="adlst"><a href="#">Add More </a></div>
            </div>
        </div>-->
</div>