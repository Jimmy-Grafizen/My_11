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
        $("#datepicker").datepicker({
            format: 'yyyy-mm-dd',
            endDate: "1d"});

        // for search results
        $('#search_form').submit(function(event)
        {
            // Stop full page load
            event.preventDefault();
            var fields = $('.search_fields').serializeArray();
            $.post('<?php echo $ajax_url; ?>',
                    {
                        fields: fields, 't': 't',
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
            

            </div>
        </div>
        <div id="middle-content">
            <?php echo $ajax_content; ?>
        </div>
    </section>
</section>
<!-- END PAGE --> 

<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Team Players Details</h4>
        </div>
        <div class="modal-body">

        <div class="row our_rec_fetch"> 

        </div>
        <div class="row ">
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
         <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
        </div>
        </div>
    
    </div>
    </div>
      
    </div>
  </div>
  
  <!-- Modal -->
<div class="modal fade" id="Modelplayer_statistics" role="dialog">
   <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">POINTS BREAKUP</h4>
         </div>
         <div class="modal-body" id="points_breakup_load" style="background-color: #6db36d;color: #fff;">
         </div>
      </div>
   </div>
</div>


<script>
  
$(document).ready(function(){

    $("#myModal").on('hide.bs.modal', function(){
        $("#myModal").find(".our_rec_fetch").html("");
    });
    
    // for actions tab
    $(document).on('click', ".views_player-liset", function(e) {
        var elecls = $(".our_rec_fetch");
        var ele     = $(this);
         url = "<?php echo HTTP_PATH . "admin/joined_teams/view_team_player/" ?>";
         var datapost = ele.attr("modaldata");
         $.post(url,
                {
                    'datapost':datapost,'t': 't',
                    beforeSend: function() {
                        elecls.addClass("fa-spinner fa-pulse fa-fw");
                    }}, function(data) {
                        elecls.html(data);
                        elecls.removeClass("fa-spinner fa-pulse fa-fw");
                        //alert(data);
                        //$(document).find("#myModal").modal('hide');
                        
                });
    });
});
   $(document).ready(function () {
        $(document).on('show.bs.modal', '.modal', function (event) {
             var zIndex = 1040 + (10 * $('.modal:visible').length);
             $(this).css('z-index', zIndex);
             setTimeout(function() {
                 $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
             }, 0);
        });
   });
</script>