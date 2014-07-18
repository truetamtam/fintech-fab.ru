<?php

namespace FintechFab\ActionsCalc\Components;


use Log;
use Queue;
use Illuminate\Queue\Jobs\Job;

class SendResults
{

	public function fire(Job $job, $data) {

		$ch = curl_init($data['url']);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$response = curl_exec($ch);
		$error = curl_error($ch);

		if(!$response || $error) {
			Log::info("Curl error: $error, response: $response");
		} else {
			Log::info("Curl success: $error, response: $response");
			$job->delete();
		}

	}

	public function makeCurl($url, $signalSid)
	{
		$postData = array('signalSid' => $signalSid);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		$httpResponse = curl_exec($ch);
		$httpError = curl_error($ch);

		if (!$httpResponse || $httpError) {
			Log::info("Ошибка CURL. httpResponse = $httpResponse , httpError = $httpError");
		} else {
			Log::info("CURL успешно отработал.  httpResponse = $httpResponse , httpError = $httpError");
		}

	}

	public function sendQueue($queue, $signalSid)
	{
		Queue::connection('ff-actions-calc')->push('FintechFab\ActionsCalc\Components\SendResults', [
			'url' => $queue,
			'signal' => $signalSid
		]);

		Log::info('Результат поставлен в очередь, класс для выполнения FintechFab\\ActionsCalc\\Components\\SendResults');

//		Queue::connection('ff-actions-calc')->push('FintechFab\ActionsCalc\Queue\QueueHandler', array(
//			'url'       => $queue,
//			'signalSid' => $signalSid,
//		));
//
//		Log::info('Результат поставлен в очередь, класс для выполнения FintechFab\\ActionsCalc\\Queue\\QueueHandler');
	}

}