/**
 * User: Sarath TS
 * Date: 16.08.2019
 * Created for: productList
 */

$(function() {
	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	let shopId;
	let companyId;

	// Setting href attribute when changing shop
	$( "#product_shop" ).on( "change", function() {
	    shopId = $( this ).val();
		$( ".getProducts" ).attr( "href", "/admin/dashboard/product/list/"+shopId+"/"+companyId);
	});

	// Setting href attribute when select a company
	$( "#product_company" ).on( "change", function() {
	    companyId = $( this ).val();
		$( ".getProducts" ).attr( "href", "/admin/dashboard/product/list/"+shopId+"/"+companyId);
	});

	$( ".getProducts" ).on( "click", function (e){
		if( ($( "#product_shop" ).val() == '') || ($( "#product_company" ).val() == '') ) {
			e.preventDefault();
			alert('Please fill the data');
		}
	});


});	
