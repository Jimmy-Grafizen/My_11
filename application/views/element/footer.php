<footer class="footer">
    <!-- CONTAINER -->
    <div class="container">
        <!-- ROW -->
        <div class="row" data-appear-top-offset="-200" data-animated="fadeInUp">
            <div class="col-lg-3 col-md-3 col-sm-4 padbot30">
                <h4>The <b>Company</b> </h4>
                <div class="f-menu clearfix">
                    <ul>
                        <li><a href="<?php echo HTTP_PATH."home/offers" ?>">Offers</a></li>
                        <li><a href="<?php echo HTTP_PATH."home/tnc" ?>">Terms & Conditions</a></li>
                        <li><a href="<?php echo HTTP_PATH."home/cancellation_and_refund" ?>">Cancellation & Refunds</a></li>
                        <li><a href="<?php echo HTTP_PATH."home/privacy_policy" ?>">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6 padbot30 foot_about_block">
                <h4>Get in  <b>touch</b> </h4>
                	<ul class="contact-info">
                     <li><a href="https://www.google.com/maps/place/El+Guero+2/@32.5555288,-116.9413705,21z/data=!4m15!1m9!2m8!1sHotels!3m6!1sHotels!2s32.5554085,+-116.94117329999999!3s0x80d947ab20b8ed5f:0xc8e97f02933b0d9a!4m2!1d-116.9411733!2d32.5554085!3m4!1s0x80d947ab20b8ed5f:0x6ea269c64d65a3d4!8m2!3d32.555474!4d-116.9412272?hl=en" target="_blank"><i class="fa fa-map-marker"></i>  2455 Otay Center Dr
Ste 114
San Diego, CA 92154</a></li>
						<li><i class="fa fa-phone-square"></i> (619) 240-7711</li>
                        <li><hr style="margin: 12px 0"></li>
                          <li><a href="https://www.google.com/maps/place/El+Guero+2/@32.8422247,-116.9873813,19z/data=!4m13!1m7!3m6!1s0x80dbfd54e905e0a7:0xfb7d83d06f6f921b!2s205+Town+Center+Pkwy+a,+Santee,+CA+92071,+USA!3b1!8m2!3d32.8422236!4d-116.9868341!3m4!1s0x80dbfd54e86940cf:0xf80b78d185db50aa!8m2!3d32.8422236!4d-116.9868341?hl=en" target="_blank"><i class="fa fa-map-marker"></i>  205 Town Center Parkway Unit A
Santee, CA 92071</a> </li>
						<li><i class="fa fa-phone-square"></i> (619) 596-9696</li>
                     
                        </ul>
                <ul class="contact-info" style="display:none;">

                    <?php  $companyAddress = $this->common_model->companyAddresses();
                            if(isset($companyAddress)){ 
                                foreach ($companyAddress as $value) { ?>

                                        <li><a href="https://www.google.com/maps/place/El+Guero+2/@32.5555288,-116.9413705,21z/data=!4m15!1m9!2m8!1sHotels!3m6!1sHotels!2s32.5554085,+-116.94117329999999!3s0x80d947ab20b8ed5f:0xc8e97f02933b0d9a!4m2!1d-116.9411733!2d32.5554085!3m4!1s0x80d947ab20b8ed5f:0x6ea269c64d65a3d4!8m2!3d32.555474!4d-116.9412272?hl=en" target="_blank"><i class="fa fa-map-marker"></i>
                                           <?php echo $value['description']?></a>
                                        </li>
                                        <li><i class="fa fa-phone-square"></i><?php echo $value['mobile']?></li>
                                    
                                <?php }

                            } ?>

                 
                </ul>
            </div>
            <div class="respond_clear"></div>
            <div class="col-lg-4 col-md-4 padbot30">
                <h4>Download our <b> App at</b></h4>
                <div class="Appstore-bx">
                    <a href="https://itunes.apple.com/us/app/el-guero-2/id1359866342" target="_blank"><i class="fa fa-apple"></i> Download from Appstore</a>
                   <a href="https://play.google.com/store/apps/details?id=com.elguero2" target="_blank"><i class="fa fa-android"></i> Download from Google Play</a>
                    <h4>Follow <b>us on :  </b> </h4>
                    <ul class="social">
                        <li><a href="https://www.yelp.com/biz/el-guero-2-san-diego" target="_blank"><i class="fa fa-yelp"></i></a></li>
                        <li><a href="https://www.facebook.com/pg/MXElguero2/reviews/" target="_blank"><i class="fa fa-facebook"></i></a></li>
                       
                       
                    </ul>
                </div>
            </div>
        </div>
        <!-- //ROW -->
    </div>
    <!-- //CONTAINER -->
    <div class="Mascot1"><img src="/../img/front/papo910_701.png" alt="" /></div>
    <div class="full-copyright">
        <div class="container">
            <div class="row copyright">
                <div class="col-sm-6 col-lg-6 text-left">
                    <p><img src="/../img/front/bike.png" alt="" /> Delivering at your doorstep</p>
                </div>
                <div class="col-sm-6 col-lg-6 text-right">
                    <p>2017 © Bochita LLC. All Rights Reserved. </p>
                </div>
            </div>
            <!-- //ROW -->
        </div>
    </div>
    </footer>

    