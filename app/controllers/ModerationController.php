<?php

class ModerationController extends ControllerBase
{
	public function edit()
	{
		throw new \Phalcon\Exception('You don\'t have permission!');
		$res = [
			'response' => "OK"
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function ban()
	{
		throw new \Phalcon\Exception('You don\'t have permission!');
		$res = [
			'response' => "OK"
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function pin()
	{
		$params['topic_id'] = $this->request->getPost('topic_id', 'int');
		if (!isset($params['topic_id']))
			throw new \Phalcon\Exception('Topic is needed');

		$params['password'] = $this->request->getPost('password');
		if (!isset($params['password']))
			throw new \Phalcon\Exception('Password is needed');
	
		$params['comment_id'] = $this->request->getPost('comment_id', 'int');
		if (!isset($params['comment_id']))
			throw new \Phalcon\Exception('Comment is needed');
			
		$topic_data = Topics::findFirstById($params['topic_id']);
		if (!$topic_data)
			throw new \Phalcon\Exception('Topic not found!');

		if (!$topic_data->selfModeration)
			throw new \Phalcon\Exception('Topic is not self-moderated');

		if (!$this->security->checkHash($params['password'], $topic_data->password))
			throw new \Phalcon\Exception('Wrong password');

		$comment_data = TopicsComments::findFirstById($params['comment_id']);
		if (!$comment_data)
			throw new \Phalcon\Exception('Comment not found!');
	
		$comment_data->isPinned = true;

		if (!$comment_data->update())
			throw new \Phalcon\Exception('Wrong! WROOONG!');
	
		$res = [
			'response' => "OK"
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function close()
	{
		$params['topic_id'] = $this->request->getPost('topic_id', 'int');
		if (!isset($params['topic_id']))
			throw new \Phalcon\Exception('Topic is needed');

		$params['password'] = $this->request->getPost('password');
		if (!isset($params['password']))
			throw new \Phalcon\Exception('Password is needed');

		$topic_data = Topics::findFirstById($params['topic_id']);
		if (!$topic_data)
			throw new \Phalcon\Exception('Topic not found!');

		if (!$topic_data->selfModeration)
			throw new \Phalcon\Exception('Topic is not self-moderated');

		if ($topic_data->isClosed)
			throw new \Phalcon\Exception('Topic is already closed');

		if (!$this->security->checkHash($params['password'], $topic_data->password))
			throw new \Phalcon\Exception('Wrong password');

		$topic_data->isClosed = true;
		
		if (!$topic_data->update())
			throw new \Phalcon\Exception('Wrong! WROOONG!');
	
		$res = [
			'response' => "OK"
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function delete()
	{
		$params['topic_id'] = $this->request->getPost('topic_id', 'int');
		$params['comment_id'] = $this->request->getPost('comment_id', 'int');
		$params['password'] = $this->request->getPost('password');

		if (!isset($params['topic_id']))
			throw new \Phalcon\Exception('Topic is needed');

		if (!isset($params['password']))
			throw new \Phalcon\Exception('Password is needed');


		$topic_data = Topics::findFirstById($params['topic_id']);
		if (!$topic_data)
			throw new \Phalcon\Exception('Topic not found!');

		if (!$topic_data->selfModeration)
			throw new \Phalcon\Exception('Topic is not self-moderated');

		if ($topic_data->isDeleted)
			throw new \Phalcon\Exception('Topic is already deleted');

		if (!$this->security->checkHash($params['password'], $topic_data->password))
			throw new \Phalcon\Exception('Wrong password');

		$object = $topic_data;

		if ($params['comment_id']) {
			$comment_data = TopicsComments::findFirst([
				'topics_id = :topic_id: and id = :id:',
				'bind' => [
					'topic_id' => $params['topic_id'],
					'id' => $params['comment_id']
				]
			]);
			if (!$comment_data)
				throw new \Phalcon\Exception('Comment not found!');

			$object = $comment_data;
		}

		$object->isDeleted = true;

		if (!$object->update())
			throw new \Phalcon\Exception('Wrong! WROOONG!');

		$res = [
			'response' => "OK"
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	public function settings()
	{
		throw new \Phalcon\Exception('You don\'t have permission!');
		$res = [
			'response' => "OK"
		];
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}
}