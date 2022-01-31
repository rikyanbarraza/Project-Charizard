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
		}, 1000 ); // 1000 står för milisekunder aka 1 min
	});
} );

// ----------------uppdatera varukorg med en knapptryckning------------


// -----------------------------cookie banner--------------------------
if(localStorage.getItem('cookieSeen') != 'shown'){
    $(".cookie-banner").delay(2000).fadeIn();
    localStorage.setItem('cookieSeen','shown')
}

$('.close').click(function(e) {
  $('.cookie-banner').fadeOut(); 
});