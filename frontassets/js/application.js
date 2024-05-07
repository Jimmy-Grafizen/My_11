var base = 'front/home/send_link';
var t=0;
    function sendapplink(ds)
    {
    $('#app_sccess').hide();
    if($('#send_app_input').val()==""){
        $('#app_error').html(' ').html('Please enter mobile number').show();
        return false;
    }else{
        var phone = $('#send_app_input').val().replace(/[^0-9]/g,'');
        if (phone.length != 10)
        {
            $('#app_error').html(' ').html('Please enter valid mobile number').show();
            return false;
        }
    }
    $('#app_error').html(' ')
    ds.html('Please Wait...');
    if(t==0)
    {
    t=1;
    var datastring = {'mob':$('#send_app_input').val()}; 
    $.post(base,datastring,function(response){
    $('#app_error').hide();
    $('#app_sccess').show();
    t=0;
    ds.html('Send app link');
    
    });
    
    } 
    }