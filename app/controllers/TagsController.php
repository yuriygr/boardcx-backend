<?php

class TagsController extends ControllerBase
{
	public function list()
	{
		$tags_data = Tags::find([
			'available = :available:',
			'bind' => [
				'available' => true
			]
		]);
		$tags_return = [];

		// Форматируем для API
		foreach ($tags_data as $tag_data) {
			$tags_return[] = $tag_data->toApi();
		}

		$res = [
			'response' => $tags_return
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function stats()
	{
		$tags_data = Tags::find([
			'available = :available:',
			'bind' => [
				'available' => true
			]
		]);
		$tags_return = [];

		// Форматируем для API
		foreach ($tags_data as $tag_data) {
			$tag_data_array = $tag_data->toArray();
			$tag_data_array['countTopics'] = $tag_data->countTopics();
			$tags_return[] = $tag_data_array;
		}

		$res = [
			'response' => $tags_return
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}
}