<?php

class ControllerBase extends \Phalcon\Mvc\Controller
{
	/**
	 * Возвращает JSON
	 * 
	 * @param  array  $response [description]
	 * @param  string $code     [description]
	 * @return response
	 */
	public function returnJson($response = [], $code = [ 200, 'OK' ])
	{
		$res = [
			'response' => $response,
			'code' => $code
		];
		$this->response->setStatusCode($code);
		return $this->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Check subject size
	 * 
	 * @param  string $subject Post subject
	 * @return bool
	 */
	public function _checkSubject($subject) : bool
	{
		$size = iconv_strlen($subject);
		return $size >= $this->config->site->subjectLimit || $size <= 3;
	}

	/**
	 * Check count tags
	 * 
	 * @param  array $tags
	 * @return bool
	 */
	public function _checkTags($tags) : bool
	{
		return count($tags) > $this->config->site->tagsLimit;
	}

	/**
	 * Check message to spam
	 * 
	 * @param  string $text User message
	 * @return bool
	 */
	public function _checkStoplist($text) : bool
	{
		// Собираем все плохие слова
		$stoplist = Stoplist::find();

		// Проходимся по ним
		foreach ($stoplist as $badword) {
			if (stripos($text, $badword->word) !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Примитивная проверка на вшивость
	 *
	 * @param  string $ip User IP adress
	 * @return bool
	 */
	public function _checkBan($ip) : bool
	{
		$banlist = Banlist::findFirstByIp($ip);

		if ($banlist) return true;
		return false;
	}

	/**
	 * Bump topic
	 * 
	 * @param  object $comments Comments
	 * @return bool
	 */
	public function _bumpTopic($comments) : bool
	{
		$topics = Topics::findFirstById($comments->topics_id);

		if ($topics->countComments() <= $this->config->site->commentsLimit) {
			$topics->bump = $comments->timestamp;
			if (!$topics->update())
				return false;
		}
		return true;
	}

	/**
	 * Проверка, когда был сделан последний топик с IP
	 *
	 * @param  string $user_ip User IP adress
	 * @return bool
	 */
	public function _checkLastTopic($user_ip) : bool
	{
		$topics_data = Topics::findFirst([
			'userIp = :user_ip:',
			'order' => 'timestamp DESC',
			'limit' => 1,
			'bind' => [
				'user_ip' => $user_ip
			]
		]);
		if (!$topics_data) return true;
		if ((time() - $topics_data->timestamp) >= $this->config->site->topicsTimeLimit) return true;

		return false;
	}

	/**
	 * Проверка, когда был сделан последний комментарий с IP
	 *
	 * @param  string $user_ip User IP adress
	 * @return bool
	 */
	public function _checkLastComment($user_ip) : bool
	{
		$comments_data = TopicsComments::findFirst([
			'userIp = :user_ip:',
			'order' => 'timestamp DESC',
			'limit' => 1,
			'bind' => [
				'user_ip' => $user_ip
			]
		]);
		if (!$comments_data) return true;
		if ((time() - $comments_data->timestamp) >= $this->config->site->commentsTimeLimit) return true;

		return false;
	}

	/**
	 * Проверка, когда был сделан последний репорт с IP
	 *
	 * @param  string $user_ip User IP adress
	 * @return bool
	 */
	public function _checkLastReport($user_ip) : bool
	{
		$report_data = Reports::findFirst([
			'userIp = :user_ip:',
			'order' => 'timestamp DESC',
			'limit' => 1,
			'bind' => [
				'user_ip' => $user_ip
			]
		]);
		if (!$report_data) return true;
		if ((time() - $report_data->timestamp) >= $this->config->site->reportsTimeLimit) return true;

		return false;
	}

	/**
	 * Проверка, отправлялся ли репорт с IP
	 *
	 * @param  string $user_ip User IP adress
	 * @return bool
	 */
	public function _checkHasReport($user_ip, $type, $params) : bool
	{
		$report_data = Reports::findFirst([
			'userIp = :userIp: and type = :type: and params = :params:',
			'order' => 'timestamp DESC',
			'limit' => 1,
			'bind' => [
				'userIp' => $user_ip,
				'type' => $type,
				'params' => $params
			]
		]);
		if ($report_data) return false;

		return true;
	}
}