<?php

class BansController extends ControllerBase
{
	public function check()
	{
		$res = [
			'response' => 'OKoss'
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}
}