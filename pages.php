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
	use API\Traits\URL;

	const MAGIC_PROPERTY = 'url_data';

	/**
	 * Data retrieved from PDO query
	 * @var \stdClass
	 */
	private $data   = null;

	/**
	 * HTTP response code
	 * @var int
	 */
	private $status = 200;

	/**
	 * Content of page
	 * @var string
	 */
	public $content = '';

	/**
	 * Type of page to display (posts, tags)
	 * @var string
	 */
	public $type    = 'posts';

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

		$this->parseURL($url);
		$this->path = array_map('urldecode', explode('/', ltrim($this->path, '/')));

		if ($pdo->connected) {
			switch(current($this->path)) {
				case 'tags':
					if (count($this->path) > 1) {
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
				case '':
					$this->type = 'posts';
					if (empty($this->path)) {
						$this->data = $pdo->fetchArray(
							'SELECT *
							FROM `posts`
							WHERE `url` = ""
							LIMIT 1;'
						, 0);
					} elseif (count($this->path) >= 1) {
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
		$DB    = PDO::load('connect.json');

		switch($this->type) {
			case 'posts':
				$post             = Template::load('posts');
				$comments         = Template::load('comments');
				$comments_section = Template::load('comments_section');

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
					'post' => $this->path
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
					$post->tags .= '<a href="' . URL . 'tags/' . urlencode(trim($tag)) . '" rel="tag">' . trim($tag) . "</a>";
				}

				$license              = new Creative_Commons_License;
				$license->title       = $this->data->title;
				$license->author      = $this->data->author;
				$license->author_url  = "{$this->data->author_url}?rel=author";
				$license->time        = $this->data->created;
				$license->use_svg     = true;
				$license->share_alike = true;

				$post->title($this->data->title)
					->content($this->data->content)
					->home(URL)
					->comments("{$comments_section}")
					->url($this->data->url)
					->license($license);

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
						->url(($post->url === '')? URL : URL .'posts/' . $post->url)
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
		$this->path = '/' . join('/', $this->path);
		$this->description = 'No results for ' . $this->URLToString();
		$this->keywords = '';
		$this->title = $title_prefix .  ' (' . $code . ')';

		$template          = Template::load('error_page');
		$template->home    = URL;
		$template->message = "Nothing found for <wbr /><var>{$this->URLToString()}</var>";
		$template->link    = $this->url;

		if ($dump) {
			$template->dump = print_r(parse_url($this->URLToString()), true);
		} else {
			$template->dump = null;
		}

		$this->content = "{$template}";
	}
}
