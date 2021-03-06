<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use FintechFab\Components\Role;
use FintechFab\Models\User;
use Input;

class AdminController extends BaseController
{

	public function TableForRoles()
	{
		$count = User::all()->count();
		$x = null;
		if (!$count) {
			$x = "В Базе данных еще нет пользователей";
		} else {
			$users = User::all();
			foreach ($users as $user) {
				$x[] = array(
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
					'admin'      => Role::userRole((int)$user->id, "admin"),
					'moderator'  => Role::userRole((int)$user->id, "moderator"),
					'user'       => Role::userRole((int)$user->id, "user"),
				);
			}
		}

		return $x;
	}

	public function changeRole()
	{
		$userN = Input::get('userN');
		$roleN = Input::get('roleN');
		$val = Input::get('val');
		$user = User::find($userN);

		if ($val == "true") {
			$user->roles()->attach($roleN);
		} else {
			$user->roles()->detach($roleN);
		}

		$res = "Изменения произошли для пользователя с порядковым номером  $userN";

		return $res;
	}


} 