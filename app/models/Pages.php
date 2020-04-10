<?php

use \Phalcon\Utils\Timeformat;

class Pages extends \Phalcon\Mvc\Model
{

	public $id;

	public $slug;

	public $name;

	public $type;

	public $text;

	public $created_at;

	public $modified_in;

	public $isComments;

	public $isHide;

	// Meta-tag
	public $meta_description;

	public $meta_keywords;



	public function initialize()
	{
		// Не записываем при редактировании сюда
		$this->skipAttributesOnUpdate(array('created_at'));

		// Не записываем при создании сюда
		$this->skipAttributesOnCreate(array('modified_in'));
	}
}
