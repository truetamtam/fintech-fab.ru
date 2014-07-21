<?php
Route::post('actions-calc/getRequest', array(
	'as'   => 'getRequest',
	'uses' => 'FintechFab\ActionsCalc\Controllers\RequestController@getRequest'
));

Route::get('actions-calc/about', array(
	'as'   => 'calcAbout',
	'uses' => 'FintechFab\ActionsCalc\Controllers\AccountController@about',
));

Route::get('actions-calc/registration', array(
	'before' => 'calcNotRegistered',
	'as'     => 'calcRegistration',
	'uses'   => 'FintechFab\ActionsCalc\Controllers\AccountController@registration',
));

Route::group(array(
	'before'    => 'calcRegistered',
	'prefix'    => 'actions-calc',
	'namespace' => 'FintechFab\ActionsCalc\Controllers',
), function () {
	Route::get('account', array(
		'as'   => 'calcAccount',
		'uses' => 'AccountController@account',
	));
	Route::get('tableRules', array(
		'as'   => 'calcTableRules',
		'uses' => 'TablesController@tableRules',
	));
	Route::get('tableEvents', array(
		'as'   => 'calcTableEvents',
		'uses' => 'TablesController@tableEvents',
	));
	Route::get('tableSignals', array(
		'as'   => 'calcTableSignals',
		'uses' => 'TablesController@tableSignals',
	));
});

Route::post('actions-calc/account/newTerminal', array(
	'as'   => 'newTerminal',
	'uses' => 'FintechFab\ActionsCalc\Controllers\AccountController@postNewTerminal',
));

Route::post('actions-calc/account/changeData', array(
	'as'   => 'changeDataCalc',
	'uses' => 'FintechFab\ActionsCalc\Controllers\AccountController@postChangeData',
));
