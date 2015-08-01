$(function() {
	var options = {

		image : $('.img-gallery'),
		fadeSpeed : 100

	}

	var img = options.image;
	img.css({ opacity: 0.5});
	
	img.mouseenter(function (){
		$(this).animate({ opacity : 1 }, options.fadeSpeed);
	});

	img.mouseleave(function(){
		$(this).animate({ opacity: 0.5 }, options.fadeSpeed);
	});
})