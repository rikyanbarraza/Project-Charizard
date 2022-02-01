// --------------- uppdatera varukorg med en knapptryckning------------

jQuery( function( $ ) {
	$('.woocommerce').on('change', 'input.qty', function(){
		$("[name='update_cart']").trigger("click");
	});
} );

var timeout;

jQuery( function( $ ) {
	$('.woocommerce').on('change', 'input.qty', function(){

		if ( timeout !== undefined ) {
			clearTimeout( timeout );
		}

		timeout = setTimeout(function() {
			$("[name='update_cart']").trigger("click");
		}, 1000 ); // 1 second delay, half a second (500) seems comfortable too

	});
} );

// --------------- uppdatera varukorg med en knapptryckning------------

// if(localStorage.getItem('cookieSeen') != 'shown'){
//     $(".cookie-banner").delay(2000).fadeIn();
//     localStorage.setItem('cookieSeen','shown')
// }

// $('.close').click(function(e) {
//   $('.cookie-banner').fadeOut(); 
// });