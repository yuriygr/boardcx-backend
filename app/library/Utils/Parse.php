<?php
namespace Phalcon\Utils;

use \Phalcon\Tag;

class Parse extends \Phalcon\Mvc\User\Component {

	var $board_slug;
	var $thread_id;

	/* Hashtag
	 ================================== */
	function MakeHashTag(string $buffer) : string
	{
		$buffer = preg_replace_callback('/(\#)([0-9a-zA-Z\_]+)/', array(&$this, 'MakeHashTagCallback'), $buffer);

		return $buffer;
	}
	function MakeHashTagCallback(array $matches) : string
	{
		$link = '<a href="/stream/?tag='. $matches[2] .'" >' . '#' . $matches[2]. '</a>';

		return $link;
	}

	/* Ссылки
	 ================================== */
	function MakeLink(string $buffer) : string
	{
		$buffer = preg_replace_callback('#(http://|https://|ftp://|rtsp://)([^(\s<|\[)]*)#', array(&$this, 'WebLinkCallback'), $buffer);
		
		return $buffer;
	}
	// Внешняя ссылка
	function WebLinkCallback($matches) : string
	{
		$full_link = $matches[1] . $matches[2];

		if (parse_url($full_link)['host'] == 'board.cx')
			return '<a href="' . $full_link . '">' . $full_link . '</a>';
		else
			return '<a href="' . $full_link . '" target="_blank" rel="noopener">' . $full_link . '</a>';
	}

	/* Цитаты
	 ================================== */
	function MakeQuote(string $buffer) : string
	{
		/* Add a \n to keep regular expressions happy */
		if (substr($buffer, -1, 1)!="\n")
			$buffer .= "\n";

		$buffer = preg_replace('/^(&gt;[^>](.*))\n/m', '<span class="quote">\\1</span>'."\n", $buffer);

		return $buffer;
	}
	
	/* Ссылка на пост
	 ================================== */
	function MakePostLink(string $buffer) : string
	{
		// Ссылка на пост в пределе раздела
		$buffer = preg_replace_callback('/&gt;&gt;([r]?[l]?[f]?[q]?[0-9,\-,\,]+)/', array(&$this, 'PostLinkCallback'), $buffer);
		// Ссылка на пост в другом разделе
		//$buffer = preg_replace_callback('/&gt;&gt;\/([a-z]+)\/([0-9]+)/', array(&$this, 'InterPostLinkCallback'), $buffer);

		return $buffer;
	}
	// Ссылка на пост в пределе раздела
	function PostLinkCallback($matches) :string
	{
		global $board_slug;

		$lastchar = '';
		// If the quote ends with a , or -, cut it off.
		if (substr($matches[0], -1) == "," || substr($matches[0], -1) == "-") {
			$lastchar = substr($matches[0], -1);
			$matches[1] = substr($matches[1], 0, -1);
			$matches[0] = substr($matches[0], 0, -1);
		}
		
		$comment_data = \TopicsComments::findFirstById($matches[1]);

		if ($comment_data) {
			$comment_id = $comment_data->id;
			$topics_id = $comment_data->topics_id;

			$link = '<a href="/topic/'. $topics_id . '#' . $comment_id . '" data-comment-preview="' . $topics_id . '|' . $comment_id . '">' . '&gt;&gt;' . $comment_id . '</a>';
		} else{
			$link = '&gt;&gt;' . $matches[1];
		}

		return $link.$lastchar;
	}
	// Ссылка на пост в другом разделе
	function InterPostLinkCallback($matches) : string
	{
		$lastchar = '';
		// If the quote ends with a , or -, cut it off.
		if (substr($matches[0], -1) == "," || substr($matches[0], -1) == "-") {
			$lastchar = substr($matches[0], -1);
			$matches[1] = substr($matches[1], 0, -1);
			$matches[0] = substr($matches[0], 0, -1);
		}
		
		$post = \Post::findFirst(
			[ 'id = :id: and board = :board:',
				'bind' => [ 'id' => $matches[2], 'board' => $matches[1]]
			]
		);

		if ($post) {
			$id = $post->id;
			$board = $post->board;
			$thread = ($post->parent == 0 ? $post->id : $post->parent);
			$op = ($post->parent == 0 ? 'op_post' : '');

			$link = '<a	href="/' . $board . '/thread/'. $thread . '#' . $id . '" data-post-preview="' . $board . '|'. $thread . '|' . $id . '" class="' .$op . '">' . '&gt;&gt;' . '/' . $board . '/' . $id . '</a>';
		} else {
			$link = '&gt;&gt;' . '/' . $matches[1] . '/' . $matches[2];
		}

		return $link.$lastchar;
	}

	/* ББ коды
	 ================================== */
	function MakeBBCode(string $buffer) : string
	{
		$patterns = array(
			'`\*\*(.+?)\*\*`is',
			'`\*(.+?)\*`is',
			'`\_\_(.+?)\_\_`is', 
			'`\-\-(.+?)\-\-`is',
			'`\%\%(.+?)\%\%`is',
			
			'`\[b\](.+?)\[/b\]`is', 
			'`\[i\](.+?)\[/i\]`is', 
			'`\[u\](.+?)\[/u\]`is', 
			'`\[s\](.+?)\[/s\]`is', 
			'`\[spoiler\](.+?)\[/spoiler\]`is', 
		);
		$replaces =  array(
			'<b>\\1</b>', 
			'<i>\\1</i>',
			'<span class="underline">\\1</span>',
			'<strike>\\1</strike>', 
			'<span class="spoiler">\\1</span>', 
			
			'<b>\\1</b>', 
			'<i>\\1</i>', 
			'<span class="underline">\\1</span>', 
			'<strike>\\1</strike>', 
			'<span class="spoiler">\\1</span>', 
		);
		$buffer = preg_replace($patterns, $replaces, $buffer);
		$buffer = preg_replace_callback('`\[code\](.+?)\[/code\]`is', array(&$this, 'CodeCallback'), $buffer);
		
		return $buffer;
	}
	function CodeCallback($matches) : string
	{
		$return = '<pre><code>' . str_replace('<br />', '', $matches[1]) . '</code></pre>';

		return $return;
	}

	/* Проверка на наличие
	 ================================== */
	function CheckNotEmpty(string $buffer) : string
	{
		$buffer_temp = str_replace("\n", "", $buffer);
		$buffer_temp = str_replace("<br>", "", $buffer_temp);
		$buffer_temp = str_replace("<br/>", "", $buffer_temp);
		$buffer_temp = str_replace("<br />", "", $buffer_temp);

		$buffer_temp = str_replace(" ", "", $buffer_temp);
		
		if ($buffer_temp == "")
			return "";
		else
			return $buffer;
	}

	/**
	 * General function
	 */
	function Make(string $message) : string
	{
		// Чистим вилкой
		$message = trim($message);
		// Удаляем спецсимволы
		$message = htmlspecialchars($message);
		// Замена переносов
		$message = nl2br($message);
		// Делаем хештеги
		$message = $this->MakeHashTag($message);
		// Ссылка на пост
		$message = $this->MakePostLink($message);
		// Цитата
		$message = $this->MakeQuote($message);
		// ББ коды
		$message = $this->MakeBBCode($message);
		// Ссылки
		$message = $this->MakeLink($message);
		// Убираем лишние переносы
		$message = preg_replace('#(<br(?: \/)?>\s*){3,}#i', '<br /><br />', $message);
		// Проверка на наличие
		$message = $this->CheckNotEmpty($message);
		
		return $message;
	}
}
?>