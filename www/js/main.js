$(function () {
    $.nette.init();
});

$(function() {
    var scroll_btn = $('#scroll-to-top');
    var body = $('html, body');

    show_scroll_btn(body, scroll_btn);
    scrol_to_top(body, scroll_btn);
});

function scrol_to_top(body, scroll_btn) {

    scroll_btn.bind('click', function() {
        body.animate({ scrollTop: 0}, 'fast');
    });
}

function show_scroll_btn(body, scroll_btn) {

    $(window).scroll( function() {
        if (body.scrollTop() > 20) {
            scroll_btn.fadeIn();
        } else {
            scroll_btn.fadeOut();
        }
    });

}