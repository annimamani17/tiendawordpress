jQuery(document).ready(function($) {
    var scroll = $(window).scrollTop();
    var scrollup = $('.scroll-top');
    /*------------------------------------------------
              Scroll Top
    ------------------------------------------------*/
    scrollup.click(function () {
      $('html, body').animate({
        scrollTop: '0px'
      }, 800);
      return false;
    });
    $(window).scroll(function () {
      var scroll = $(window).scrollTop();
      if (scroll >= 200) {
        scrollup.fadeIn();
      } else {
        scrollup.fadeOut();
      }
    });

    /*------------------------------------------------
              Homepage slider
    ------------------------------------------------*/
    var clothing_store_blocks_Slider = new Swiper(".clothing-store-blocks-swiper", {
    slidesPerView: 1,
    speed: 1000,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    pagination: {
      el: ".clothing-store-blocks-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".clothing-store-blocks-swiper-button-next",
      prevEl: ".clothing-store-blocks-swiper-button-prev",
    },
  });

});
