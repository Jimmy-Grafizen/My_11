<?php

$loginUser = $this->session->userdata('loginUser');
//echo "<pre>";print_r($loginUser);die;
$permissions = $loginUser['permissions']?json_decode($loginUser['permissions']):array();

$config = "";
$users = "";
$diamond = "";
if ($this->uri->segment(3) == 'changepassword') {
    $changepassword = 'active';
    $config = 'display: block;';
} else {
    $changepassword = '';
}

if ($this->uri->segment(2) == 'home' and ($this->uri->segment(3) == 'index' or $this->uri->segment(3) == 'dashboard' or $this->uri->segment(3) == '')) {
    $dashboard = 'active';
} else {
    $dashboard = '';
}
if ($this->uri->segment(3) == 'changeemail') {
    $changeEmail = 'active';
    $config = 'display: block;';
} else {
    $changeEmail = '';
}

if ($this->uri->segment(3) == 'updateprofile') {
    $updateprofile = 'active';
    $config = 'display: block;';
} else {
    $updateprofile = '';
}

if ($this->uri->segment(2) == 'content') {
    $contents = 'active';
    $config = 'display: block;';
} else {
    $contents = '';
}

if ($this->uri->segment(2) == 'users' and $this->uri->segment(3) == 'add') {
    $users = 'display: block;';
    $userAdd = 'active';
} else {
    $userAdd = '';
}
if ($this->uri->segment(2) == 'users' and ( $this->uri->segment(3) == 'index' OR $this->uri->segment(3) == '' or $this->uri->segment(3) == 'edit' )) {
    $usersList = 'active';
    $users = 'display: block;';
} else {
    $usersList = '';
}
if ($this->uri->segment(2) == 'diamond' and $this->uri->segment(3) == 'add') {
    $diamond = 'display: block;';
    $diamondAdd = 'active';
} else {
    $diamondAdd = '';
}

if ($this->uri->segment(2) == 'diamond' and ( $this->uri->segment(3) == 'index' OR $this->uri->segment(3) == '' OR $this->uri->segment(3) == 'edit' )) {
     $diamond = 'display: block;';
    $diamondList = 'active';
   
} else {
    $diamondList = '';
}

if ($this->uri->segment(2) == 'diamond' and ($this->uri->segment(3) == 'enquiry' or $this->uri->segment(3) == 'view')) {
    $diamond = 'display: block;';
    $diamondEnquiry = 'active';
} else {
    $diamondEnquiry = '';
}
$id = $this->session->userdata('adminId');

?>
<aside>
    <div id="sidebar"  class="nav-collapse ">
        <ul class="sidebar-menu" id="nav-accordion">
            <li>
                <a href="<?php echo HTTP_PATH . 'admin/home/dashboard' ?>" class="<?php echo $dashboard; ?>">
                    <i class="fa"><img src="<?php echo HTTP_PATH . 'img/icons/dashboard.png' ?>" alt="" /></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <?php 
            if(!empty($permissions)){
                foreach ($permissions as $key => $value) {
                    if(!empty($value)) {
                         $in = 1;
                        foreach ($value as $val) {
                            if($val->level and $val->menu){
                                if($in == 1){
                        ?>
                                <li class="sub-menu">
                                    <a href="javascript:;">
                                        <i class="fa"><img src="<?php echo HTTP_PATH . 'img/icons/'.str_ireplace(" ","_",strtolower ( $key ) ).'.png' ?>" alt="" /></i> <span><?php echo $key; ?></span>
                                    </a>
                                    <ul class="sub">
                        <?php } ?>
                                       <li> <?php echo anchor($val->url, $val->label) ?></li>
                                    <?php if($in == array_column_counts($value) ){ ?>  
                                    </ul>
                                </li>
                        <?php  } ?> 
                        <?php  
                            $in++; 
                            }
                        ?>
                    <?php
                        } //end Second loop
                    } ?>
        <?php
                }// End First loop
            }
        ?>
        <li class="sub-menu">-->
           <a href="javascript:void(0);">
               <i class="fa"><img src="<?php echo HTTP_PATH . 'img/icons/appearance.png'?>" alt="" /></i> <span>Commission</span>
           </a>
           <ul class="sub">
              <li><a href="<?php echo HTTP_PATH . 'admin/commission/index' ?>">Commission Slab</a></li>
           </ul>
       </li> 
        <li class="sub-menu">
            <a href="javascript:void(0);">
                <i class="fa"><img src="<?php echo HTTP_PATH . 'img/icons/appearance.png'?>" alt="" /></i> <span>Appearance</span>
            </a>
            <ul class="sub">
               <li><a href="<?php echo HTTP_PATH . 'admin/testimonial/index' ?>">Testimonials</a></li>
            </ul>
        </li>
        <li class="sub-menu">
            <a href="javascript:void(0);">
                <i class="fa"><img src="<?php echo HTTP_PATH . 'img/icons/appearance.png'?>" alt="" /></i> <span>Faq</span>
            </a>
            <ul class="sub">
               <li><a href="<?php echo HTTP_PATH . 'admin/faq/index' ?>">Manage Faq</a></li>
               <li><a href="<?php echo HTTP_PATH . 'admin/faq_category/index' ?>">List Category</a></li>
               <li><a href="<?php echo HTTP_PATH . 'admin/faq_category/add' ?>">Add Category</a></li>
            </ul>
        </li>
        </ul>
    </div>
	<div class="sidebar-overlay"></div>
</aside>