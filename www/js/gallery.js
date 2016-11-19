$(document).ready(function() {
    $('.lightbox-trigger').on('click', function(e) {
        e.preventDefault();
        var imgHref = $(this).attr('href');
        var len = $('#lightbox').length;
        var total = $('.photos').children().length;
        var parent = $('.photos a[href="' + imgHref + '"]').parent();
        var index = parent.index() + 1;

        if (len > 0) {
            $('#picture img').attr('src', imgHref);
            $('#metadata').text(index + " / " + total);
            $('#lightbox').show();
        } else {
            var lightbox = 
                '<div id="lightbox">' + 
                    '<p id="close">' +
                        '<span class="glyphicon glyphicon-remove"></span>' + 
                    '</p>' +
                    '<div id="show-prev" class="col-xs-1 col-sm-1 col-md-1 col-lg-2">' +
                        '<span class="glyphicon glyphicon-menu-left"></span>' +
                    '</div>' +
                    '<div id="picture" class="col-xs-10 col-sm-10 col-md-10 col-lg-8">' +
                        '<img src="' + imgHref + '"/>' +
                    '</div>' +
                    '<div id="show-next" class="col-xs-1 col-sm-1 col-md-1 col-lg-2">' +
                        '<span class="glyphicon glyphicon-menu-right"></span>' +
                    '</div>' +
                    '<div id="metadata" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">' + index + ' / ' + total + '</div>'
                '</div>';
            $('body').append(lightbox);
        }

        $('#close').bind('click', function() {
            $('#lightbox').hide();
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
                prev = $('.photos').find('figure:last-child a').attr('href');
                index = total;
            } else {
                index = prevParent.index() + 1;
            }
            console.log(i);
            console.log(index);
            $('#metadata').text(index + " / " + total);
            $('#picture img').attr('src', prev);
        });

        $('#show-next').bind('click', function() {
            var imgHref = $('#picture img').attr('src');
            var parent = $('a[href="' + imgHref + '"]').parent();
            var nextParent = parent.next();
            var next = nextParent.find('a').attr('href');

            if (typeof next == "undefined") {
                next = $('.photos').find('figure:first-child a').attr('href');
                index = 1;
            } else {
                index = nextParent.index() + 1;
            }
            console.log(index);
            $('#metadata').text(index + " / " + total);
            $('#picture img').attr('src', next);
        });
    });    
});