<?php

class TopicsFiles extends \Phalcon\Mvc\Model
{
	public $id;

	public $topics_id;

	public $files_id;

	public function initialize()
	{
		$this->belongsTo(
			'topics_id', 'Topics', 'id', [
				'alias' => 'Topics',
				'foreignKey' => true
			]
		);

		$this->belongsTo(
			'files_id', 'Files', 'id', [
				'alias' => 'Files',
				'foreignKey' => true
			]
		);
	}
}
