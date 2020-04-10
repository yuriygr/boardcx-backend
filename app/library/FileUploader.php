<?php

namespace Phalcon;

class FileUploader
{
	private function sendFile($file_data)
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.imgur.com/3/image",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => [
				'image' => $file_data
			],
			CURLOPT_HTTPHEADER => [
				"Authorization: Client-ID 1da99cd90edd4fc",
				"content-type: multipart/form-data;"
			]
		]);

		$response = curl_exec($curl);
		$response = json_decode($response);
		$err = curl_error($curl);

		curl_close($curl);

		return [$response, $err];
	}


	public function upload($file)
	{
		throw new \Phalcon\Exception('On next time');

		$file_data = file_get_contents($file->getTempName());
		if (!$file_data) {
			throw new \Phalcon\Exception('Cannot fine tmp file');
		}

		list($response, $err) = $this->sendFile($file_data);

		if ($err)
			throw new \Phalcon\Exception("cURL Error #:" . $err);

		if ($response->success === false)
			throw new \Phalcon\Exception($response->data->error);

		$files = new \Files();
		$files->slug 		= $response->data->id;
		$files->provider 	= 'imgur.com';
		$files->type 		= explode('/', $response->data->type)[1];
		$files->size 		= $response->data->size;
		$files->o_width 	= $response->data->width;
		$files->o_height 	= $response->data->height;
		$files->t_width 	= '160';
		$files->t_height 	= '160';

		return $files;
	}
}