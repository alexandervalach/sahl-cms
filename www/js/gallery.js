$(function () {
    var options = {
        image: $('.animate'),
        fadeSpeed: 500
    }

    screenWidth = $('body').innerWidth();
    console.log(screenWidth);

    var portrait = [];
    var figure;
    var section;

    if (screenWidth >= 974) {
        var img = options.image;
        img.each(function () {
            var width = $(this).width();
            var height = $(this).height();
            if (height > width) {
                // $(this).height(0.75 * width);
                figure = $(this).parent().parent();
                section = figure.parent();
                portrait.push( figure.html() );
                figure.detach();
                console.log(figure);
            }
        });
        
        console.log(section);

        $.each(portrait, function ( index, value ) {
            section.append( value );
            console.log( index + ": " + value );
        });

        img.css({opacity: 0.6});

        img.mouseenter(function () {
            $(this).not(':animated').animate({opacity: 1}, options.fadeSpeed);
        });
        img.mouseleave(function () {
            $(this).animate({opacity: 0.6}, options.fadeSpeed);
        });
    }
})