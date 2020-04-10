<?php

class Files extends \Phalcon\Mvc\Model
{
	public $id;

	public $slug;

	public $provider;

	public $type;

	public $size;

	public $o_width;

	public $o_height;

	public $t_width;

	public $t_height;

	public function initialize()
	{
		$this->hasManyToMany(
			'id', 'TopicsFiles', 'files_id',
			'topics_id', 'Topics', 'id'
		);
		$this->hasManyToMany(
			'id', 'CommentsFiles', 'files_id',
			'comments_id', 'TopicsComments', 'id'
		);
	}

	public function getLink($type = 'origin')
	{
		if ($type == 'origin')
			return 'https://i.imgur.com/' . $this->slug . '.' . $this->type;
			
		if ($type == 'thumb')
			return 'https://i.imgur.com/' . $this->slug . 'b.' . $this->type;
	}
	// Получаем разрешение файла
	public function getResolution()
	{
		return $this->o_width . 'x' . $this->o_height;
	}

	public function toApi() : array
	{
		return [
			'id' 				=> (int) $this->id,
			'origin' 			=> (string) $this->getLink('origin'),
			'thumb' 			=> (string) $this->getLink('thumb'),
			'resolution' 		=> (string) $this->getResolution(),
			'type' 				=> (string) $this->type,
			'size' 				=> (string) $this->size
		];
	}
}
