<?php

class SessionController extends ControllerBase
{
	public function get()
	{	
		$res = [
			'response' => "Ok"
		];
		$this->response->setHeader('x-session', $this->session->getId());
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}
}