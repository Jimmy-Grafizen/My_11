/* Axuta Theme Scripts */
var host = window.location.protocol+"//"+window.location.hostname;              
 
var ServerUrl               =host+"/";
var BaseUrlPageContents     = ServerUrl+"project/gully11/admin/content_management/get_page_contents/";
var BaseUrlExistsGametype   = ServerUrl+"project/gully11/admin/cricket_points/get_exists_gametype";
var BaseUrlWebsite          = ServerUrl+"project/gully11/m/pages/";

// Load page content dynamically 
function PageContant(pageTitle, page,footer=false){ 
      $.ajax({
          url: BaseUrlPageContents+page,
          dataType: "json",
          success: function(data) {
         
            $.each(data, function( k, v ) {
                //alert(" Key "+ k + " Valu" +v.page_name)
                $("#"+ v.page_name).html(v.content);
                
                if (v.page_name == pageTitle) {
                      document.title = v.title;
                } 

                if(footer){
                   var $target = $('.accountDetailsBx2');
                   $target.animate({scrollTop: $target.height()-800}, 500);
                }

            }); 
        },
        error : function(request, status, error) {

            var val = request.responseText;
            alert("error"+status);
        },
      });
}


function LoadPointsSytems(){
    $.get(BaseUrlExistsGametype+"_sports", function(data) {
        $("#sportstabscreate").html(data);
    });
}

function LoadHowToPlaySytems(){
    $.get(BaseUrlExistsGametype+"_htp_sports", function(data) {
        $("#tabscreate").html(data);
    });
}

$(document).ready(function(){

    $(document).on("click",'[data-toggle="htp_tab_sports"]', function(e) {
        var $this   = $(this),
            loadurl = $this.attr('href'),
            targ    = $this.attr('data-target');
        $.ajax({
          url: loadurl,
          dataType: "json",
          success: function(data) {
            if(data.length>0){
                $.each(data, function( k, v ) {
                    //alert(" Key "+ k + " Valu" +v.page_name)
                    $("#data-target").html(v.content);
                }); 
            }else{
                $("#data-target").html("");
            }
        },
        error : function(request, status, error) {

            var val = request.responseText;
            alert("error"+status);
        },
      });

        $this.tab('show');
        return false;
    });

    $(document).on("click",'[data-toggle="tab_sports"]', function(e) {
        var $this   = $(this),
            loadurl = $this.attr('href'),
            targ    = $this.attr('data-target');
        $.get(loadurl, function(data) {
            $("#tabscreate").html(data);
        });

        $this.tab('show');
        return false;
    });

    $(document).on("click",'[data-toggle="tab"]', function(e) {
        var $this   = $(this),
            loadurl = $this.attr('href'),
            targ    = $this.attr('data-target');
        $.get(loadurl, function(data) {
            $("#data-target").html(data);
        });

        $this.tab('show');
        return false;
    });
    
});

    $(document).ready(function(){

        $('#addHref li a.baseurl').each(function(){ 
            var oldUrl = $(this).attr("href"); // Get current url

            var newUrl = BaseUrlWebsite+oldUrl; // Create new url

            $(this).attr("href", newUrl); // Set herf value

        });

    });

(function($){ "use strict";
             
    $(window).on('load', function() {
        $('body').addClass('loaded');
    });
        
 $(".menubtn").click(function(e) {
    e.preventDefault();
    $("body").toggleClass("toggled");
  });
	                        
     
/*=========================================================================
	Scroll To Top
=========================================================================*/ 
    $(window).on( 'scroll', function () {
        if ($(this).scrollTop() > 100) {
            $('#scroll-to-top').fadeIn();
        } else {
            $('#scroll-to-top').fadeOut();
        }
    });

             
/*=========================================================================
	MAILCHIMP
=========================================================================*/ 
    if ($('.subscribe_form').length>0) {
        /*  MAILCHIMP  */
        $('.subscribe_form').ajaxChimp({
            language: 'es',
            callback: mailchimpCallback,
            url: "//alexatheme.us14.list-manage.com/subscribe/post?u=48e55a88ece7641124b31a029&amp;id=361ec5b369" 
        });
    }

    function mailchimpCallback(resp) {
        if (resp.result === 'success') {
            $('#subscribe-result').addClass('subs-result');
            $('.subscription-success').text(resp.msg).fadeIn();
            $('.subscription-error').fadeOut();

        } else if(resp.result === 'error') {
            $('#subscribe-result').addClass('subs-result');
            $('.subscription-error').text(resp.msg).fadeIn();
        }
    }


})(jQuery);



function tabscreateOneMore() {
     if($('#tabscreate ul li').length <=1 ){
        $('#tabscreate ul').hide();
     }else{
        $('#tabscreate ul').show();
     }
     console.log($('#tabscreate ul li').length);
}

function toggler(divId) {
    $("#" + divId).toggle();
}
 $("#myBtn3").click(function(){
	  $(".toggleBalance").toggleClass("newClass"); 
  });
  $("#closeBTN").click(function(){
	  $(".toggleBalance").removeClass("newClass"); 
  });
  