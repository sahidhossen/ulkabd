;$(function() {
    // page is now ready, initialize the calendar...
    //var d = new Date();

    //alert(d.getDate());

    var $scheduleTitle = "Schedule1";
    var $scheduleTime = "02:04 PM";
    var $previewHtml = "";
    var $cards = [
        { src: "http://localhost/ulkabot/public/images/product-images/1001.jpg"},
        { src: "http://localhost/ulkabot/public/images/product-images/1002.jpg"},
        { src: "http://localhost/ulkabot/public/images/product-images/1003.jpg"},
        { src: "http://localhost/ulkabot/public/images/product-images/1004.jpg"},
        { src: "http://localhost/ulkabot/public/images/product-images/1005.jpg"},
        { src: "http://localhost/ulkabot/public/images/product-images/1006.jpg"},
        { src: "http://localhost/ulkabot/public/images/product-images/1007.jpg"},
        { src: "http://localhost/ulkabot/public/images/product-images/1008.jpg"},
        { src: "http://localhost/ulkabot/public/images/product-images/1009.jpg"}
    ];

    var m = $('#calendar').fullCalendar({
        // put your options and callbacks here
        height: 410,

        events: [
            {
                start: '2017-03-10T10:00:00',
                end: '2017-03-10T16:00:00',
                //rendering: 'background'
            }
        ],
        dayClick: function(date, jsEvent, view) {

//                    alert('Clicked on: ' + date.format());
//
//                    alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
//
//                    alert('Current view: ' + view.name);

            $('.broadcast-day').html(date.format('dddd'));
            $('.broadcast-title').html($scheduleTitle);

            $(".broadcast-preview").html("");//.css('display','none');
            $previewContainer = "<div class='preview-container' style='display:none'></div>";
            for( i=0; i< $cards.length; i++){
                $previewHtml += "<div class='col-md-4 col-sm-4'><div class='preview-item thumbnail'><img src='"+$cards[i].src+"' alt='card"+i+"'></div></div>";

            };

            $($previewContainer).appendTo('.broadcast-preview').fadeIn('slow');

            $('.preview-container').html($previewHtml, 500);

            $previewHtml = "";
            // change the day's abackground color just for fun
            $('.clicked-schedule').removeClass('clicked-schedule');
            $(this).addClass('clicked-schedule');

        }
    });

});