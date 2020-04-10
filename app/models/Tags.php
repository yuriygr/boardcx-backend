<?php

class Tags extends \Phalcon\Mvc\Model
{
	public $id;

	public $title;

	public $slug;

	public $type;

	public $available;

	public $nsfw;


	public function initialize()
	{
		$this->hasManyToMany(
			'id', 'TopicsTags', 'tags_id',
			'topics_id', 'Topics', 'id'
		);
	}

	public function toApi() : array
	{
		return [
			'id' 		=> (int) $this->id,
			'title'		=> (string) $this->title,
			'slug' 		=> (string) $this->slug,
			'type' 		=> (string) $this->type,
			'available' => (int) $this->available,
			'nsfw' 		=> (bool) $this->nsfw,
		];
	}
}
