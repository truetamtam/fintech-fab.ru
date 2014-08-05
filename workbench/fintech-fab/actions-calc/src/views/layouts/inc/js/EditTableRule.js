$(document).ready(function () {


	$("input[type=checkbox]").click(function () {
		var id = this.id;
		var val = this.checked;
		$.post('tableRule/changeFlagRule/', {"id": id, "val": val},
			function (res) {
				$("#message").dialog({ title: 'Сообщение', show: 'drop', hide: 'explode' }).html(res);
			});
	});

	$('button.btnEdit').click(function () {
		var $btn = $(this);
		var rule = $btn.data('rule');

		$('button#saveChangeRule').data('id', rule.id);
		$('#myModalLabel').html('Введите новые данные для правила #' + rule.id);

		$('#errorName').empty();
		$('#errorRule').empty();
		$('#errorEventSid').empty();
		$('#errorSignalSid').empty();

		$('#inputEventSid ').val(rule.event_id);
		$('#inputSignalSid').val(rule.signal_id);
		$("#EventSid").find('input').val(rule.event.event_sid);
		$('#SignalSid').find('input').val(rule.signal.signal_sid);


		$('#inputName').val(rule.name);
		$('#inputRule').val(rule.rule);
	});

	$('button.tableAddBtn').click(function () {

		$('#errorName').empty();
		$('#errorRule').empty();
		$('#errorEventSid').empty();
		$('#errorSignalSid').empty();

		$(".EventSid").find('input').val('').attr('placeholder', 'Введите event_sid');
		$(".SignalSid").find('input').val('').attr('placeholder', 'Введите signal_sid');


	});


	$('button#saveChangeRule').click(function () {
		var $btn = $(this);
		var id = $btn.data('id');

		var eventSid = $('#EventSid input').val();
		var signalSid = $('#SignalSid input').val();
		var eventSidId = $('#inputEventSid').val();
		var signalSidId = $('#inputSignalSid').val();
		var name = $('#inputName').val();
		var rule = $('#inputRule').val();
		$('button').attr('disabled', true);
		$.post('tableRules/changeData/', {
				event_id: eventSidId,
				signal_sid: signalSid,
				event_sid: eventSid,
				signal_id: signalSidId,
				rule: rule,
				name: name,
				id: id
			},
			function (data) {
				if (data['errors']) {
					$('button').attr('disabled', false);
					$('#errorName').html(data['errors']['name']);
					$('#errorEventSid').html(data['errors']['event_id']);
					$('#errorRule').html(data['errors']['rule']);
					$('#errorSignalSid').html(data['errors']['signal_id']);
					return;
				}
				location.reload();
			}
		);
	});

	$('button.addDataRuleTable').click(function () {
		var eventSid = $('#inputEventSidAdd').val();
		var signalSid = $('#inputSignalSidAdd').val();
		var name = $('#inputNameAdd').val();
		var rule = $('#inputRuleAdd').val();

		$.post('tableRules/addData/', {
				event_id: eventSid,
				signal_id: signalSid,
				rule: rule,
				name: name
			},
			function (data) {
				if (data['errors']) {
					$('#errorNameAdd').html(data['errors']['name']);
					$('#errorEventSidAdd').html(data['errors']['event_id']);
					$('#errorRuleAdd').html(data['errors']['rule']);
					$('#errorSignalSidAdd').html(data['errors']['signal_id']);
					return;
				}
				location.reload();
			}
		);
	});


	(function ($) {
		$.widget("custom.combobox", {

			_create: function () {
				this.wrapper = $("<span>")
					.addClass("custom-combobox")
					.insertAfter(this.element);

				this.element.hide();
				this._createAutocomplete();
				this._createShowAllButton();
			},

			_createAutocomplete: function () {
				var selected = this.element.children(":selected"),
					value = selected.val() ? selected.text() : "";

				this.input = $("<input>")
					.appendTo(this.wrapper)
					.val(value)
					.attr("title", "")
					.addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left form-control")
					.autocomplete({
						delay: 0,
						minLength: 0,
						source: $.proxy(this, "_source")
					})
					.tooltip({
						tooltipClass: "ui-state-highlight"
					});

				this._on(this.input, {
					autocompleteselect: function (event, ui) {
						ui.item.option.selected = true;
						this._trigger("select", event, {
							item: ui.item.option
						});
					},

					autocompletechange: "_removeIfInvalid"
				});
			},

			_createShowAllButton: function () {
				var input = this.input,
					wasOpen = false;

				$("<a>")
					.attr("tabIndex", -1)
					.attr("title", "Show All Items")
					.tooltip()
					.appendTo(this.wrapper)
					.button({
						icons: {
							primary: "ui-icon-triangle-1-s"
						},
						text: false
					})
					.removeClass("ui-corner-all")
					.addClass("custom-combobox-toggle ui-corner-right")
					.mousedown(function () {
						wasOpen = input.autocomplete("widget").is(":visible");
					})
					.click(function () {
						input.focus();

						// Close if already visible
						if (wasOpen) {
							return;
						}

						// Pass empty string as value to search for, displaying all results
						input.autocomplete("search", "");
					});
			},

			_source: function (request, response) {
				var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
				response(this.element.children("option").map(function () {
					var text = $(this).text();
					if (this.value && ( !request.term || matcher.test(text) ))
						return {
							label: text,
							value: text,
							option: this
						};
				}));
			},

			_removeIfInvalid: function (event, ui) {

				// Selected an item, nothing to do
				if (ui.item) {
					return;
				}

				// Search for a match (case-insensitive)
				var value = this.input.val(),
					valueLowerCase = value.toLowerCase(),
					valid = false;
				this.element.children("option").each(function () {
					if ($(this).text().toLowerCase() === valueLowerCase) {
						this.selected = valid = true;
						return false;
					}
				});

				// Found a match, nothing to do
				if (valid) {
					return;
				}

				// Remove invalid value
				this.input
					.val("")
					.attr("title", value + " didn't match any item")
					.tooltip("open");
				this.element.val("");
				this._delay(function () {
					this.input.tooltip("close").attr("title", "");
				}, 2500);
				this.input.autocomplete("instance").term = "";
			},

			_destroy: function () {
				this.wrapper.remove();
				this.element.show();
			}
		});
	})(jQuery);

	$(function () {
		$('select').combobox();
	});


});


