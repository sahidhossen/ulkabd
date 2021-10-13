/**
 * Created by sahidhossen on 5/21/17.
 */
(function ( $ ) {
    "use strict"

    $.fn.ulkaModal = function( options ) {

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            color: "#556b2f",
            backgroundColor: "white",
            closeBtn : '.modalClose'
        }, options );

        let elm = this;

        $(this).openModal();

        $(settings.closeBtn).on('click', function(e){
            e.preventDefault();
            elm.find("div.modalContent").fadeOut(300);

            elm.hide();
        })

        return this;

    };

    $.fn.createModal =function(){
        // Create modalContent dom element and grab all of the code
        if(!this.find("div.modalContent").length){
            var modalContent = document.createElement('div');
            modalContent.className = "modalContent";
            var modalElements = this.html();
            modalContent.innerHTML = modalElements;
            this.html(modalContent);
        }
    }

    $.fn.closeModal = function(options) {
        // Close popup code.
        var settings = $.extend({
           closeBtn : '.modalClose'
        }, options );
        this.find("div.modalContent").fadeOut();
        this.hide();

    };

    $.fn.openModal = function(options) {
        // Close popup code.

        var settings = $.extend({

        }, options );

        $(this).createModal();

        let elm = this;
        let modalContent = elm.find("div.modalContent");

        elm.css({'display':'block'});

        setTimeout(function(){
            modalContent.fadeIn();
        },100)

    };

}( jQuery ));

$(function(){
    $(".showModal").on('click', function(e){
        e.preventDefault(0);
        $("#facebookSignInModal").ulkaModal();
    })
})