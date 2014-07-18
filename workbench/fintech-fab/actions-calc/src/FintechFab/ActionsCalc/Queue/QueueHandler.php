<?php

namespace FintechFab\ActionsCalc\Queue;


use Illuminate\Queue\Jobs\Job;

class QueueHandler
{
	//	public function fire(Job $job, $data) {
	//
	//		$ch = curl_init($data['url']);
	//		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//		curl_setopt($ch, CURLOPT_POST, true);
	//		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	//
	//		$response = curl_exec($ch);
	//		$error = curl_error($ch);
	//
	//		if(!$response || $error) {
	//			Log::info("Curl error: $error, response: $response");
	//		} else {
	//			Log::info("Curl success: $error, response: $response");
	//			$job->delete();
	//		}
	//
	//	}

	public function fire(Job $job, array $data)
	{
		$job->delete();
		dd($data);
	}

} 