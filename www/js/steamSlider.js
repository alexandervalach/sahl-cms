$(function() {
	$('.img-gallery').click(function() {
		var src = $(this).attr('src');

		$('#current').attr('src', src);
		//console.log(src);
	});
});