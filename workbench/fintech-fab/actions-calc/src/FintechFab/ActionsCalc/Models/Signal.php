<?php

namespace FintechFab\ActionsCalc\Models;

use Eloquent;

/**
 * @property integer $id
 * @property integer $terminal_id
 * @property string  $name
 * @property string  $signal_sid
 * @property string  $updated_at
 * @property string  $created_at
 *
 * @method static Signal find()
 * @method static Signal links()
 */
class Signal extends Eloquent
{
	protected $table = 'signals';
	protected $connection = 'ff-actions-calc';

	protected $fillable = array('terminal_id', 'name', 'signal_sid');

	public function terminal()
	{
		return $this->belongsTo(Terminal::class);
	}

	public function rules()
	{
		return $this->hasMany(Rule::class);
	}


	public function changeSignal($data)
	{
		$this->name = $data['name'];
		$this->signal_sid = $data['signal_sid'];
		$this->save();
	}

	public function newSignal($data)
	{
		$this->name = $data['name'];
		$this->signal_sid = $data['signal_sid'];
		$this->terminal_id = $data['terminal_id'];
		$this->save();
	}


	public static function getSignalSid()
	{
		$signal = Signal::select('id', 'signal_sid')->get();
		$result = array();
		foreach ($signal as $key => $value) {
			$result[$value->id] = $value->signal_sid;
		}

		return $result;
	}


}