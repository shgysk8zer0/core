<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2015, Chris Zuber
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

/**
 * Easily work with pages by getting just the content/meta unique to them
 * Works for either regular or AJAX requests
 */
class Pages implements API\Interfaces\Magic_Methods
{
	use API\Traits\Singleton;
	use API\Traits\Magic_Methods;

	const MAGIC_PROPERTY = 'data';
	private $data, $path, $url, $status, $parsed;
	public $content, $type;

	/**
	 * Construct the class based on $url (defaulting to the current URL)
	 * Aside from other magic methods, this is the only public method.
	 * All else is handled during construction.
	 * @param string $url Any valid relative or absolute URL... Or null
	 */
	public function __construct($url = null)
	{
		$this->status = (array_key_exists('REDIRECT_STATUS', $_SERVER))
			? $_SERVER['REDIRECT_STATUS']
			: http_response_code();

		$pdo = PDO::load('connect.json');

		if (is_string($url)) {
			$this->url = $url;
		} else {
			$this->url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];
			if (array_key_exists('REDIRECT_URL', $_SERVER)) {
				$this->url .= $_SERVER['REDIRECT_URL'];
			} elseif (array_key_exists('REQUEST_URI', $_SERVER)) {
				$this->url .= $_SERVER['REQUEST_URI'];
			}
		}

		$this->parsed = (object)parse_url(strtolower(urldecode($this->url)));
		$this->path = explode('/', trim($this->parsed->path, '/'));
		if (str_replace('/', DIRECTORY_SEPARATOR, rtrim($_SERVER['DOCUMENT_ROOT'], '/')) !== BASE) {
			unset($this->path[0]);
			$this->path = array_values($this->path);

			if (empty($this->path)) {
				$this->path = [''];
			}
		}

		if ($pdo->connected) {
			switch(current($this->path)) {
				case 'tags':
					if (isset($this->path[1])) {
						$this->type = 'tags';
						$this->data = $pdo->prepare(
							"SELECT
								`title`,
								`description`,
								`author`,
								`author_url`,
								`url`,
								`created`
							FROM `posts`
							WHERE `keywords` LIKE :tag
							LIMIT 20;"
						)->execute([
							'tag' => preg_replace('/\s*/', '%', " {$this->path[1]} ")
						])->getResults();
					}
					break;

				case 'posts':
				case '/':
				case '':
					$this->type = 'posts';
					if (count($this->path) === 1 and $this->path[0] === '') {
						$this->data = $pdo->fetchArray(
							'SELECT *
							FROM `posts`
							WHERE `url` = ""
							LIMIT 1;'
						, 0);
					} elseif (count($this->path) >= 2) {
						$this->data = $pdo->prepare(
							'SELECT *
							FROM `posts`
							WHERE `url` = :url
							ORDER BY `created`
							LIMIT 1;'
						)->execute([
							'url' => urlencode($this->path[1])
						])->getResults(0);
					}
					break;
			}
			if (isset($this->data) and !empty($this->data)) {
				$this->getContent();
			} else{
				$this->errorPage();
			}
		}
	}

	/**
	 * Where all of the parsing and setting of data is handled.
	 * Switches on type of page request, and sets various properties
	 * accordingly.
	 *
	 * @return void
	 * @uses \shgsyk8zer0\Template
	 */
	private function getContent()
	{
		$login = Login::load();
		$DB = PDO::load('connect.json');

		switch($this->type) {
			case 'posts':
				$post = Template::load('posts');
				$comments = Template::load('comments');
				$comments_section = Template::load('comments_section');
				$license = Template::load('creative_commons');

				$comments_section->title($this->data->title)
					->home(URL)
					->comments(null);

				$results = $DB->prepare(
					'SELECT
						`comment`,
						`author`,
						`author_url`,
						`time`
					FROM `comments`
					WHERE `post` = :post;'
				)->execute([
					'post' => $this->data->url
				])->getResults();

				if (is_array($results)) {
					foreach ($results as $comment) {
						$time = strtotime($comment->time);
						$comments->comment(
							$comment->comment
						)->author(
							(strlen($comment->author_url))
								? "<a href=\"{$comment->author_url}\" target=\"_blank\">{$comment->author}</a>"
								: $comment->author
						)->time(
							date('l, F jS Y h:i A', $time)
						);

						$comments_section->comments .= "{$comments}";
					}
				}

				foreach (explode(',', $this->data->keywords) as $tag) {
					$post->tags .= '<a href="' . URL . '/tags/' . urlencode(trim($tag)) . '" rel="tag">' . trim($tag) . "</a>";
				}

				$time = strtotime($this->data->created);

				$license->title($this->data->title)
					->author($this->data->author)
					->author_url($this->data->author_url)
					->date(date('m/d/Y', $time))
					->datetime($time);

				$post->title($this->data->title)
					->content($this->data->content)
					->home(URL)
					->comments("{$comments_section}")
					->url($this->data->url)
					->license("{$license}");

				$this->content = "{$post}";

				break;

			case 'tags':
				$this->title = 'Tags';
				$this->description = "Tags search results for {$this->path[1]}";
				$this->keywords = "Keywords, tags, search, {$this->path[1]}";
				$this->content = '<div class="tags">';

				$template = Template::load('tags');

				array_map(function(\stdClass $post) use (&$template)
				{
					if (! isset($post->title)) {
						return;
					}
					$template->title($post->title)
						->description($post->description)
						->author($post->author)
						->author_url($post->author_url)
						->url(($post->url === '')? URL : URL .'/posts/' . $post->url)
						->date(date('D M jS, Y \a\t h:iA', strtotime($post->created)));
					$this->content .= "{$template}";
				}, array_filter($this->data, 'is_object'));

				$this->content .= '</div>';
				break;
		}
	}

	/**
	 * Handler for invalid URLs
	 *
	 * @param  int     $code         HTTP Status Code
	 * @param  string  $title_prefix Prefix <title> with this string
	 * @param  bool    $dump         Whether or not to include a dump of parsed URL
	 * @return void
	 */
	private function errorPage(
		$code = 404,
		$title_prefix = 'Woops! Not found',
		$dump = true
	)
	{
		http_response_code($code);
		$this->status = $code;
		$this->description = 'No results for ' . $this->url;
		$this->keywords = '';
		$this->title = $title_prefix .  ' (' . $code . ')';

		$template = Template::load('error_page');
		$template->home = URL;
		$template->message = "Nothing found for <wbr /><var>{$this->url}</var>";
		$template->link = $this->url;

		if ($dump) {
			$template->dump = print_r($this->parsed, true);
		} else {
			$template->dump = null;
		}

		$this->content = "{$template}";
	}
}
