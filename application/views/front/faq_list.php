<?php if(!isset($_GET['data']))
{ ?>
<section class="home-section text-center">
<div class="home-section-overlay"></div>
<div class="container">
<div class="row">
<div class="col-md-12 padding-top-100 padding-bottom-70">
<h1>Frequently Asked Question</h1>
<p style="max-width: 100%;">We're here to help you</p>
</div>
</div>
</div>
</section>

<?php } ?>
<section>
<div class="section-grey">
    <div class="container">
    <div class="row">
        <div class="col-md-12 text-center">
            <h2 class="section-title"><?php echo $rowFaqCatInfo['name']; ?></h2>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <?php $i =1; foreach($rowFaqInfo as $faq) {?>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="heading<?php echo $i;?>">
                        <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $i;?>" aria-expanded="true" aria-controls="collapse<?php echo $i;?>"> <i class="icon icon-rocket panel-icon"></i><?php echo $i;?>. <?php echo $faq['name']; ?></a>
                        </h4>
                    </div>
                    <div id="collapse<?php echo $i;?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $i;?>">
                        <div class="panel-body">
                            <p><?php echo $faq['content']; ?></p>
                        </div>
                    </div>
                </div>
                <?php $i++;}?>
            </div>
        </div>
    </div>
    </div>
</div>
</section>
<?php if(!isset($_GET['data']))
{ ?><section class="section-lyla" style="padding:40px 0px">
    <div class="container">
        <div class="col-md-9">
            <h3 style="color:#fff; margin:0px;font-size:42px">Can’t find what you looking for?</h3>
        </div>
        <div class="col-md-3"><a href="<?php echo APP_URL;?>page/contact-us.html" class="btn-green scrool">Contact Us</a></div>
    </div>
</section>
<?php } ?>
