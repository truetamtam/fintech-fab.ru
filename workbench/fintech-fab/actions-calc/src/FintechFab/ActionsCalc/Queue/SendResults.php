<?php

namespace FintechFab\ActionsCalc\Queue;

use Illuminate\Queue\Jobs\Job;
use Log;
use Queue;

class SendResults
{

	public function fire(Job $job, $data)
	{

		$ch = curl_init($data['url']);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ['signalSid' => $data['signalSid']]);

		$response = curl_exec($ch);
		$error = curl_error($ch);

		if (!$response || $error) {
			Log::info("Curl error: $error, response: $response");
		} else {
			Log::info("Curl success: $error, response: $response");
			$job->delete();
		} // TODO: address flag to send by url to true.

	}

	public function requestToQueue($url, $queue, $signalSid)
	{
		Queue::connection('ff-actions-calc')->push('FintechFab\ActionsCalc\Queue\SendResults', array(
			'url'       => $url,
			'queue'     => $queue,
			'signalSid' => $signalSid,
		));

		Log::info('Результат поставлен в очередь, класс для выполнения FintechFab\\ActionsCalc\\Queue\\QueueHandler');
	}

}