// jQuery news slider on Hompage:default

$(function() {

	var slider = $('#slider');
	var para = slider.find('p');
	var img = slider.find('img');
	var paraHeight = para.height();
	
	para.bind('mouseover', function() {
		var src = $(this).attr('data-src');

		img.not(":animated").animate({ opacity: 0 }, 250, function() {
			img.attr('src', src);
			img.animate({ opacity: 1}, 250);
		});
	});

	para.bind('mouseenter', function() {
		var item = $(this).not(":animated");
		item.animate({height : paraHeight + 10}, 250)
		
	}).bind('mouseleave', function() {
		$(this).animate({height : paraHeight }, 250);	
	})

});