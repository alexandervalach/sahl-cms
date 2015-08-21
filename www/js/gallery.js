$(function () {
    
    var options = {
        image: $('.animate'),
        fadeSpeed: 500
    }

    var img = options.image;
    img.css({opacity: 0.6});

    img.mouseenter(function () {
        $(this).not(':animated').animate({opacity: 1}, options.fadeSpeed);
    });

    img.mouseleave(function () {
        $(this).animate({opacity: 0.6}, options.fadeSpeed);
    });
})