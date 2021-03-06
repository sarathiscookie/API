/**
 * User: Sarath TS
 * Date: 02.12.2019
 * Created for: orderList
 */

$(function () {
	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	let orderList = '';
	let orderCompany = '';
	let orderListDateRange = '';
	let downloadAllInvoiceDiv = $(".downloadAllInvoiceDiv");

	// When page loads download button hide by default 
	downloadAllInvoiceDiv.hide();

	// On page load this function works
	orderDatatableFunc(orderCompany, orderListDateRange);

	/* Datatable script */
	function orderDatatableFunc(orderCompany, orderListDateRange) {
		orderList = $("#order_list").DataTable({
			lengthMenu: [20, 50, 100],
			order: [1, "desc"],
			processing: true,
			serverSide: true,
			ajax: {
				url: "/admin/dashboard/order/list/datatables",
				dataType: "json",
				type: "POST",
				data: {
					orderCompany: orderCompany,
					orderListDateRange: orderListDateRange,
					pageActive: function () {
						let orderListTableInfo = $("#order_list")
							.DataTable()
							.page.info();
						return orderListTableInfo.page + 1;
					}
				}
			},
			drawCallback: function (data) {
				// If total records greater than zero then download invoices button shows.
				if (data._iRecordsTotal > 0) {

					// Array of order numbers
					let orderNoArr = $(".orderNoInput").map(function () {
						return this.value;
					}).get();

					let inputOrderCompanyId = $("#orderCompany").val();

					downloadAllInvoiceDiv.show();

					$("#inputOrderCompanyId").attr("value", inputOrderCompanyId);
					$("#inputOrderNoArr").attr("value", orderNoArr);
					$("#inputOrderDateRange").attr("value", orderListDateRange);
					$("#orderListPages").attr("value", data.json.pages);
					$("#orderListPerPage").attr("value", data.json.per_page);
					$("#orderListTotal").attr("value", data._iRecordsTotal);
				}
				else {
					downloadAllInvoiceDiv.hide();
				}
			},
			deferRender: true,
			columns: [
				{ data: "hash" },
				{ data: "order" },
				{ data: "status" },
				{ data: "actions" }
			],
			columnDefs: [
				{
					orderable: false,
					targets: [0, 2, 3]
				}
			],
			language: {
				sEmptyTable: "Keine Daten in der Tabelle vorhanden",
				sInfo: "_START_ bis _END_ von _TOTAL_ Einträgen",
				sInfoEmpty: "0 bis 0 von 0 Einträgen",
				sInfoFiltered: "(gefiltert von _MAX_ Einträgen)",
				sInfoPostFix: "",
				sInfoThousands: ".",
				sLengthMenu: "_MENU_ Einträge anzeigen",
				sLoadingRecords: "Wird geladen...",
				sProcessing: "Bitte warten...",
				sSearch: "Suchen",
				sZeroRecords: "Keine Einträge vorhanden.",
				oPaginate: {
					sFirst: "Erste",
					sPrevious: "Zurück",
					sNext: "Nächste",
					sLast: "Letzte"
				},
				oAria: {
					sSortAscending:
						": aktivieren, um Spalte aufsteigend zu sortieren",
					sSortDescending:
						": aktivieren, um Spalte absteigend zu sortieren"
				}
			}
		});
	}

	/* <tfoot> search functionality */
	$(".search-input").on("keyup change", function () {
		let i = $(this).attr("id");  // getting column index
		let v = $(this).val();  // getting search input value
		orderList.columns(i).search(v).draw();
	});

	/* Date range script */
	$("#orderListDateRange").daterangepicker({
		autoUpdateInput: false,
		ranges: {
			'Letzten 7 Tage': [moment().subtract(7, 'days'), moment()],
			'Letzten 30 Tage': [moment().subtract(30, 'days'), moment()],
			'Dieser Monat': [moment().startOf('month'), moment().endOf('month')],
			'Letzter Monat': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		},
		locale: {
			format: 'DD.MM.YYYY',
			applyLabel: "Bestätigen",
			cancelLabel: "Löschen",
			daysOfWeek: [
				"So",
				"Mo",
				"Di",
				"Mi",
				"Do",
				"Fr",
				"Sa"
			],
		}
	});

	$("#orderListDateRange").on("apply.daterangepicker", function (ev, picker) {
		$(this).val(picker.startDate.format('DD.MM.YYYY') + '-' + picker.endDate.format('DD.MM.YYYY'));
	});

	$("#orderListDateRange").on("cancel.daterangepicker", function (ev, picker) {
		let data = $(this).val("");
		orderList.destroy();
		orderDatatableFunc(orderCompany, orderListDateRange);
	});

	/* Generate order list */
	$("#generateOrders").on("click", function (e) {
		e.preventDefault();

		orderCompany = $("#orderCompany").val();
		orderListDateRange = $("#orderListDateRange").val();

		if (orderCompany !== '' && orderListDateRange !== '') {
			orderList.destroy();
			orderDatatableFunc(orderCompany, orderListDateRange);
		}
		else {
			$('.alertMsg').html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Leere Felder bitte ausfüllen</div>');
		}
	});

});	