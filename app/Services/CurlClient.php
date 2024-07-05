<?php

namespace App\Services;

class CurlClient
{
	private $baseUri;
	private $headers;

	public function __construct($configs)
	{
		$this->baseUri = rtrim($configs['base_uri'], '/') . '/';
		$this->headers = $configs['headers'];
	}

	private function sendRequest($method, $uri, $options = [])
	{
		$ch = curl_init();

		$url = $this->baseUri . ltrim($uri, '/');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

		if (isset($options['headers'])) {
			$headers = array_merge($this->headers, $options['headers']);
		} else {
			$headers = $this->headers;
		}

		$curlHeaders = [];
		foreach ($headers as $key => $value) {
			$curlHeaders[] = "$key: $value";
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

		if (isset($options['form_params'])) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options['form_params']));
		} elseif (isset($options['body'])) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
		}

		$response = curl_exec($ch);
		$err = curl_error($ch);
		curl_close($ch);

		if ($err) {
			throw new \Exception('cURL Error: ' . $err);
		}

		return json_decode($response, true);
	}

	public function get($uri, $options = [])
	{
		return $this->sendRequest('GET', $uri, $options);
	}

	public function post($uri, $options = [])
	{
		return $this->sendRequest('POST', $uri, $options);
	}

	// You can add other methods (PUT, DELETE, etc.) if needed
}
