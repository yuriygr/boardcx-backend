<?php

class SettingsController extends ControllerBase
{
	public function export()
	{
		$res = [
			'response' => 'OKs'
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function import()
	{
		$res = [
			'response' => 'OKs'
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}
}