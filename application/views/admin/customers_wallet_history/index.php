<script>
    function checkedAll() {
        for (var i = 0; i < document.getElementById('table_form').elements.length; i++) {
            document.getElementById('table_form').elements[i].checked = true;
        }
    }
    function uncheckedAll() {
        for (var i = 0; i < document.getElementById('table_form').elements.length; i++) {
            document.getElementById('table_form').elements[i].checked = false;
        }
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {

        // page date picker feature
        

        // for search results
        $('#search_form').submit(function(event)
        {
            var filter="per_page";
            var btn = $(document.activeElement).val();
            var fields = $(this).find("select, textarea, input").length;
            //console.log(fields);
            var empty = $(this).find("select, textarea, input").filter(function() {
                return this.value === "";
            });
            if( empty.length !== fields ){
                filter="filtered";
            }
            // Stop full page load
            event.preventDefault();
            var fields = $('.search_fields').serializeArray();
            $.post('<?php echo $ajax_url; ?>',
                    {
                        fields: fields, 't': 't',action_export: btn, filter: filter,
                        beforeSend: function() {
                            $('#loading-image').show();
                            $('#current_url').val('<?php echo $ajax_url; ?>');
                            window.history.pushState('', '', '<?php echo $this_url; ?>');
                        }}, function(data) {
                            if( btn == 'export' ){
                                var extension = data.split('.').pop();
                                if(extension == "xls" ){
                                    location.href =data;
                                }else{
                                    $('#middle-content').html(data);
                                }
                            }else{
                                $('#middle-content').html(data);
                            }
                            $('#loading-image').hide();
            });
            ;
            return false;

        });

    });

    // for select all and actions
    $(document).on("submit", '.form', function(event)
    {
        // Stop full page load
        event.preventDefault();
        var fields = $('.search_fields').serializeArray();
        var matches = [];
        $(".className:checked").each(function() {
            matches.push(this.value);
        });

        if (!matches.length) {
            alert('Please select atleast one record');
            return false;
        }

        if (!$("#table-action").val()) {
            alert('Please select action');
            return false;
        }
        var current_url = $('#current_url').val();
        current_url = current_url.replace("index", "ajax_index");
        $.post(current_url,
                {
                    fields: fields, check: matches, action: $("#table-action").val(),
                    beforeSend: function() {
                        $('#loading-image').show();
                        window.history.pushState('', '', $('#current_url').val());
                        $('#current_url').val('<?php echo $ajax_url; ?>');
                    }
                },
        function(data) {
            $('#middle-content').html(data);
            $('#loading-image').hide();
        });
        return false;

    });

    // for actions tab
    $(document).on('click', ".action-list", function(e) {
        e.preventDefault();
        if ($(this).hasClass("delete-list")) {
            if (!confirm("Are you sure you want to delete?")) {
                return false;
            }
        }
        var url = $(this).attr("href");
        var fields = $('.search_fields').serializeArray();
        var current_url = $('#current_url').val();
        current_url = current_url.replace("index", "ajax_index");
        $.post(url,
                {
                    't': 't',
                    beforeSend: function() {
                        $('#loading-image').show();
                        $('#current_url').val('<?php echo $ajax_url; ?>');
                    }}, function(data) {
            $.post(current_url,
                    {
                        fields: fields, 't': 't',
                        beforeSend: function() {
                            $('#loading-image').show();
                            $('#current_url').val('<?php echo $ajax_url; ?>');
                        }}, function(data) {
                $('#middle-content').html(data);
                $('#loading-image').hide();
            });

        });
        ;
        return false;

    })

    // for enable sort feature
    $(document).on('click', ".enable-sort", function(e) {
        var field = $(this).attr('field');
        var sort_type = $(this).attr('sort_type') ? $(this).attr('sort_type') : "asc";
        e.preventDefault();
        var fields = $('.search_fields').serializeArray();
        $.post('<?php echo $ajax_url; ?>?sort=' + sort_type + "&field=" + field,
                {
                    fields: fields,
                    beforeSend: function() {
                        $('#loading-image').show();
                        $('#current_url').val('<?php echo $ajax_url; ?>');
                        window.history.pushState('', '', '<?php echo $this_url; ?>');
                    }}, function(data) {
            $('#middle-content').html(data);
            $('#loading-image').hide();
        });
        ;
        return false;
    });
</script>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/css/datepicker.css" />
<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/data-tables/DT_bootstrap.css" />
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Search <?php echo $names ?> 
                    </header>
                    <div class="panel-body">
<?php
    $status_options = array(""=>"Select Status", "S"=>"Success", "F"=>"Failed");
    $wallet_type = array(""=>"Select Status", "CUSTOMER_JOIN_CONTEST"=>"CUSTOMER_JOIN_CONTEST","CUSTOMER_RECEIVED_RCB"=>"CUSTOMER_RECEIVED_RCB","CUSTOMER_RECEIVED_REFCB"=>"CUSTOMER_RECEIVED_REFCB","CUSTOMER_REFUND_ABCONTEST"=>"CUSTOMER_REFUND_ABCONTEST","CUSTOMER_REFUND_CONTEST"=>"CUSTOMER_REFUND_CONTEST","CUSTOMER_WALLET_RECHARGE"=>"CUSTOMER_WALLET_RECHARGE","CUSTOMER_WIN_CONTEST"=>"CUSTOMER_WIN_CONTEST","REGISTER_CASH_BONUS"=>"REGISTER_CASH_BONUS","WALLET_RECHARGE_ADMIN"=>"WALLET_RECHARGE_ADMIN","WALLET_WITHDRAW_ADMIN"=>"WALLET_WITHDRAW_ADMIN",);
?>
                        <?php
                        echo form_open('', array('class' => "form-inline", 'id' => 'search_form'));
                        ?>
                        <div class="form-group">
                            <label class="sr-only" for="search">Your Keyword</label>
                            <?php
                            if ($this->uri->segment(4) == 'search') {
                                $search = urldecode($this->uri->segment(5));
                            } else {
                                $search = urldecode($this->input->post('search'));
                            }
                            $data = array(
                                'name' => 'from_date',
                                'id' => 'datepicker12',
                                'value' => $search,
                                'class' => 'required search_fields form-control',
                                'placeholder' => 'From Date'
                            );
                            echo form_input($data);
                            ?>
                        </div>
                        <div class="form-group">
                            <label class="sr-only" for="date">Search by joining date</label>
                            <?php
                            if ($this->uri->segment(4) == 'search') {
                                $search = urldecode($this->uri->segment(5));
                            } else {
                                $search = urldecode($this->input->post('search'));
                            }
                            $data = array(
                                'name' => 'to_date',
                                'id' => 'datepickerto',
                                'value' => $search,
                                'class' => 'required default-date-picker search_fields form-control',
                                'placeholder' => 'To Date'
                            );
                            echo form_input($data);
                            ?>
                        </div>
                        <div class="form-group ">
                                <div class="col-lg-10">
			
                                    <?php
                  
                                    $value = set_value("status") ? set_value("status") : (isset($user_detail['status']) ? $user_detail['status'] : '');
                                    echo form_dropdown('status', $status_options, $value, 'class="form-control required search_fields" id="status"');
                                    ?>
                                </div>
                            </div>
                        <div class="form-group ">
                                <div class="col-lg-10">
            
                                    <?php
                  
                                    $value = set_value("wallet_type") ? set_value("wallet_type") : (isset($user_detail['wallet_type']) ? $user_detail['wallet_type'] : '');
                                    echo form_dropdown('wallet_type', $wallet_type, $value, 'class="form-control required search_fields" id="wallet_type__lll"');
                                    ?>
                                </div>
                            </div>
                        <button type="submit" class="btn btn-success">Search</button>
                        <button type="reset" class="btn btn-danger" value="Reset">Reset</button>
                        <button name="button" id="export_excel" value='export' class="btn btn-primary">Export as excel</button>
                        <?php echo form_close(); ?>
                    </div>
                </section>

            </div>
        </div>
        <div class="table-toolbar form-group">
            <div class="btn-group">
                <a class="btn btn-success" href="#myAlert" data-toggle="modal">Recharge Wallet</a>
            </div>
            <div class="btn-group" style="margin-left: 5px;">
                <a class="btn btn-success" href="#myAlert_with" data-toggle="modal">Withdraw Wallet</a>
            </div>
        </div>
        <div id="middle-content">
            <?php echo $ajax_content; ?>
        </div>
    </section>
</section>
<!-- END PAGE --> 




<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>


<div id="myAlert" class="modal fade" role="dialog" >
<div class="modal-dialog" role="document">
 <div class="modal-content">
    <div class="modal-header">
        <button data-dismiss="modal" class="close" type="button">×</button>
        <h3>Recharge Wallet</h3>
    </div>
     <div class="modal-body">
    <form action="javascript:void(0)" name="customer_recharge_wallet_form" id="customer_recharge_wallet_form" method="post">
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label" for="rechargeAmoung">Recharge Amount <span class='mandatory'>*</span> </label>
                        <div class="controls">
                            <?php 
                                echo form_input(array("type"=>"text", "class"=>"form-control input-large", "placeholder"=>"Recharge Amount", "name"=>"recharge_amount", "id"=>"recharge_amount"));
                            ?>
                            <div class="error" id="recharge_amount_error"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label" for="rechargeAmoung">Wallet Type <span class='mandatory'>*</span> </label>
                        <div class="controls">
                            <?php 

                              $options = unserialize(WALLET_TYPE);
                              $options['']="Select wallet Type";

                                
                                $value="";
                               echo form_dropdown('wallet_type', $options, $value, 'class="form-control input-large" id="wallet_type"');
                            ?>
                            <div class="error" id="wallet_type_error"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label" for="lastname">Description<span class='mandatory'>*</span></label>
                        <div class="controls">
                            <?php 
                                echo form_textarea(array("type"=>"text", "class"=>"form-control input-large", "placeholder"=>"Description", "name"=>"description", "id"=>"description", 'maxlength'=>500));
                            ?>
                            <div class="error" id="description_error"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
        
        <?php  $customers_id = $set_data = $this->session->userdata('customers_id'); ?>
            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $customers_id; ?>">
            <?php echo form_submit('recharge_wallet', 'Recharge',"class='btn btn-success'","id='submit_button'"); ?>
            <a data-dismiss="modal" class="btn btn-success" href="#">Cancel</a>
        </div>
    </form>
    </div>
    </div>
    </div>
</div>




<div id="myAlert_with" class="modal fade" role="dialog" >
<div class="modal-dialog" role="document">
 <div class="modal-content">
    <div class="modal-header">
        <button data-dismiss="modal" class="close" type="button">×</button>
        <h3>Withdraw Wallet</h3>
    </div>
     <div class="modal-body">
    <form action="javascript:void(0)" name="customer_withdraw_wallet_form" id="customer_withdraw_wallet_form" method="post">
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label" for="rechargeAmoung">Withdraw Amount <span class='mandatory'>*</span> </label>
                        <div class="controls">
                            <?php 
                                echo form_input(array("type"=>"text", "class"=>"form-control input-large", "placeholder"=>"Withdraw Amount", "name"=>"withdraw_amount", "id"=>"withdraw_amount"));
                            ?>
                            <div class="error" id="withdraw_amount_error"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label" for="rechargeAmoung">Wallet Type <span class='mandatory'>*</span> </label>
                        <div class="controls">
                            <?php 

                              $options = unserialize(WALLET_TYPE);
                              $options['']="Select wallet Type";

                                
                                $value="";
                               echo form_dropdown('wallet_type', $options, $value, 'class="form-control input-large" id="withdraw_wallet_type"');
                            ?>
                            <div class="error" id="withdraw_wallet_type_error"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label" for="lastname">Description<span class='mandatory'>*</span></label>
                        <div class="controls">
                            <?php 
                                echo form_textarea(array("type"=>"text", "class"=>"form-control input-large", "placeholder"=>"Description", "name"=>"withdraw_description", "id"=>"withdraw_description", 'maxlength'=>500));
                            ?>
                            <div class="error" id="withdraw_description_error"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
        <?php  $customers_id = $set_data = $this->session->userdata('customers_id'); ?>
            <input type="hidden" name="withdraw_customers_id" id="withdraw_customer_id" value="<?php echo $customers_id; ?>">
            <?php echo form_submit('withdraw_wallet', 'Withdraw',"class='btn btn-success'","id='submit_button'"); ?>

            <a data-dismiss="modal" class="btn btn-success" href="#">Cancel</a>
        </div>
    </form>
    </div>
    </div>
    </div>
</div>



<script type="text/javascript">

$(document).ready(function(){





	$("#customer_recharge_wallet_form").on("submit", function(e){
			e.preventDefault();
			var recharge_amount = $("#recharge_amount").val();
			var recharge_description = $("#description").val();
			var recharge_wallet_type = $("#wallet_type").val();
			var recharge_customer_id = $("#customer_id").val();
			console.log(recharge_amount);
			var ajaxerror = 0;
			
			if(recharge_description==""){
				$("#description_error").empty().html("Description is required.");
				ajaxerror = 1;
			}else {
	           $("#description_error").empty().html("");
			}

			if(recharge_wallet_type==""){
				$("#wallet_type_error").empty().html("Please select wallet Type.");
				ajaxerror = 1;
			}else {
	           $("#wallet_type_error").empty().html("");
			}


			if(isNaN(recharge_amount) || recharge_amount==""){
				$("#recharge_amount_error").empty().html("Amount is required.");
				ajaxerror = 1;
			}else{
				$("#recharge_amount_error").empty().html("");
			}
			if(recharge_amount<=0){
				$("#recharge_amount_error").empty().html("Amount should be greater than zero.");
				ajaxerror = 1;
			}else{
				$("#recharge_amount_error").empty().html("");
			}
			if(ajaxerror == 0){
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: '<?php echo APP_URL."admin/customers_wallet_history/wallet_recharge"; ?>',
					data: {
						recharge_amount: recharge_amount,
						recharge_description: recharge_description,
						recharge_wallet_type: recharge_wallet_type,					
						recharge_customer_id: recharge_customer_id
					},
					beforeSend: function(){
						$("#overlay").css("display", "block");
					},
					success: function(data) {
						$("#overlay").css("display", "none");
						alert(data.message);
						if(data.status=="success")
							location.reload();
					}
				});
			}
		});



	$("#customer_withdraw_wallet_form").on("submit", function(e){
			e.preventDefault();
			var withdraw_amount = $("#withdraw_amount").val();
			var withdraw_wallet_type = $("#withdraw_wallet_type").val();

			var withdraw_description = $("#withdraw_description").val();
			var withdraw_customer_id = $("#withdraw_customer_id").val();
			
			
			
			var ajaxerror = 0;
			
			if(withdraw_description==""){
				$("#withdraw_description_error").empty().html("Description is required.");
				ajaxerror = 1;
			}else {
	           $("#withdraw_description_error").empty().html("");
			}


			if(withdraw_wallet_type==""){
				$("#withdraw_wallet_type_error").empty().html("Please select wallet Type.");
				ajaxerror = 1;
			}else {
	           $("#withdraw_wallet_type_error").empty().html("");
			}


			if(isNaN(withdraw_amount) || withdraw_amount==""){
				$("#withdraw_amount_error").empty().html("Amount is required.");
				ajaxerror = 1;
			}else{
				$("#withdraw_amount_error").empty().html("");
			}
			if(withdraw_amount<=0){
				$("#withdraw_amount_error").empty().html("Amount should be greater than zero.");
				ajaxerror = 1;
			}else{
				$("#withdraw_amount_error").empty().html("");
			}
			
			if(ajaxerror == 0){
				
				

				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: '<?php echo APP_URL."admin/customers_wallet_history/wallet_withdraw"; ?>',
					data: {
						withdraw_amount: withdraw_amount,
						withdraw_description: withdraw_description,
						withdraw_wallet_type: withdraw_wallet_type,
						withdraw_customer_id: withdraw_customer_id
					},
					beforeSend: function(){
						$("#overlay").css("display", "block");
					},					
					success: function(data) {
						$("#overlay").css("display", "none");
						alert(data.message);
						if(data.status=="success")
							location.reload();
					}
				});
			}
		});


	
});

	

	

</script>
<style>
.error{color:red;}
</style>

