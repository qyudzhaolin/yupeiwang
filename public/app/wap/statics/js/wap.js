j(function(){
    if (j('.home-hot').length) {
        var swiper1 = new Swiper('.home-hot', {
            slidesPerView: 3,
            spaceBetween: 30,
            slidesPerGroup: 3,
            loop: true,
            loopFillGroupWithBlank: true
          });
    }

    if (j('.J_ad_slider').length) {
        j(function(){
            var mySwiper = new Swiper ('.J_ad_slider', {
                loop: true,
                pagination: {
                el: '.swiper-pagination'
                }
            });
        });
    }
});