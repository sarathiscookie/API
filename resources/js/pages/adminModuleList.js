/**
 * User: Sarath TS
 * Date: 17.10.2019
 * Created for: adminModuleList
 */

$(function() {
	"use strict";

	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	/* Datatable scripts */
	let moduleList = $("#module_list").DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [1, "desc"],
		processing: true,
		serverSide: true,
		ajax: {
			url: "/admin/dashboard/module/list/datatables",
			dataType: "json",
			type: "POST"
		},
		deferRender: true,
		columns: [
			{ data: "hash" },
			{ data: "module" },
			{ data: "active" },
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

	/* <tfoot> search functionality */
	$(".search-input").on("keyup change", function() {
		var i = $(this).attr("id"); // getting column index
		var v = $(this).val(); // getting search input value
		moduleList
			.columns(i)
			.search(v)
			.draw();
	});

	/* Create module */
	$("button.createModule").on("click", function(e) {
		e.preventDefault();

		let module_name = $("#module").val();

		$.ajax({
			url: "/admin/dashboard/module/store",
			dataType: "JSON",
			type: "POST",
			data: {
				module: module_name
			}
		})
		.done(function(result) {
			if (result.moduleStatus === "success") {
					$("#createModuleModal").modal("hide"); // It hides the modal

					moduleList.ajax.reload(null, false); //Reload data on table

					$(".responseModuleMessage").html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
						result.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

					$(".responseModuleMessage")
					.show()
					.delay(5000)
					.fadeOut();
				}
		})
		.fail(function(data) {
			if (data.responseJSON.moduleStatus === "failure") {
				$(".moduleValidationAlert").html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
			}

			if (data.status === 422) {
				$.each(data.responseJSON.errors, function(key, val) {
					$(".moduleValidationAlert").html(
						"<p class='alert alert-danger'>" + val + "</p>"
						);
				});
			}
		});
	});

	/* Clearing data of create module modal fields */
	$("#createModuleModal").on("hidden.bs.modal", function(e) {
		
		// On model close, it will hide alert messages. Reason is, it shows default when model opens.
		$( "p .alert, .alert-danger" ).hide();
		$(this)
			.find("input,textarea,select")
			.val("")
			.end()
			.find("input[type=checkbox], input[type=radio]")
			.prop("checked", "")
			.end();
	});

	/* Edit module details */
	$("#module_list tbody").on("click", "a.editModule", function(e) {
		var moduleid = $(this).data("moduleid");

		$(".updateModule_" + moduleid).on("click", function(e) {
			e.preventDefault();

			var module_name = $("#module_" + moduleid).val();

			$.ajax({
				url: "/admin/dashboard/module/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					module: module_name,
					moduleid: moduleid
				}
			})
				.done(function(result) {
					if (result.moduleStatusUpdate === "success") {
						$("#editModuleModal_" + moduleid).modal("hide"); // It hides the modal

						moduleList.ajax.reload(null, false); //Reload data on table

						$(".responseModuleMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseModuleMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.moduleStatusUpdate === "failure") {
						$(".moduleUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$(".moduleUpdateValidationAlert").html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});

	/* module status changing functionality */
	$("#module_list tbody").on("change", "input.buttonStatus", function(e) {
		e.preventDefault();

		var newStatus = "";

		var moduleStatusId = $(this)
			.parent()
			.data("modulestatusid");

		if ($(this).is(":checked") === true) {
			newStatus = "yes";
		} else {
			newStatus = "no";
		}

		$.ajax({
			url: "/admin/dashboard/module/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, moduleStatusId: moduleStatusId }
		})
			.done(function(result) {
				moduleList.ajax.reload(null, false);
			})
			.fail(function(data) {
				moduleList.ajax.reload(null, false);

				if (data.responseJSON.moduleStatusChange === "failure") {
					$(".responseModuleMessage").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				$(".responseModuleMessage")
					.show()
					.delay(5000)
					.fadeOut();
			});
	});

	/* Delete module functionality */
	$("#module_list tbody").on("click", "a.deleteModule", function(e) {
		e.preventDefault();
		var deletemoduleid = $(this).data("deletemoduleid");
		var r = confirm("Are you sure you want to remove the module?");
		if (r == true) {
			$.ajax({
				url: "/admin/dashboard/module/delete/" + deletemoduleid,
				dataType: "JSON",
				type: "DELETE"
			})
			.done(function(result) {
				if (result.deletedModuleStatus === "success") {
						$("#editModuleModal_" + deletemoduleid).modal("hide"); // It hides the modal

						moduleList
						.row($(this).parents("tr"))
						.remove()
						.draw();

						$(".responseModuleMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
							result.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
							);

						$(".responseModuleMessage")
						.show()
						.delay(5000)
						.fadeOut();
					}
			})
			.fail(function(data) {
				if (data.responseJSON.deletedModuleStatus === "failure") {
					$(".moduleUpdateValidationAlert").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
						data.responseJSON.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
				}
			});
		}
	});


});	