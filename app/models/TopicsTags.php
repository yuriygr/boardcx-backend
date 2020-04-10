<?php

class TopicsTags extends \Phalcon\Mvc\Model
{
	public $id;

	public $topics_id;

	public $tags_id;

	public function initialize()
	{
		$this->belongsTo(
			'topics_id', 'Topics', 'id', [
				'alias' => 'Topics',
				'foreignKey' => true
			]
		);

		$this->belongsTo(
			'tags_id', 'Tags', 'id', [
				'alias' => 'Tags',
				'foreignKey' => true
			]
		);
	}
}
