<?php

class TopicsController extends ControllerBase
{
	public function list()
	{
		// Переменные фильтра
		$filter_data['type']    = $this->request->get('type', 'string');
		$filter_data['tag']     = $this->request->get('tag', 'string');
		$filter_data['limit']   = $this->request->get('limit', 'int', $this->config->site->topicsPerPage);
		$filter_data['page']    = $this->request->get('page', 'int', 1);
		$filter_data['except']  = explode(',', $this->request->get('except', 'string'));

		// Сортировка
		switch ($filter_data['type'] ) {
			case 'new':
			default:
				$order = 'isPinned DESC, timestamp DESC';
				break;

			case 'hot':
				$order = 'isPinned DESC, bump DESC';
				break;
		}

		// Магия нахуй
		if ($filter_data['tag']) {
			$tags_data = Tags::find([
				'slug = :slug:',
				'bind' => [
					'slug' => $filter_data['tag']
				]
			]);
			$topics_return = [];

			foreach ($tags_data as $tag_data) {
				foreach ($tag_data->getTopics(['isDeleted = 0', 'order' => $order]) as $topic_data) {
					$topics_return[] = $topic_data->toApi(false);
				}
			}

		} elseif ($filter_data['except']) {
			$topics_data = Topics::find(['isDeleted = 0', 'order' => $order]);
			$topics_return = [];

			foreach ($topics_data as $topic_data) {
				foreach ($topic_data->getTags() as $tag) {
					if (in_array($tag['slug'], $filter_data['except'])) continue 2;
				}
				$topics_return[] = $topic_data->toApi(false);
			}

		} else {
			$topics_data = Topics::find(['isDeleted = 0', 'order' => $order]);
			$topics_return = [];

			foreach ($topics_data as $topic_data) {
				$topics_return[] = $topic_data->toApi(false);
			}
		}

		// Лимты
		if ($filter_data['limit'] <= 0)
			$filter_data['limit'] = 1;
		if ($filter_data['limit'] >= 30)
			$filter_data['limit'] = 30;

		// Заводим данные
		$paginator = new \Phalcon\Paginator\Adapter\NativeArray([
			'data'      => $topics_return,
			'limit'     => $filter_data['limit'],
			'page'      => $filter_data['page']
		]);
		$topics_return = $paginator->getPaginate();

		$res = [
			'response' => $topics_return,
			'session' => $this->session->getId()
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function item()
	{
		// Переменные
		$filter_data['topic_id'] = $this->request->get('topic_id', 'int');

		// Находим топик
		$topic_data = Topics::findFirstById($filter_data['topic_id']);
		if (!$topic_data)
			throw new \Phalcon\Exception('Topic not found!');

		if ($topic_data->isDeleted)
			throw new \Phalcon\Exception('Topic deleted!');

		$topics_views = TopicsViews::findFirst([
			'topics_id = :topics_id: and userIp = :user_ip: ',
			'bind' => [
				'topics_id' => $topic_data->id,
				'user_ip' => $this->request->getClientAddress()
			]
		]);
		if (empty($topics_views)) {
			$topics_views = new TopicsViews([
				'topics_id' => $topic_data->id,
				'userIp' => $this->request->getClientAddress(),
				'timestamp' => time()
			]);
			$topics_views->save();
		}

		$topic_return = $topic_data->toApi(true);

		$res = [
			'response' => $topic_return,
			'session' => $this->session->getId()
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function add()
	{
		if ($this->request->isPost()) {			
			$type              = $this->request->getPost('type', 'string', 'normal');
			$subject           = $this->request->getPost('subject', 'striptags');
			$message           = $this->parse->make($this->request->getPost('message'));
			$tags              = explode(',', $this->request->getPost('tags', 'striptags', ''));
			$is_mine           = $this->request->getPost('is_mine', 'int', 0);
			$allow_attach      = $this->request->getPost('allow_attach', 'int', 1);
			$self_moderation   = $this->request->getPost('self_moderation', 'int', 0);
			$password          = $this->request->getPost('password', 'string', '');
			$user_ip           = $this->request->getClientAddress();
			$captchaResponse   = $this->request->getPost('captcha', 'string', '');

			// Сессия
			if (!$this->request->get('session'))
				throw new \Phalcon\Exception('Word from the Stop List.');

			// Капча
			$recaptcha = $this->recaptcha->verify($captchaResponse, $user_ip);
			if (!$recaptcha->isSuccess())
				throw new \Phalcon\Exception($recaptcha->getErrorCodes()[0]);

			// Проверка на наличие текста
			if (!$subject)
				throw new \Phalcon\Exception('Enter subject.');

			// Проверка длинны заголовка
			if ($this->_checkSubject($subject))
				throw new \Phalcon\Exception('The subject is too small... or large.');

			// Проверка на наличие текста
			if (!$message)
				throw new \Phalcon\Exception('Enter message.');

			// Проходим проверку на спам
			if ($this->_checkStoplist($message))
				throw new \Phalcon\Exception('Word from the Stop List.');

			// Проверка бана
			if ($this->_checkBan($user_ip))
				throw new \Phalcon\Exception('Enter message, boy.');

			// Проверка тегов
			if ($tags == '')
				throw new \Phalcon\Exception('At least one tag is needed.');

			// Проверка кол-ва тегов
			if ($this->_checkTags($tags))
				throw new \Phalcon\Exception('Too many tags.');

			// Проверяем, добавлял ли данный молодой человек недавно топик
			if (!$this->_checkLastTopic($user_ip))
				throw new \Phalcon\Exception('Please wait a few seconds');

			// Проверка на наличие текста
			if ($self_moderation && !$password)
				throw new \Phalcon\Exception('Enter password.');

			// Создаём пост
			$topic                   = new Topics();
			$topic->type             = (string) $type;
			$topic->subject          = (string) $subject;
			$topic->message          = $message;
			$topic->timestamp        = (int) time();
			$topic->userIp           = (string) $user_ip;
			$topic->bump             = (int) time();
			$topic->isMine           = (int) $is_mine;
			$topic->allowAttach      = (int) $allow_attach;
			$topic->selfModeration   = (int) $self_moderation;
			$topic->password         = (string) $self_moderation ? $this->security->hash($password) : '';
			$topic->hash             = (string) '0x' . hash('crc32', $user_ip);

			// Добваляем к посту теги
			if (!empty($tags)) {
				$topicsTags = [];

				foreach ($tags as $tag) {
					$tags_data = Tags::findFirstById($tag);
					if (!$tags_data) continue;
					$topicsTags[] = $tags_data;
				}

				$topic->tags = $topicsTags;
			}

			// Добваляем к посту картинки
			if ($this->request->hasFiles() && $topic->allowAttach) {
				$files = [];

				foreach ($this->request->getUploadedFiles() as $file) {
					$files[] = $this->fileUploader->upload($file);
				}

				$topic->files = $files;
			}

			// Добавляем пост
			if (!$topic->create())
				throw new \Phalcon\Exception('Error: ' . $topic->getMessages()[0]);

			// Возвращаем что-то
			$res = [
				'response' => [
					'type' => 'success',
					'message' => 'Topic created',
					'data' => $topic->toApi()
				]
			];
			$this->response->setStatusCode(201, "Created");
			return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
		}

		if ($this->request->isOptions()) {
			$res = [
				'response' => 'OK'
			];
			return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
		}
	}

	public function refresh()
	{
		// Переменные
		$topic_id = $this->request->get('topic_id', 'int');
		$after_id = $this->request->get('after_id', 'int');

		// Находим комментарии
		$comments_data = TopicsComments::find([
			'topics_id = :topic_id: and id > :after_id:',
			'order' => 'timestamp',
			'bind' => [
				'topic_id' => $topic_id,
				'after_id' => $after_id
			]
		])
		->filter(function($child) {
			return $child->toApi(false);
		});

		// Находим топик для подсчета
		$topic_data = Topics::findFirstById($topic_id);
		if (!$topic_data)
			throw new \Phalcon\Exception('Topic not found!');

		if ($topic_data->isDeleted)
			throw new \Phalcon\Exception('Topic deleted!');
	
		$res = [
			'response' => [
				'bump' => $topic_data->bump,
				'countComments' => $topic_data->countComments([ 'isDeleted = 0 ' ]),
				'comments' => $comments_data
			]
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function report()
	{
		if ($this->request->isPost()) {
			// Переменные
			$type = 'topic';
			$params = $this->request->getPost('topics_id', 'int');
			$reason = $this->request->getPost('reason', 'striptags');
			$user_ip = $this->request->getClientAddress();

			// Check topic
			$topic = Topics::findFirstById($params);
			if (!$topic)
				throw new \Phalcon\Exception('Topic does not exist');

			// Проверка на наличие причины
			if (!$reason)
				throw new \Phalcon\Exception('You must provide a reason');

			// Проверяем, добавлял ли данный молодой человек недавно репорт
			if (!$this->_checkLastReport($user_ip))
				throw new \Phalcon\Exception('Please wait a few seconds');

			// Проверяем, добавлял ли данный молодой человек репорт на этот топик
			if (!$this->_checkHasReport($user_ip, $type, $params))
				throw new \Phalcon\Exception('You already reported this ' . $type);

			// Создаём пост
			$report = new Reports();
			$report->type        = (string) $type;
			$report->reason      = (string) $reason;
			$report->params      = (int) $params;
			$report->timestamp   = (int) time();
			$report->userIp     = $user_ip;

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

	public function password()
	{
		if ($this->request->isPost()) {
			// Переменные
			$topics_id = $this->request->getPost('topics_id', 'int');
			$password = $this->request->getPost('password', 'string');

			// Check topic
			$topic = Topics::findFirstById($topics_id);
			if (!$topic)
				throw new \Phalcon\Exception('Topic does not exist');

			if (!$topic->selfModeration)
				throw new \Phalcon\Exception('In the topic there is no self-moderation');

			// Проверка на наличие причины
			if (!$password)
				throw new \Phalcon\Exception('You must enter a password');

			// Сверка паролей
			if (!$this->security->checkHash($password, $topic->password))
				throw new \Phalcon\Exception('Wrong password');

			// Возвращаем что-то
			$res = [
				'response' => [
					'type' => 'success',
					'message' => 'Password correct, enjoy'
				]
			];
			$this->response->setStatusCode(201, 'Created');
			return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
		}
	}

	public function logs()
	{
		// Переменные
		$filter_data['topic_id'] = $this->request->get('topic_id', 'int');

		// Находим топик
		$topic_data = Topics::findFirstById($filter_data['topic_id']);
		if (!$topic_data)
			throw new \Phalcon\Exception('Topic not found!');

		$logs_data = $topic_data->getLogs();

		$logs_return = $logs_data;

		$res = [
			'response' => $logs_return
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}
}