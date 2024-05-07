<?php if(!isset($_GET['data']))
{ ?>
<section class="home-section text-center">
<div class="home-section-overlay"></div>
<div class="container">
<div class="row">
<div class="col-md-12 padding-top-100 padding-bottom-70">
<h1><?php echo $rowPageInfo['title']; ?></h1>
<p style="max-width: 100%;">We're here to help you</p>
</div>
</div>
</div>
</section>

<section class="section-grey">
<div class="container">
<div class="row">
<?php foreach($rowFaqCatInfo as $value){?>
<div class="col-md-4">
<div class="main-services01">
<a href="<?php echo APP_URL.'page/faq-list/'.$value['id'];?>">
<img src="<?php echo CONTEXTCATEGORY_IMAGE_THUMB_URL.$value['image']?>" class="width-100" alt="pic">
<h3><?php echo $value['name']?></h3>
<p><?php echo $value['description']?></p>
</a>
</div>
</div>
<?php }?>
</div>
</div>  
</section>

 <section class="section-lyla" style="padding:40px 0px">
<div class="container">
<div class="col-md-9">
<h3 style="color:#fff; margin:0px;font-size:42px">Can’t find what you looking for?</h3>
</div>
<div class="col-md-3"><a href="<?php echo APP_URL;?>page/contact-us.html" class="btn-green scrool">Contact Us</a></div>
</div>
</section>
<?php }else{ ?>

<section class="section-grey" style="padding:0px">
<div class="container">
<div class="row">
<?php foreach($rowFaqCatInfo as $value){?>
<div class="col-md-4">
<div class="main-services01">
<a href="<?php echo APP_URL.'page/faq-list/'.$value['id'];?>?data=app">
<img src="<?php echo CONTEXTCATEGORY_IMAGE_THUMB_URL.$value['image']?>" class="width-100" alt="pic">
<h3><?php echo $value['name']?></h3>
<p><?php echo $value['description']?></p>
</a>
</div>
</div>
<?php }?>
</div>
</div>  
</section>
<?php } ?>
