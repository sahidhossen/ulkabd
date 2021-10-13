/**
 * Created by sahidhossen on 4/1/17.
 */

function parallax(parallaxID, speed, that) {
    var scrolTop = window.pageYOffset,
        current_window_offset = $("#" + parallaxID).offset().top + 250,
        actualScroll = current_window_offset - $(window).height(),
        yPos = scrolTop - actualScroll,
        banner = document.getElementById(parallaxID);
    if (scrolTop > actualScroll) {
        banner.style.top = yPos * speed + 'px';
    }
}

jQuery(document).ready(function(){

    var deviceWidth = $(window).width();
    console.debug(deviceWidth);
    $(window).scroll(function(){
        var sticky = $('.navbar-default'),
            scroll = $(window).scrollTop();

        if (scroll >= 690) sticky.addClass('navbar-fixed-top');
        else sticky.removeClass('navbar-fixed-top');

        if(deviceWidth>768) {
            window.addEventListener('scroll', function () {
                var yPos = window.pageYOffset,
                    banner = document.getElementById('usha_agent');
                banner.style.top = yPos * 0.2 + 'px';
            }, false);
        }

    });
})