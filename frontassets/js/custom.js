      
/*fix_header_js*/
$(window).scroll(function() {
    if ($(this).scrollTop() > 250){  
        $('.fix_top_header').addClass("sticky");
    }
    else{
        $('.fix_top_header').removeClass("sticky");
    }
    
});
/*fix_header_js*/

/*choose_section_js*/
$('.feedback_slider').slick({
  dots: false,
  infinite: false,
  speed: 300,
  slidesToShow: 3,
  slidesToScroll:1,
  arrows:false,
  responsive: [
    {
      breakpoint: 1024,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
        infinite: true,
        dots: false
      }
    },
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    }
  ]
});
/*choose_section_js*/
