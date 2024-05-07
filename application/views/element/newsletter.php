<section class="newsletter">
    <!-- CONTAINER -->
    <div class="container">

        <!-- ROW -->
        <div class="row">
            <div id="newsletter-error-message" >

            </div>
            <div class="col-sm-4 col-lg-6 text-right">
                <h4>STAY IN TOUCH</h4></div>
            <div class="col-sm-8 col-lg-6">
                <div class="newsletter-bx">
                    <form id="newsletter">
                        <input type="email" class="form-control search_fields" id="subscribe" name="email" placeholder="Enter email" required>
                        <button class="subscribe-btn">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $('#newsletter').submit(function(event)
    {
        // Stop full page load
        event.preventDefault();
        var fields = $(this).serializeArray();

        var result = { };
        $.each($(this).serializeArray(), function() {
            result[this.name] = this.value;
        });


        $.post('<?php echo WEBSITE_URL; ?>ajax/newletter',
            {
                fields: result,
                beforeSend: function() {
                    $('#loading-image').show();
                    $('#current_url').val('<?php echo WEBSITE_URL; ?>');
                    window.history.pushState('', '', '<?php echo WEBSITE_URL; ?>');
                }}, function(data) {
                var data = JSON.parse(data);

                if(data.status==0){
                    $("#newsletter-error-message").html('\n' +
                        '<div class="alert alert-danger">\n' +data.message+
                        '</div>');
                }else{
                    $("#newsletter-error-message").html('\n' +
                        '<div class="alert alert-success">\n' +(data.message)+
                        '</div>');
                }


                setTimeout(function(){
                    $("#newsletter-error-message").html('');
                }, 3000);
                $('#middle-content').html(data);
                $('#loading-image').hide();
            });
        ;
        return false;

    });
</script>