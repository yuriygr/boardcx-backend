<?php

class TopicsLogs extends \Phalcon\Mvc\Model
{
	public $id;

	public $topics_id;

	public $type;

	public $info;

	public $timestamp;

	public function initialize()
	{
		$this->belongsTo(
			'topics_id', 'Topics', 'id', [
				'alias' => 'Topics'
			]
		);
	}

	public function toApi() : array
	{
		return [
			'id' 			=> (int) $this->id,
			'topics_id' 	=> (int) $this->topics_id,
			'type' 			=> (string) $this->type,
			'info' 			=> (string) $this->info,
			'timestamp' 	=> (int) $this->timestamp
		];
	}
}
