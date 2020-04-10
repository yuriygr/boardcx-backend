<?php

class CommentsController extends ControllerBase
{
	public function item()
	{
		// Переменные
		$filter_data['comment_id'] = $this->request->get('comment_id', 'int');

		// Находим комментарий
		$comment_data = TopicsComments::findFirstById($filter_data['comment_id']);
		if (!$comment_data)
			throw new \Phalcon\Exception('Comment not found!');

		$comment_return = $comment_data->toApi(true);

		$res = [
			'response' => $comment_return
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function list()
	{
		// Переменные
		$filter_data['topic_id'] = $this->request->get('topic_id', 'int');

		// Находим топик
		$topic_data = Topics::findFirstById($filter_data['topic_id']);
		if (!$topic_data)
			throw new \Phalcon\Exception('Topic not found!');

		if ($topic_data->isDeleted)
			throw new \Phalcon\Exception('Topic deleted!');

		$comments_return = $topic_data->getComments([ 'parent = 0' ]);

		$res = [
			'response' => $comments_return
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function add()
	{
		if ($this->request->isPost()) {

			$topics_id  = $this->request->getPost('topics_id', 'int');
			$parent_id  = $this->request->getPost('parent_id', 'int');
			$type       = $this->request->getPost('type', 'string', 'normal');
			$message    = $this->parse->make($this->request->getPost('message', null, ''));
			$user_ip    = $this->request->getClientAddress();

			// Check topic
			$topic = Topics::findFirstById($topics_id);
			if (!$topic)
				throw new \Phalcon\Exception('Topic does not exist');
			if ($topic->isClosed)
				throw new \Phalcon\Exception('Topic is closed');
			
			if ($parent_id) {
				// Находим комментарий родительский
				if (!TopicsComments::findFirstById($parent_id))
					throw new \Phalcon\Exception('Comment not found!');
			}
			// Проверка на наличие текста
			if (!$message)
				throw new \Phalcon\Exception('Enter your message');

			// Проходим проверку на спам
			if ($this->_checkStoplist($message))
				throw new \Phalcon\Exception('Word from the Stop List');

			// Проверка бана
			if ($this->_checkBan($user_ip))
				throw new \Phalcon\Exception('Enter your message, boy');

			// Проверяем, добавлял ли данный молодой человек недавно комментарий
			if (!$this->_checkLastComment($user_ip))
				throw new \Phalcon\Exception('Please wait a few seconds');

			// Создаём пост
			$comments = new TopicsComments();
			$comments->topics_id    = (int) $topics_id;
			$comments->parent       = (int) $parent_id;
			$comments->type         = (string) $type;
			$comments->message      = $message;
			$comments->timestamp    = (int) time();
			$comments->userIp       = $user_ip;
			$comments->hash         = (string) '0x' . hash('crc32', $user_ip);

			// Добавляем к посту картинки
			if ($this->request->hasFiles() && $topic->allowAttach) {
				$files = [];

				foreach ($this->request->getUploadedFiles() as $file)
					$files[] = $this->fileUploader->upload($file);

				$comments->files = $files;
			}

			// Добавляем пост
			if (!$comments->save())
				throw new \Phalcon\Exception('Error: ' . $comments->getMessages()[0]);

			// Бампаем тред, если это пост
			if (!$this->_bumpTopic($comments))
				throw new \Phalcon\Exception('Topic not bumped');

			// Возвращаем что-то
			$res = [
				'response' => [
					'type' => 'success',
					'message' => 'Comments sended',
					'data' => $comments->toApi()
				]
			];
			$this->response->setStatusCode(201, 'Created');
			return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
		}

		if ($this->request->isOptions()) {
			$res = [
				'response' => 'OK'
			];
			return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
		}
	}

	public function report()
	{
		if ($this->request->isPost()) {
			// Переменные
			$type = 'comment';
			$params = $this->request->getPost('comments_id', 'int');
			$reason = $this->request->getPost('reason', 'striptags');
			$user_ip = $this->request->getClientAddress();

			// Check comments
			$comment = TopicsComments::findFirstById($params);
			if (!$comment)
				throw new \Phalcon\Exception('Comment does not exist');

			// Проверка на наличие причины
			if (!$reason)
				throw new \Phalcon\Exception('You must provide a reason');

			// Проверяем, добавлял ли данный молодой человек недавно репорт
			if (!$this->_checkLastReport($user_ip))
				throw new \Phalcon\Exception('Please wait a few seconds');

			// Проверяем, добавлял ли данный молодой человек репорт на этот комментарий
			if (!$this->_checkHasReport($user_ip, $type, $params))
				throw new \Phalcon\Exception('You already reported this ' . $type);

			// Создаём пост
			$report = new Reports();
			$report->type        = (string) $type;
			$report->reason      = (string) $reason;
			$report->params      = (int) $params;
			$report->timestamp   = (int) time();
			$report->userIp      = $user_ip;

			if (!$report->save())
				throw new \Phalcon\Exception('Error: ' . $report->getMessages()[0]);

			// Возвращаем что-то
			$res = [
				'response' => [
					'type' => 'success',
					'message' => 'Report sent'
				]
			];
			$this->response->setStatusCode(201, 'Created');
			return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
		}
	}

	public function delete()
	{
		$res = [
			'response' => "OK"
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}
}