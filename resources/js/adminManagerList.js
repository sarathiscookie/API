/**
 * User: Sarath TS
 * Date: 04.08.2019
 * Created for: adminManagerList
 */

$(function() {
	"use strict";

	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	/* Passing url to datatable */
	const url = "/admin/dashboard/manager/list/datatables";
	fetchData(url);

	/* <tfoot> search functionality */
	$(".search-input").on("keyup change", function() {
		var i = $(this).attr("id"); // getting column index
		var v = $(this).val(); // getting search input value
		booking_data
			.columns(i)
			.search(v)
			.draw();
	});
});
