<?php

class PagesController extends ControllerBase
{
	public function list()
	{
		$pages_data = Pages::find([
			'columns' => 'id, slug, name',
			'order' => 'id DESC',
			'cache' => [
				'key' => 'page-list',
			]
		]);

		$res = [
			'response' => $pages_data
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function item()
	{
		$page_slug = $this->request->get('page_slug', 'striptags');

		$page_data = Pages::findFirst([
			'slug = :slug:',
			'bind' => [
				'slug' => $page_slug
			]
		]);
		
		if (!$page_data)
			throw new \Phalcon\Exception('Page not found!');

		$res = [
			'response' => $page_data
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}
}