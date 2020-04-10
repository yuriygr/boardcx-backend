<?php

class GalleryController extends ControllerBase
{
	public function list()
	{
		// Переменные фильтра
		$filter_data['tag']     = $this->request->get('tag', 'string');
		$filter_data['limit']   = $this->request->get('limit', 'int', $this->config->site->filesPerPage);
		$filter_data['page']    = $this->request->get('page', 'int', 1);
		$filter_data['except']  = explode(',', $this->request->get('except', 'string'));

		// Сортировка
		$order = 'isPinned DESC, timestamp DESC';

		// Магия нахуй
		if ($filter_data['tag']) {
			$tags_data = Tags::find([
				'slug = :slug:',
				'bind' => [
					'slug' => $filter_data['tag']
				]
			]);
			$files_return = [];

			foreach ($tags_data as $tag_data) {
				foreach ($tag_data->getTopics(['order' => $order]) as $topic_data) {
					foreach ($topic_data->getFiles() as $file_data) {
						$files_return[] = $file_data;
					}
					foreach ($topic_data->getComments([ 'parent is null' ]) as $comment_data) {
						foreach ($comment_data['files'] as $file_data) {
							$files_return[] = $file_data;
						}
					}
				}
			}

		} elseif ($filter_data['except']) {
			$topics_data = Topics::find(['order' => $order]);
			$files_return = [];

			foreach ($topics_data as $topic_data) {
				foreach ($topic_data->getTags() as $tag) {
					if (in_array($tag['slug'], $filter_data['except'])) continue 2;
				}
				foreach ($topic_data->getFiles() as $file_data) {
					$files_return[] = $file_data;
				}
				foreach ($topic_data->getComments([ 'parent is null' ]) as $comment_data) {
					foreach ($comment_data['files'] as $file_data) {
						$files_return[] = $file_data;
					}
				}
			}

		} else {
			$files_data = Files::find(['order' => 'id DESC']);
			$files_return = [];

			foreach ($files_data as $file_data) {
				$files_return[] = $file_data->toApi(false);
			}
		}

		// Лимты
		if ($filter_data['limit'] <= 0)
			$filter_data['limit'] = 1;
		if ($filter_data['limit'] >= $this->config->site->filesPerPage)
			$filter_data['limit'] = $this->config->site->filesPerPage;

		// Заводим данные
		$paginator = new \Phalcon\Paginator\Adapter\NativeArray([
			'data'      => $files_return,
			'limit'     => $filter_data['limit'],
			'page'      => $filter_data['page']
		]);
		$files_return = $paginator->getPaginate();

		$res = [
			'response' => $files_return
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);

	}
}