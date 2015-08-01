$(function()
{
	/*
	 *  do pola pridame adresy obrazkov	
	 *	postupne ich budeme vyberat a vytvorime novy div s background-image s touto adresou
	 *  tento pridame do html a zobrazime plynule ho zobrazime - prekryje ten povodny
	 *  ak uz nemame co vyberat, mame vsetky obrazky na stranke, budeme ich zobrazovat v cykle dokola
	 *  viditelnost robime cez opacity
	 */
	var options = {
		
		// src adresy pre obrazky, sem pridavat nove
		pics :[	
			'/sahl/sandbox/www/images/slider/img01.jpg',
			'/sahl/sandbox/www/images/slider/img08.jpg',
			'/sahl/sandbox/www/images/slider/img05.jpg',
			'/sahl/sandbox/www/images/slider/img06.jpg',
			'/sahl/sandbox/www/images/slider/img07.jpg',
			'/sahl/sandbox/www/images/slider/img09.jpg'
		],
		
		// po akom case sa ma prehodit na druhy obrazok, v milisekudach
		switchTimeout: 4000,
		
		// rychlost prechodu (fade) na novy obrazok, v milisekundach
		fadeSpeed: 1200,
		
		// element, v ktorom budeme menit backgrounds
		switcher: $('#switcher')
		
	};
	
	/* ------------------- LET'S GO ------------------- */

	var switcher = options.switcher,						
		firstPicSrc = options.pics[0],	// cesta k prvemu obrazku
		count = options.pics.length,					// pocet obrazkov, pouzije sa neskor v cykle menenia..
		i = 0,											// iterator.. 
		imgs;											// obrazky, ktore budeme menit
		
	// shuffle obrazkov pola, aby sa menili nahodne, a odstranenie prvotneho obrazku z pola, nech tam neni 2x
	options.pics = $.grep(shuffle(options.pics), function(src) {
		return (firstPicSrc.indexOf(src)  < 0);
	});
	
	// v html mame 1 div z background obrazkom, zmenime ho na wrapper, do ktoreho budeme pridavat dalsie obrazky
	switcher.html( $('<img/>').attr( 'src', firstPicSrc ).addClass('img-responsive') );
	
	// lets go
	setInterval(function() 
	{	
		// ak este mame v options.pics poli obrazky, pridame ich do html
		if (options.pics.length)
		{
			var newSrc = options.pics.shift(),			// cesta k novemu obrazku, array.shift() ho vyhodi z pola obrazkov.. 
				newImg = $('<img/>', { src: newSrc })	// vytvorime novy image, kvoli cache noveho obrazku 
				
			// load zaruci, ze to co je vo vnutri sa spusti, az ked bude cely obrazok nacitany
			newImg.load(function()
			{
				$('<img/>').attr( 'src', newSrc )
						   .css({ opacity: 0 })
						   .addClass( 'img-responsive' )
						   .appendTo(switcher)
						   .animate({ opacity: 1 }, options.fadeSpeed);
			});
		}
		else
		{
			// vsetky obrazky uz boli nacitane, teraz ich budeme len dokola vymienat.. pomocou opacity
			if (!imgs) imgs = switcher.children();
			
			if (i === 0)
			{
				imgs.eq(0).animate({opacity: 1}, options.fadeSpeed);
				imgs.not(':last-child').css({opacity: 0});
				imgs.not(':eq(0)').animate({ opacity: 0 }, options.fadeSpeed);
				i++;
			}
			else
			{
				imgs.eq(i).animate({ opacity: 1 }, options.fadeSpeed);					
				if (++i >= count) i = 0;
			}
		}
		
	}, options.switchTimeout);

});

// shuffle array funkcia
function shuffle(v) {
	for(var j, x, i = v.length; i; j = parseInt(Math.random() * i), x = v[--i], v[i] = v[j], v[j] = x);
	return v;
};