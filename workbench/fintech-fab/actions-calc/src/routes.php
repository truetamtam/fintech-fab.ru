<?php
/**
 * File routes.php
 *
 * @author Ulashev Roman <truetamtam@gmail.com>
 */

// main entry point
use FintechFab\ActionsCalc\Components\AuthHandler;
use FintechFab\ActionsCalc\Models\Rule;
use FintechFab\ActionsCalc\Models\Signal;
use FintechFab\ActionsCalc\Models\Event;

Route::post('actions-calc', [
	'as'   => 'getRequest',
	'uses' => 'FintechFab\ActionsCalc\Controllers\RequestController@getRequest',
]);

// auth registration
Route::get('/actions-calc/registration', [
	'as' => 'auth.registration', function () {

		if (AuthHandler::isTerminalRegistered()) {
			return Redirect::route('calc.manage');
		}

		return View::make('ff-actions-calc::layouts.main')
			->nest('content', 'ff-actions-calc::auth.registration', ['terminal_id' => AuthHandler::getTerminalId()]);
	}
]);
Route::post('/actions-calc/registration', [
	'uses' => 'FintechFab\ActionsCalc\Controllers\AuthController@registration'
]);

Route::group(['before' => 'ff-actions-calc.auth'], function () {

	// signals
	// signal edit
	Route::get('/signal/{id}/edit', function ($id) {
		$signal = Signal::find($id);

		return View::make('ff-actions-calc::signal.edit', compact('signal'));
	});
	// signal resources
	Route::resource('signal', 'FintechFab\ActionsCalc\Controllers\SignalController');

	// events
	// delete event
	Route::post('/actions-calc/event/delete', [
		'as'   => 'event.delete',
		'uses' => 'FintechFab\ActionsCalc\Controllers\EventController@delete'
	]);
	// event update, open
	Route::get('/actions-calc/event/update/{id}', function ($id) {
		$event = Event::find($id);

		return View::make('ff-actions-calc::event.update', ['event' => $event]);
	});
	// event update
	Route::match(['POST', 'GET'], '/actions-calc/event/update/{id?}', [
		'as'   => 'event.update',
		'uses' => 'FintechFab\ActionsCalc\Controllers\EventController@update',
	])->where('id', '[0-9]+');
	// create event
	Route::post('/actions-calc/event/create', [
		'as'   => 'event.create',
		'uses' => 'FintechFab\ActionsCalc\Controllers\EventController@create'
	]);
	// events table pagination
	Route::get('/actions-calc/events/table{page?}', [
		'uses' => 'FintechFab\ActionsCalc\Controllers\EventController@updateEventsTable',
	]);
	// events search
	Route::get('actions-calc/event/search', [
		'uses' => 'FintechFab\ActionsCalc\Controllers\EventController@search',
	]);

	// events -> rules:
	// get events rules
	Route::post('/actions-calc/manage/get-event-rules', [
		'uses' => 'FintechFab\ActionsCalc\Controllers\ManageController@getEventRules'
	]);
	// toggle events rules flag
	Route::post('/actions-calc/manage/toggle-rule-flag', [
		'uses' => 'FintechFab\ActionsCalc\Controllers\ManageController@toggleRuleFlag'
	]);
	// event -> rules:
	// rule create
	Route::get('/actions-calc/rule/create', function () {
		$signals = Signal::whereTerminalId(AuthHandler::getTerminalId())->get(['id', 'name', 'signal_sid']);

		return View::make('ff-actions-calc::rule.create', compact('signals'));
	});
	Route::post('/actions-calc/rule/create', [
		'as'   => 'rule.create',
		'uses' => 'FintechFab\ActionsCalc\Controllers\RuleController@create',
	]);
	// event -> rules:
	// rule update
	Route::get('/actions-calc/rule/update/{id}', function ($id) {
		$oRule = Rule::find($id);
		$aoSignals = Signal::whereTerminalId(AuthHandler::getTerminalId())->get(['id', 'name', 'signal_sid']);

		return View::make('ff-actions-calc::rule.update', ['rule' => $oRule, 'signals' => $aoSignals]);
	});
	// event -> rules:
	// rule update
	Route::post('/actions-calc/rule/update/{id?}', [
		'as'   => 'rule.update',
		'uses' => 'FintechFab\ActionsCalc\Controllers\RuleController@update',
	])->where('id', '[0-9]+');
	// event -> rules:
	// rule delete
	Route::post('/actions-calc/rule/delete/{id}', [
		'as'   => 'rule.delete',
		'uses' => 'FintechFab\ActionsCalc\Controllers\RuleController@delete'
	])->where('id', '[0-9]+');

	// managing all records
	Route::get('/actions-calc/manage', [
		'as'   => 'calc.manage',
		'uses' => 'FintechFab\ActionsCalc\Controllers\ManageController@manage'
	]);

	// auth profile
	Route::any('/actions-calc/profile', [
		'as'   => 'auth.profile',
		'uses' => 'FintechFab\ActionsCalc\Controllers\AuthController@profile'
	]);

});
