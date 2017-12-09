$(document).ready(function() {
    $('.lightbox-trigger').on('click', function(e) {
        e.preventDefault();
        var imgHref = $(this).attr('href');
        var len = $('#lightbox').length;
        var total = $('section').children('figure').length - 3;
        var parent = $('section figure a[href="' + imgHref + '"]').parent();
        var index = parent.index() + 1;

        if (len > 0) {
            $('#picture img').attr('src', imgHref);
            $('#metadata').text(index + " / " + total);
            $('#lightbox').fadeIn(500);
        }

        $('#close').bind('click', function() {
            $('#lightbox').fadeOut(500);
            $('#show-prev').unbind('click');
            $('#show-next').unbind('click');
        });

        $('#show-prev').bind('click', function() {
            var imgHref = $('#picture img').attr('src');
            var parent = $('a[href="' + imgHref + '"]').parent();
            var prevParent = parent.prev();
            var prev = prevParent.find('a').attr('href');
            var i = 0;

            if (typeof prev == "undefined") {
                prev = $('section').find('figure:last-child a').attr('href');
                index = total;
            } else {
                index = prevParent.index() + 1;
            }

            $('#metadata').text(index + " / " + total);
            $('#picture img').fadeOut(300, function() { 
                $(this).attr('src', prev);
                $(this).fadeIn(300);
            });
        });

        $('#show-next').bind('click', function() {
            var imgHref = $('#picture img').attr('src');
            var parent = $('a[href="' + imgHref + '"]').parent();
            var nextParent = parent.next();
            var next = nextParent.find('a').attr('href');

            if (typeof next == "undefined") {
                next = $('section').find('figure:first-child a').attr('href');
                index = 1;
            } else {
                index = nextParent.index() + 1;
            }

            $('#metadata').text(index + " / " + total);
            $('#picture img').fadeOut(300, function() {
                $(this).attr('src', next);
                $(this).fadeIn(300);
            });
        });
    });    
});