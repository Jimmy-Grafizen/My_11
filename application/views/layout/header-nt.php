<?php
if ($this->uri->segment(2) == 'login') {
    $login = "active";
} else {
    $login = "";
}
if ($this->uri->segment(2) == 'register') {
    $register = "active";
} else {
    $register = "";
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
if ($this->uri->segment(1) == 'cart') {
    $cart = "active";
} else {
    $cart = "";
}
if ($this->uri->segment(2) == 'editProfile') {
    $editprofile = "active";
} else {
    $editprofile = "";
}
if ($this->uri->segment(1) == 'diamonds') {
    $jewellery = "active";
} else {
    $jewellery = "";
}

if ($this->uri->segment(2) == 'enquiries') {
    $enquiries = "active";
} else {
    $enquiries = "";
}
?>
<header>
    <div class="wrapper">
        <div class="logo"><a href="<?php echo HTTP_PATH; ?>home/"><img src="<?php echo HTTP_PATH; ?>img/front/logo.png" alt="Esynarion Jewellery - Express your love" ></a>
        </div>
        <div class="navigation">
            <ul>
                <li><a href="#">Store Location&nbsp;&nbsp;<span class="new">*New*</span></a></li>
                <li class="<?php echo $jewellery; ?>"><a href="<?php echo HTTP_PATH; ?>diamonds">Diamonds</a></li>
                <li class="<?php echo $enquiries; ?>"><a href="<?php echo HTTP_PATH . "user/enquiries" ?>">Enquiries</a></li>


                <?php
                if ($this->session->userdata('userId')) {
                    ?>
                    <li class="<?php echo $myprofile, $editprofile, $changepassword; ?>"><a  href="<?php echo HTTP_PATH; ?>user/myprofile">My Profile</a></li>
                    <li><a href="<?php echo HTTP_PATH; ?>home/logout">Logout</a></li>
                    <?php
                } else {
                    ?>
                    <li class="<?php echo $register; ?>"><a href="<?php echo HTTP_PATH; ?>home/register">Register</a></li>
                    <li class="<?php echo $login; ?>"><a href="<?php echo HTTP_PATH; ?>home/login">Login</a></li>
                    <?php
                }
                if ($this->session->userdata('userId')) {
                    ?>
                    <li class="<?php echo $mywishlist; ?>"><a href="<?php echo HTTP_PATH . "user/mywishlist" ?>"> Wishlist</a></li>
                <?php } else {
                    ?>
                    <li class="<?php echo $mywishlist; ?>"><a href="<?php echo HTTP_PATH . "home/mywishlist" ?>"> Wishlist</a></li>
                    <?php
                }
                ?>
<!--                <li class="hidenavtxt <?php echo $cart; ?>"><a href="<?php echo HTTP_PATH . "home/viewcart" ?>"> <p>Cart</p> <i class="fa fa-shopping-cart"></i>
              <div class="countbx">69</div> </a>
      </li>-->
            </ul>
            <div class="facebook">
                <img src="<?php echo HTTP_PATH; ?>img/front/facebook_plug_in.png" alt="fb_plugin">
            </div>
        </div>

    </div>
</header>