<?php
Route::get('/', array('as' => 'index', 'uses' => 'App\Controllers\Site\MainController@index'));
Route::get('vanguard', array('as' => 'vanguard', 'uses' => 'App\Controllers\Site\VanguardController@vanguard'));
Route::post('vanguard', array('as' => 'vanguard', 'uses' => 'App\Controllers\Site\VanguardController@postOrder'));

Route::get('notices', array(
	'before' => 'testRole:messageSender',
	'as' => 'notices',
	'uses' => 'App\Controllers\Site\NoticesController@notices')
);

Route::post('auth', array('as' => 'auth', 'uses' => 'App\Controllers\Site\AuthController@postAuth'));
Route::post('registration', array(
	'as'   => 'registration',
	'uses' => 'App\Controllers\Site\AuthController@postRegistration'
));
Route::get('registration', array(
	'before' => 'guest',
	'as'     => 'registration',
	'uses'   => 'App\Controllers\Site\AuthController@registration'
));

Route::get('logout', array('as' => 'logout', 'uses' => 'App\Controllers\Site\AuthController@logout'));

Route::get('vk', 'App\Controllers\Site\AuthController@socialNet');
Route::get('fb', 'App\Controllers\Site\AuthController@socialNet');

Route::get('admin', array(
	'before' => 'auth|roleAdmin',
	'as'     => 'admin',
	'uses'   => 'App\Controllers\User\UserProfileController@showAdmin'
));

Route::get('TableForAdmin', array(
	'as'   => 'WorkAdmin',
	'uses' => 'App\Controllers\User\AdminController@TableForRoles'
));

Route::get('changeRole', array(
	'as'   => 'changeRole',
	'uses' => 'App\Controllers\User\AdminController@changeRole'
));

Route::group(array('before' => 'auth'), function () {
	Route::get('profile', array(
		'as'   => 'profile',
		'uses' => 'App\Controllers\User\UserProfileController@showUserProfile'
	));
	Route::post('upload/image', array(
		'as'   => 'upload/image',
		'uses' => 'App\Controllers\User\DownloadController@uploadImage',
	));
	Route::get('widgets/getPhoto', array(
		'as'   => 'widgets/getPhoto',
		'uses' => 'App\Controllers\User\UserProfileController@getPhoto',
	));
});
