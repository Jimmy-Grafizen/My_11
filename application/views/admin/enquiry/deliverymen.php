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
        $("#datepicker1, #datepicker").datepicker({
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
        if($("#table-action").val()=="Delete"){
            if (!confirm("Are you sure you want to delete?")) {
                return false;
            }
        }
        var current_url = $('#current_url').val();
        //alert(current_url);return;
        current_url = current_url.replace("<?php echo $index_rpls; ?>", "<?php echo $ajax_index_rpls; ?>");
        $.post(current_url,
                {
                    fields: fields, check: matches, action: $("#table-action").val(), act: $("#act").val(),
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
        current_url = current_url.replace("<?php echo $index_rpls; ?>", "<?php echo $ajax_index_rpls; ?>");
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
                        Search Delivery Partner
                    </header>
                    <div class="panel-body">
                        <?php echo form_open('', array('class' => "form-inline", 'id' => 'search_form'));?>
                            <?php CreatFilterWithFromNtoDate(); ?>
                        <?php echo form_close(); ?>  
                    </div>
                </section>

            </div>
        </div>
        <div id="middle-content">
            <?php echo $ajax_content; ?>
        </div>
    </section>
</section>
<!-- END PAGE --> 

<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
