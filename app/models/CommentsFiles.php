<?php

class CommentsFiles extends \Phalcon\Mvc\Model
{
	public $id;

	public $comments_id;

	public $files_id;

	public function initialize()
	{
		$this->belongsTo(
			'comments_id', 'TopicsComments', 'id', [
				'alias' => 'Comments',
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
