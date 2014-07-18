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
	'as'   => 'calcRegistration',
	'uses' => 'FintechFab\ActionsCalc\Controllers\AccountController@registration',
));


Route::group(array(
	'before' => 'checkTerm',
	'prefix'    => 'actions-calc',
	'namespace' => 'FintechFab\ActionsCalc\Controllers',
), function () {
	Route::get('editRule', array(
		'as'   => 'calcEditRule',
		'uses' => 'AccountController@editRule',
	));

	Route::get('account', array(
		'as'   => 'calcAccount',
		'uses' => 'AccountController@account',
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
