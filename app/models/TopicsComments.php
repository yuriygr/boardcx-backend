<?php

class TopicsComments extends \Phalcon\Mvc\Model
{
	public $id;

	public $topics_id;

	public $type;

	public $timestamp;

	public $message;

	public $isBanned;

	public $isPinned;

	public $isDeleted;

	public $userIp;

	public $hash;

	public $parent;

	public function initialize()
	{
		$this->belongsTo(
			'topics_id', 'Topics', 'id', [
				'alias' => 'Topics'
			]
		);
		$this->belongsTo(
			'parent', 'TopicComments', 'id', [
				'alias' => 'Parent'
			]
		);
		$this->hasManyToMany(
			'id', 'CommentsFiles', 'comments_id',
			'files_id', 'Files', 'id',
			[
				'alias' => 'Files',
				'foreignKey' => true
			]
		);
		$this->hasMany(
			'id', 'TopicsComments', 'parent',
			[
				'alias' => 'Comments',
				'foreignKey' => true
			]
		);
	}

	/**
	 * Получение файлов
	 *
	 * @return \Files[]
	 */
	public function getFiles($parameters = null) : array
	{
		$files_data = $this->getRelated('Files', $parameters);
		$files_return = [];

		// Форматируем для API
		foreach ($files_data as $file_data)
			$files_return[] = $file_data->toApi();

		return $files_return;
	}

	function getComments($parameters = null): Array
	{
		$comments_data = $this->getRelated('Comments', $parameters);
		$comments_return = [];

		// Форматируем для API
		foreach ($comments_data as $comment_data)
			$comments_return[] = $comment_data->toApi();

		return $comments_return;
	}

	public function toApi($showReplies = true) : Array
	{
		return [
			'id' 			=> (int) $this->id,
			'topics_id' 	=> (int) $this->topics_id,
			'parent' 		=> (int) $this->parent,
			'type' 			=> (string) $this->type,
			'message' 		=> (string) ($this->isDeleted ? '' : $this->message),
			'isBanned' 		=> (int) $this->isBanned,
			'isPinned' 		=> (int) $this->isPinned,
			'isDeleted' 	=> (int) $this->isDeleted,
			'timestamp' 	=> (int) $this->timestamp,
			'files' 		=> (array) ($this->isDeleted ? [] : $this->getFiles()),
			'replies' 		=> (array) ($showReplies ? $this->getComments() : [])
		];
	}
}
