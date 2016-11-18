/*
$(function () {
    var options = {
        class: '.animate',
        image: $('.animate'),
        fadeSpeed: 100,
        screenWidth: $('body').innerWidth()
    }

    var portrait = [];
    var figure;
    var section;
    var img = options.image;
    img.css({opacity: 0.6});

    if (options.screenWidth >= 974) {
        img.each(function () {
            var width = $(this).width();
            var height = $(this).height();
            if (height > width) {
                // $(this).height(0.75 * width);
                figure = $(this).parent().parent();
                section = figure.parent();
                portrait.push(figure.html());
                figure.detach();
                console.log(figure);
            }
        });

        $(document).ready(function () {
            section = $('#portrait');
            $.each(portrait, function (index, value) {
                var figure = '<figure class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' + value + '</figure>';
                section.append(figure);
                console.log(index + ": " + value);
            });
        }
        );

        $(document).on('mouseleave', options.class, function () {
            $(this).animate({opacity: 0.6}, options.fadeSpeed);
        });

        $(document).on('mouseenter', options.class, function () {
            $(this).not(':animated').animate({opacity: 1}, options.fadeSpeed);
        });
    }
})
*/
$(document).ready(function() {
    $('.lightbox-trigger').on('click', function(e) {
        e.preventDefault();
        var image_href = $(this).attr("href");
        var len = $('#lightbox').length;

        if (len > 0) {
            $('#picture').html('<img src="' + image_href + '"/>');
            $('#lightbox').show();
        } else {
            var lightbox = 
                '<div id="lightbox">' + 
                    '<p>Zavri</p>' +
                    '<div id="picture">'
                        '<img src="' + image_href + '"/>' +
                    '</div>' + 
                '</div>';
            $('body').append(lightbox);
        }

        $('#lightbox').on('click', function() {
            $('#lightbox').hide();
        });
    });    
});