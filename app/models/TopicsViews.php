<?php

class TopicsViews extends \Phalcon\Mvc\Model
{
	public $id;

	public $topics_id;

	public $timestamp;

	public $userIp;

	public function initialize()
	{
		$this->belongsTo(
			'topics_id', 'Topics', 'id', [
				'alias' => 'Topics'
			]
		);
	}
}
