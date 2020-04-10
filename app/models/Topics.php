<?php

class Topics extends \Phalcon\Mvc\Model
{
	public $id;

	public $type;

	public $subject;

	public $timestamp;

	public $bump;

	public $message;

	public $userIp;

	public $isClosed;

	public $isPinned;

	public $isDeleted;

	public $isMine;

	public $allowAttach;
	
	public $selfModeration;

	public $password;

	public function initialize()
	{
		// Хм
		$this->useDynamicUpdate(true);

		// Relations
		$this->hasManyToMany(
			'id', 'TopicsTags', 'topics_id',
			'tags_id', 'Tags', 'id',
			[
				'alias' => 'Tags',
				'foreignKey' => true
			]
		);
		$this->hasManyToMany(
			'id', 'TopicsFiles', 'topics_id',
			'files_id', 'Files', 'id',
			[
				'alias' => 'Files',
				'foreignKey' => true
			]
		);
		$this->hasMany(
			'id', 'TopicsComments', 'topics_id',
			[
				'alias' => 'Comments',
				'foreignKey' => true
			]
		);
		$this->hasMany(
			'id', 'TopicsViews', 'topics_id',
			[
				'alias' => 'Views',
				'foreignKey' => true
			]
		);
	}

	/**
	 * Получение всех тегов поста.
	 *
	 * @return \Tags[]
	 */
	public function getTags($parameters = null) : array
	{
		$tags_data = $this->getRelated('Tags', $parameters);
		$tags_return = [];

		// Форматируем для API
		foreach ($tags_data as $tag_data)
			$tags_return[] = $tag_data->toApi();

		return $tags_return;
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


	/**
	 * Получение ответов на пост.
	 *
	 * @return \Comments[]
	 */
	public function getComments($parameters = null) : array
	{
		$comments_data = $this->getRelated('Comments', $parameters);
		$comments_return = [];

		// Форматируем для API
		foreach ($comments_data as $comment_data)
			$comments_return[] = $comment_data->toApi();

		return $comments_return;
	}

	/**
	 * Форматирует пост для API.
	 *
	 * @return Array
	 */
	public function toApi($showReplies = false) : array
	{
		$formated = [];
		$formated['id']              = (int) $this->id;
		$formated['subject']         = (string) $this->subject;
		$formated['message']         = (string) $this->message;
		$formated['timestamp']       = (int) $this->timestamp;
		$formated['bump']            = (int) $this->bump;
		$formated['isClosed']        = (int) $this->isClosed;
		$formated['isPinned']        = (int) $this->isPinned;
		$formated['isMine']          = (int) $this->isMine;
		$formated['allowAttach']     = (int) $this->allowAttach;
		$formated['selfModeration']  = (int) $this->selfModeration;
		$formated['countComments']   = (int) $this->countComments([ 'isDeleted = 0 ' ]);
		$formated['countViews']      = (int) $this->countViews();
		$formated['tags']            = (array) $this->getTags();
		$formated['files']           = (array) $this->getFiles();
		if ($showReplies)
			$formated['comments']        = (array) $this->getComments([ 'parent = 0', 'order' => 'isPinned DESC, timestamp ASC' ]);

		return $formated;
	}
}
