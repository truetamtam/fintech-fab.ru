/**
 * Web interface. Main actions-calc "manage" file.
 * Added in views/calculator/manage.php.
 *
 * @author Ulashev Roman <truetamtam@gmail.com>
 */
// global handlers
$(document).ajaxError(function (e, xhr) {

	var oResponseText = JSON.parse(xhr.responseText);

	// catching script message
	if (oResponseText.message != undefined) {
		toastr.error(oResponseText.message)
	}
	// catching exception messages
	if (oResponseText.error != undefined) {
		if (oResponseText.error.message != undefined && oResponseText.error.message.length > 0) {
			toastr.error(oResponseText.error.message);
		}
		if (oResponseText.error.type != undefined) {
			toastr.error(oResponseText.error.type);
		}
	}
}).ajaxSuccess(function (e, xhr, opt, oData) {
	if (oData.message != undefined) {
		toastr.success(oData.message);
	}
});


$(document).ready(function () {

	var $body = $('body');
	var $eventsContainer = $('#events-table-container');
	var $signalsContainer = $('#signals-container');

	// datatable
	var $signalsTable = $('#manage-signals').DataTable({
		aaSorting: [],
		language: {
			"sProcessing": "Подождите...",
			"sLengthMenu": "Показать _MENU_ записей",
			"sZeroRecords": "Записи отсутствуют.",
			"sInfo": "Записи с _START_ до _END_ из _TOTAL_ записей",
			"sInfoEmpty": "Записи с 0 до 0 из 0 записей",
			"sInfoFiltered": "(отфильтровано из _MAX_ записей)",
			"sInfoPostFix": "",
			"sSearch": "Поиск: ",
			"sUrl": "",
			"oPaginate": {
				"sFirst": "Первая",
				"sPrevious": "Предыдущая",
				"sNext": "Следующая",
				"sLast": "Последняя"
			},
			"oAria": {
				"sSortAscending": ": активировать для сортировки столбца по возрастанию",
				"sSortDescending": ": активировать для сортировки столбцов по убыванию"
			}
		}
	});
	var oButtons = {
		edit: '<ul class="signal-buttons button-group right">' +
		'<li><button class="tiny button signal-edit">&nbsp;<i class="fi-page-edit"></i></button></li>' +
		'<li><button class="tiny button alert signal-delete">&nbsp;<i class="fi-x"></i></button></li>' +
		'</ul>'
	};
	var oSignalRow = {
		DT_RowId: null,
		0: '',
		1: '',
		2: oButtons.edit
	};

	// events:
	// events table pagination
	// pagination through ajax
	$body.on('click', 'ul.pagination a', function (e) {
		e.preventDefault();

		var $eventTableContainer = $('#events-table-container');
		loadingUp($eventsContainer);

		$.get($(this).attr('href'),
			function (oData) {
				$eventTableContainer.empty();
				$eventTableContainer.append(oData);
			},
			'html'
		).always(function () {
				loadingDown($eventsContainer);
			});

		return false;
	});

	// events:
	// modal event create
	$body.on('click', '#button-event-create', function (e) {
		e.preventDefault();

		// $th button event create
		var $button = $(this);
		var $form = $button.closest('form');

		buttonSleep($button);

		$.post('/actions-calc/event/create',
			$form.serialize(),
			function (oData) {
				if (oData.status == 'success') {
					$('#modal-event-create').foundation('reveal', 'close');
					updateEventsTable();
					clearFormErrors($form);
				}
				return false;
			},
			'json'
		).error(function (xhr) {
				revealFormErrors($form, JSON.parse(xhr.responseText).errors);
			})
			.always(function () {
				buttonWakeUp($button);
			});

		return false;
	});
	// events:
	// event update modal - open
	$body.on('click', 'button.edit-rule', function () {

		var $th = $(this);
		var $eventId = $th.closest('tr').data('id');

		buttonSleep($th);
		loadingUp($eventsContainer);

		$.get('/actions-calc/event/update/' + $eventId,
			function (oData) {
				// showing update form
				$('#modal-update-event').html(oData).foundation('reveal', 'open');
				loadingUp($eventsContainer);
			},
			'html'
		).always(function () {
				buttonWakeUp($th);
				loadingDown($eventsContainer);
			});
	});
	// events:
	// event update modal - update
	$body.on('click', '#button-event-update', function (e) {
		e.preventDefault();

		var $th = $(this);
		var $eventId = $th.closest('form').data('id');

		buttonSleep($th);

		$.post(
			'/actions-calc/event/update/' + $eventId,
			$th.closest('form').serialize(),
			function (oData) {
				if (oData.status == 'success') {
					$('#modal-update-event').foundation('reveal', 'close');
					updateRuleRow(oData.update);
					clearFormErrors($th);
				}
			},
			'json'
		).error(function (xhr) {
				revealFormErrors($th.closest('form'), JSON.parse(xhr.responseText).errors);
			})
			.always(function () {
				buttonWakeUp($th);
			});

		return false;
	});
	// events:
	// modal event delete // TODO: count event on every deletion, if less than 10, update events table.
	$body.on('click', '#events-rules button.delete-rule', function () {
		// $th clicked delete button
		var $button = $(this);
		buttonSleep($button);
		loadingUp($eventsContainer);

		var $thisRow = $button.closest('tr');
		var $eventId = $thisRow.data('id');
		var $nextRow = $thisRow.next('tr.event-rules-row');
		var $nextRowId = $nextRow.data('event-rules');

		$.post('/actions-calc/event/delete',
			{id: $eventId},
			function (oData) {
				if (oData.status == 'success') {
					// deleted, removing table records and opened rules, if exists
					if ($nextRowId == $eventId) {
						$nextRow.fadeOut();
					}
					$thisRow.fadeOut();
				}
				return false;
			},
			'json'
		).always(function () {
				loadingDown($eventsContainer);
				buttonWakeUp($button);
			});

		return false;
	});

	// events:
	// events search
	$body.on('click', 'button#search-event', function () {

		var $button = $(this);
		var sSearchQuery = $('input#search-event-text').val();

		loadingUp($eventsContainer);

		buttonSleep($button);
		$.get(
			'/actions-calc/event/search?q=' + sSearchQuery,
			function (oData) {

				$eventsContainer.html(oData);

				// showing revert to get back to normal events showing
				$('#search-revert').fadeIn(50);
			},
			'html'
		).always(function () {
				loadingDown($eventsContainer);
				buttonWakeUp($button);
			}).fail(function (xhr) {
				alert(xhr.responseText);
			});

		return false;
	});
	// events:
	// events search revert button
	// hides search table, reveals opened earlier
	$body.on('click', 'button#search-revert', function () {

		var $button = $(this);

		buttonSleep($button);

		updateEventsTable();

		buttonWakeUp($button);

		// hiding search revert button
		$button.hide();

		return false;
	});
	// events:
	// search field keydown activate search on [enter]
	$body.on('keydown', 'input#search-event-text', function (e) {

		var $input = $(this);
		var sQuery = $input.val();

		// hit [enter]
		if (e.keyCode == 13) {
			console.log(sQuery);
			console.log(sQuery.length);
			if (sQuery.length < 2 || sQuery === undefined) {
				toastr.info('2 буквы хотя бы.');
				return false;
			}

			$('button#search-event').trigger('click');

			return false;
		}
	});

	// event -> rules:
	// toggle event rules flag
	$body.on('click', '#event-rules-wrap div.switch label', function () {
		var $iRuleId = $(this).closest('tr').data('id');
		var sFlagState = $(this).prev('input:checked').val() === undefined ? 'on' : 'off';

		$.ajax({
			type: 'POST',
			url: '/actions-calc/manage/toggle-rule-flag',
			data: {id: $iRuleId, flag_active: sFlagState},
			beforeSend: loadingUp($eventsContainer),
			dataType: 'json'
		}).always(function () {
			loadingDown($eventsContainer);
		});

	});
	// events -> rules:
	// see event rules
	$body.on('click', 'button.see-rules', function () {
		// $th clicked button "see rules"
		var $th = $(this);
		var $parentTr = $th.closest('tr');
		var iTdCount = $parentTr.children('td').length;

		// no rules = no moves, also not doing anything while button disabled
		if ($th.data('rules-count') == 0 || buttonBusy($th)) {
			return false;
		}

		// opening event rules
		// loading rules makes sense once
		if ($th.hasClass('rules-loaded') == false) {
			// disabling button and preventing click while loading rules
			loadingUp($eventsContainer);
			buttonSleep($th);
			// ajax sending
			$.post('/actions-calc/manage/get-event-rules',
				{event_id: $parentTr.data('id')},
				function (oData) { // success function

					// placing event rules in ceparate table, and showing
					$("<tr class='event-rules-row' data-event-rules=" + $parentTr.data('id') + ">" +
					"<td colspan=" + iTdCount + ">" + oData + "</td></tr>").insertAfter($parentTr);

					$th.addClass('rules-loaded');
					// make visible "close" button and hiding self
					$th.hide();
					$th.next('button.close-rules').show();
				},
				'html'
			).always(function () {
					loadingDown($eventsContainer);
					buttonWakeUp($th);
				});
		} else {
			// if rules loaded, just showing them and toggling see-rules\close-rules buttons
			$th.hide();
			$th.next('button.close-rules').show();
			$(this).closest('tr').next('tr').show();
		}

		return false;
	});

	// events -> rules:
	// [Rule Create] - open
	$body.on('click', 'button.rule-create', function () {

		var $th = $(this);
		var $modalRuleCreate = $('#modal-rule-create');
		var $toEventId = $th.closest('tr').data('id');

		buttonSleep($th);
		loadingUp($eventsContainer);

		$.get(
			'/actions-calc/rule/create',
			$th.closest('form').serialize(),
			function (oData) {
				$modalRuleCreate.html(oData);
				// event id to hidden input
				$modalRuleCreate.find('input[name="event_id"]').val($toEventId);
				$modalRuleCreate.find('select.s2').select2();
				$modalRuleCreate.foundation('reveal', 'open');
			},
			'html'
		).always(function () {
				buttonWakeUp($th);
				loadingDown($eventsContainer);
			});

		return false;
	});
	// events -> rules:
	// [Rule Create] - create
	// forming JSON string from rule conditions
	$body.on('click', '#button-rule-create', function () {

		var $submit = $(this);
		var $modalRuleCreate = $('#modal-rule-create');
		// finding container with rule conditions
		var $rulesContainer = $modalRuleCreate.find('.event-rules-translate');
		var aoRuleData = [];
		var $aRules = $rulesContainer.find('div.event-rule');

		// forming condition objects
		$.each($aRules, function (index, rule) {

			var sRuleOperator = $(rule).find('select.event-rule-operator > option:selected').val();
			// checking if different input types input|select
			var ruleValue = (sRuleOperator == 'OP_BOOL') ? $(rule).find('select.condition-bool > option:selected').val() :
				$(rule).find('input.event-rule-value').val();

			ruleValue = typeFromString(ruleValue);
			ruleValue = (ruleValue === undefined || ruleValue == "undefined") ? !!ruleValue : ruleValue;

			aoRuleData.push({
				name: $(rule).find('input.event-rule-name').val(),
				value: ruleValue,
				operator: sRuleOperator
			});
		});

		var sRules = JSON.stringify(aoRuleData);

		// updating hidden input with rules
		$submit.closest('form').find('input[name="rule"]').val(sRules);

		// update rule request
		buttonSleep($submit);

		$.post(
			'/actions-calc/rule/create',
			$submit.closest('form').serialize(),
			function (oData) {
				if (oData.status == 'success') {
					updRulesCountFromEvent(oData);
					// finding rule_id passed to #modal-rule-create
					var $ruleId = $('#modal-rule-create').find('input[name="event_id"]').val();
					updateEventRules($ruleId, $('#events-table-container'));
					$modalRuleCreate.foundation('reveal', 'close');
				}
			},
			'json'
		).always(function () {
				buttonWakeUp($submit);
			}).fail(function (xhr) {
				revealFormErrors($submit.closest('form'), JSON.parse(xhr.responseText).errors);
			});

		return false;
	});
	// events -> rules:
	// [Rule Update] - open
	$body.on('click', 'button.rule-update', function () {

		var $th = $(this);
		var $ruleId = $th.closest('tr').data('id');
		var $modalRuleUpdate = $('#modal-rule-update');

		buttonSleep($th);
		loadingUp($eventsContainer);

		$.get(
			'/actions-calc/rule/update/' + $ruleId,
			$th.closest('form').serialize(),
			function (oData) {
				$modalRuleUpdate.html(oData);
				$modalRuleUpdate.find('select.s2').select2();
				$modalRuleUpdate.foundation('reveal', 'open');

				var $sRule = $modalRuleUpdate.find('input[name="rule"]').val();
				var rulesFactory = new RulesFactory();
				rulesFactory.formFromJson($sRule, $modalRuleUpdate.find('.event-rules-translate'));
			},
			'html'
		).always(function () {
				loadingDown($eventsContainer);
				buttonWakeUp($th);
			});

		return false;
	});
	// events -> rules:
	// [Rule Update] - update
	// forming JSON string from rule conditions
	$body.on('click', '#button-rule-update', function (e) {
		e.preventDefault();

		// finding container with rule conditions
		var $th = $(this);
		var $rulesContainer = $th.closest('form').find('div.event-rules-translate');
		var aoRuleData = [];
		var $aRules = $rulesContainer.find('div.event-rule');

		// forming condition objects
		$.each($aRules, function (index, rule) {

			var sRuleOperator = $(rule).find('select.event-rule-operator > option:selected').val();
			// checking if different input types input|select
			var ruleValue = (sRuleOperator == 'OP_BOOL') ? $(rule).find('select.condition-bool > option:selected').val() :
				$(rule).find('input.event-rule-value').val();

			ruleValue = typeFromString(ruleValue);
			ruleValue = (ruleValue === undefined || ruleValue == "undefined") ? !!ruleValue : ruleValue;

			aoRuleData.push({
				name: $(rule).find('input.event-rule-name').val(),
				value: ruleValue,
				operator: sRuleOperator
			});
		});

		var sRules = JSON.stringify(aoRuleData);

		// updating hidden input with rules
		$th.closest('form').find('input[name="rule"]').val(sRules);

		var $ruleId = $th.closest('form').data('id');

		// update rule request
		buttonSleep($th);

		$.post(
			'/actions-calc/rule/update/' + $ruleId,
			$th.closest('form').serialize(),
			function (oData) {
				if (oData.status == 'success') {
					updateEventRules($ruleId, $('#events-table-container'));
					$('#modal-rule-update').foundation('reveal', 'close');
				}
			},
			'json'
		).always(function () {
				buttonWakeUp($th);
			}).error(function (xhr) {
				revealFormErrors($th.closest('form'), JSON.parse(xhr.responseText).errors);
			});

		return false;
	});
	// events -> rules:
	// rule delete button - delete
	$body.on('click', 'button.rule-delete', function () {

		// $th clicked delete button
		var $delButton = $(this);

		buttonSleep($delButton);
		loadingUp($eventsContainer);

		var $thisRow = $delButton.closest('tr');
		var $ruleId = $thisRow.data('id');

		$.post('/actions-calc/rule/delete/' + $ruleId,
			function (oData) {
				if (oData.status == 'success') { // success
					// update event -> rules button counter
					updRulesCountFromRules($delButton, oData);
					// deleted, removing table records and opened rules, if exists
					$thisRow.fadeOut();
					// closing rules table if 0 rules
					if (oData.data.count <= 0) {
						var $parentEventRow = $delButton.parents('tr.event-rules-row');
						var iParentEventId = $parentEventRow.data('event-rules');
						$parentEventRow.prev('tr[data-id=' + iParentEventId + ']').find('button.close-rules').click();
					}
				}
				return false;
			},
			'json'
		).always(function () {
				loadingDown($eventsContainer);
				buttonWakeUp($delButton);
			});

		return false;
	});
	// events -> rules:
	// close rules button
	$body.on('click', 'button.close-rules', function () {
		$(this).hide();
		$(this).prev('button.see-rules').show();
		$(this).closest('tr').next('tr').hide();
		return false;
	});

	// signals
	// signal store
	$body.on('click', '#button-signal-create', function () {

		var $button = $(this);
		var $form = $button.closest('form');

		buttonSleep($button);

		$.post(
			'/signal',
			$button.closest('form').serialize(),
			function (oData) {
				if (oData.status == 'success') {

					clearFormErrors($form);
					$('#modal-signal-create').foundation('reveal', 'close');

					// new row to datatable
					oSignalRow.DT_RowId = oData.data.id;
					oSignalRow[0] = oData.data.signal_sid;
					oSignalRow[1] = oData.data.name;

					$signalsTable.row.add(oSignalRow).draw().node();
				}
			},
			'json'
		).always(function () {
				buttonWakeUp($button);
			}).fail(function (xhr) {
				revealFormErrors($form, JSON.parse(xhr.responseText).errors);
			});

		return false;
	});
	// signal update - open form
	$body.on('click', '.signal-edit', function () {

		var $button = $(this);
		var iSignalId = +$button.closest('tr').attr('id');

		buttonSleep($button);
		loadingUp($signalsContainer);

		$.get(
			'/signal/' + iSignalId + '/edit',
			function (oData) {
				var $modalUpdate = $('#modal-signal-udpate');
				$modalUpdate.html(oData);
				$modalUpdate.foundation('reveal', 'open');
			},
			'html'
		).always(function () {
				buttonWakeUp($button);
				loadingDown($signalsContainer);
			}).fail(function (xhr) {
				toastr.error(xhr.responseText);
			});

		return false;
	});
	// signal delete
	$body.on('click', '.signal-delete', function () {

		var $button = $(this);
		var iSignalId = +$button.closest('tr').attr('id');

		loadingUp($signalsContainer);
		buttonSleep($button);

		$.ajax({
			method: 'DELETE',
			url: '/signal/' + iSignalId,
			success: function (oData) {
				if (oData.status == 'success') {
					$button.closest('tr[id=' + iSignalId + ']').fadeOut();
				}
			},
			dataType: 'json'
		}).always(function () {
			buttonWakeUp($button);
			loadingDown($signalsContainer);
		});

		return false;
	});
	// signal update - put signal
	$body.on('click', '#signal-update-button', function (e) {
		e.preventDefault();

		var $button = $(this);
		var $form = $button.closest('form');
		var iSignalId = $form.data('id');

		buttonSleep($button);

		$.ajax({
			method: 'PUT',
			url: '/signal/' + iSignalId,
			data: $form.serialize(),
			success: function (oData) {
				if (oData.status == 'success') {
					$('#modal-signal-udpate').foundation('reveal', 'close');
					clearFormErrors($form);

					// updating signals table
					oSignalRow[0] = oData.data.signal_sid;
					oSignalRow[1] = oData.data.name;
					$signalsTable.row('[id=' + iSignalId + ']').data(oSignalRow).draw();
				}
			},
			dataType: 'json'
		}).always(function () {
			buttonWakeUp($button);
		}).fail(function (xhr) {
			revealFormErrors($form, JSON.parse(xhr.responseText).errors);
		});

		return false;
	});

	// auth
	// auth profile - [open]
	$body.on('click', '#auth-profile', function () {

		var $button = $(this);
		var $profileModal = $('#modal-auth-profile');

		buttonSleep($button);

		$.ajax({
			url: '/actions-calc/profile',
			success: function (oData) {
				$profileModal.html(oData);
				$profileModal.foundation('reveal', 'open');
			},
			dataType: 'html'
		}).always(function () {
			buttonWakeUp($button);
		});

	});
	// auth
	// auth profile - [update]
	$body.on('click', '#button-profile-update', function () {

		var $button = $(this);
		var $profileModal = $('#modal-auth-profile');

		buttonSleep($button);

		$.ajax({
			method: 'POST',
			url: '/actions-calc/profile',
			data: $button.closest('form').serialize(),
			success: function (oData) {
				$profileModal.html(oData);
			},
			dataType: 'html'
		}).always(function () {
			buttonWakeUp($button);
		});

		return false;
	});

	$body.on('change', '#profile-password-change-trigger', function () {
		$('#profile-password-change-container').toggle();
	});

	// for RulesFactory // TODO: bring handlers below, to rulesFactory.
	// operator and bool condition attribute change,
	// and input type change on OP_BOOL
	$body.on('change', '.event-rule-operator, .condition-bool', function () {
		var $th = $(this);
		// toggle selected option.
		$th.find('option[selected]').removeAttr('selected');
		$th.find('option[value=' + $th.val() + ']').attr('selected', 'selected');

		if ($th.hasClass('event-rule-operator')) {
			var $template = $('#event-rules-template');

			// switching bool and input[type=text]
			if ($th.find('option:selected').val() == 'OP_BOOL') {
				var $conditionBool = $template.find('select.condition-bool').clone();
				$conditionBool.find('option:first').attr('selected', 'selected');
				$th.parents('div.event-rule').find('input.event-rule-value').replaceWith($conditionBool);
			} else {
				var $conditionValue = $template.find('input.event-rule-value').clone();
				$th.parents('div.event-rule').find('select.condition-bool').replaceWith($conditionValue);
			}
		}
	});

	// event -> rules -> conditions:
	// delete condition
	$body.on('click', '.event-rules-translate a.delete-event-rule', function (e) {
		e.preventDefault();
		$(this).closest('div.event-rule').remove();
		return false;
	});
	// event -> rules -> conditions:
	// [Condition Add] for rule
	$body.on('click', 'button.event-rule-add-condition', function () {

		var $template = $('#event-rules-template');
		var $ruleCondition = $template.find('div.event-rule').clone();
		// making first option selected
		$ruleCondition.find('select.event-rule-operator > option').eq(1).attr('selected', 'selected');
		var $resultHtml = $('<div>', {'class': 'row'}).append($ruleCondition);
		// finding rules-translate container
		// and pushing new condition
		$(this).parent().prev('fieldset').find('.event-rules-translate').append($resultHtml);

		return false;
	});

});

// Rules factory class
// needs template
// container :: rules container :: input\selects
// input\selects model:
// - 1st [key]-[operator^}-[value],
// - 2nd [rule_operator]-[key]-[operator^}-[value]
RulesFactory = function () {

	var $template = $('#event-rules-template');

	// forming event rules[] to editable inputs
	// placing inside form
	this.formFromJson = function ($sRule, selector) {

		var $oRule = $.parseJSON($sRule);

		$.each($oRule, function (index, rule) {
			var $conditionsHtml = $template.find('div.event-rule').clone();

			$conditionsHtml.find('input[name="event-rule-name"]').val(rule.name);
			if (rule.operator == 'OP_BOOL') {
				// putting switch trigger
				var $selectBool = $template.find('select.condition-bool').clone();
				$selectBool.find('option[value=' + rule.value + ']').attr('selected', 'selected');
				$conditionsHtml.find('input.event-rule-value').replaceWith($selectBool);
			} else {
				// common text input
				$conditionsHtml.find('input[name="event-rule-value"]').val(rule.value);
			}
			$conditionsHtml.find('option[value=' + rule.operator + ']').attr('selected', 'selected');

			var $resultHtml = $('<div>', {'class': 'row'}).append($conditionsHtml);
			$resultHtml.appendTo(selector.closest('.event-rules-translate'));
		});
	};

	this.clear = function () {
		$('body').off('change', '.event-rule-operator');
	};

	this.settings = {};

};

/**
 * Update events table
 *
 * @returns {boolean}
 */
function updateEventsTable() {
	var $eventTableContainer = $('#events-table-container');

	var iPage = $('#pagination-events-current-page').text();

	loadingUp($eventTableContainer);

	$.get('/actions-calc/events/table?page=' + iPage,
		function (oData) {
			$eventTableContainer.html(oData);
		},
		'html'
	).always(function () {
			loadingDown($eventTableContainer);
		});

	return false;
}

/**
 * Put event rules in container
 *
 * @param ruleId
 * @param $eventsContainer
 */
function updateEventRules(ruleId, $eventsContainer) {
	$.post(
		'/actions-calc/manage/get-event-rules',
		{event_id: ruleId},
		function (oData) {
			var $eventRulesTable = $eventsContainer.find('tr[data-event-rules=' + ruleId + ']');

			if ($eventRulesTable !== undefined) {
				$eventRulesTable.find('#event-rules-wrap').replaceWith(oData);
			}
		},
		'html'
	).fail(function (xhr) {
			alert(xhr.responseText);
		});

	return false;
}

/**
 * Update event name and event_sid on update
 * @param oData
 */
function updateRuleRow(oData) {
	var $row = $('tr[data-id="' + oData.id + '"]');
	$row.find('td.event-name').html(oData.name);
	$row.find('td.event-sid').html(oData.event_sid);
}

/**
 * Reveal errors in a form, if validation on server fails
 *
 * @param form
 * @param errors
 */
function revealFormErrors(form, errors) {
	clearFormErrors(form);
	$.each(errors, function (sName) {
		form.find('label[for="' + sName + '"]').addClass('error');
		form.find('input[name="' + sName + '"]').addClass('error');
		form.find('small[id="' + sName + '-error"]').addClass('error');
	});
}

/**
 * Clear form errors
 *
 * @param form
 */
function clearFormErrors(form) {
	form.find('label').removeClass('error');
	form.find('input').removeClass('error');
	form.find('small').removeClass('error');
}

/**
 * Button becomes available again
 *
 * @param button
 */
function buttonWakeUp(button) {
	button.removeAttr('disabled');
	$(document).off('click', button);
}

/**
 * Blocking button
 *
 * @param button
 */
function buttonSleep(button) {
	button.attr('disabled', 'disabled');
	$(document).on('click', button, function () {
		var bIsButtonDisabled = !!button.attr('disabled');
		return !bIsButtonDisabled;
	});
}

/**
 * Evaluate, if is button available
 *
 * @param button
 * @returns {boolean}
 */
function buttonBusy(button) {
	return !!button.attr('disabled');
}

/**
 * When forming rule JSON from inputs
 * returning right type
 *
 * @param string
 * @returns {*}
 */
function typeFromString(string) {

	if (string === 'true') {
		return true;
	} else if (string === 'false') {
		return false;
	} else if (isNaN(parseFloat(string))) {
		return string;
	} else {
		return parseFloat(string);
	}
}

/**
 * Update rules counter inside see-rules button, from rules table
 *
 * @param $button
 * @param oData
 */
function updRulesCountFromRules($button, oData) {
	// finding parent row with button, comparing id's
	var $rulesTableRow = $button.closest('tr.event-rules-row');
	var $eventId = $rulesTableRow.data('event-rules');
	var $buttonSeeRules = $rulesTableRow.prev('tr[data-id=' + $eventId + ']').find('button.see-rules');

	if (oData.data.count < 1) {
		$buttonSeeRules.addClass('disabled');
	} else {
		$buttonSeeRules.removeClass('disabled');
	}

	$buttonSeeRules.data('rules-count', oData.data.count);
	$buttonSeeRules.find('span').text(oData.data.count);
}

/**
 * Update rules counter inside see-rules button, from event
 *
 * @param oData
 */
function updRulesCountFromEvent(oData) {
	var iEventId = $('#modal-rule-create').find('input[name="event_id"]').val();
	var $ruleRow = $('#events-table-container:not(.in-modal)').find('tbody tr[data-id=' + iEventId + ']');
	var $seeRules = $ruleRow.find('button.see-rules');

	if (oData.data.count < 1) {
		$seeRules.addClass('disabled');
	} else {
		$seeRules.removeClass('disabled');
	}

	$seeRules.data('rules-count', oData.data.count);
	$seeRules.find('span').text(oData.data.count);
}

/**
 * Bluring and blocking container on some action.
 */
function loadingUp($container) {
	console.log('loadingUp');
	$container.prepend($('<div class="table-loading"></div>'));
}

/**
 * Deblocking container on some action.
 */
function loadingDown($container) {
	console.log('loadingDown');
	$container.find('div.table-loading').remove();
}