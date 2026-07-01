<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\Table
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\Table;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\YouTubeHelper;

/**
 * Movie table class.
 *
 * @since  1.5.0
 */
class MovieTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.5.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__ws_cinetixx_movies', 'id', $db);
	}

	/**
	 * Validates movie data before saving
	 *
	 * @return bool
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public function check(): bool
	{
		parent::check();

		$this->title = trim($this->title ?? '');

		$this->trailer_id = trim($this->trailer_id ?? '');
		if ($this->trailer_id !== '') {
			if (!preg_match("/^[\w-]{11}$/", $this->trailer_id)) {
				$youTubeId = YouTubeHelper::parseYoutubeId($this->trailer_id);
				if (!$youTubeId) {
					throw new Exception('Ungültige YouTube ID oder YouTube URL.');
				}
				$this->trailer_id = $youTubeId;
			}
		}

		$this->poster     = trim($this->poster ?? '');
		$this->poster_big = trim($this->poster_big ?? '');

		return true;
	}
}
