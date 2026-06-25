<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Site\Model
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Site\Model;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\CinetixxHelper;

/**
 * This model supports retrieving a list of movies.
 *
 * @since 1.5.0
 */
class MoviesModel extends ListModel
{
	/**
	 * Cinetixx Mandator ID
	 *
	 * @var string
	 *
	 * @since 1.5.0
	 */
	private string $mandatorId;

	/**
	 * Constructor
	 *
	 * @param   array                     $config
	 * @param   MVCFactoryInterface|null  $factory
	 *
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$params           = ComponentHelper::getParams('com_weltspiegel');
		$this->mandatorId = $params->get('mandator_id');
	}

	/**
	 * Method to get an array of movies merged with DB overrides.
	 *
	 * @return array|false
	 *
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public function getItems(): array|false
	{
		$movies = CinetixxHelper::getMovies($this->mandatorId);
		$items  = parent::getItems();

		if ($items) {
			foreach ($items as $item) {
				if (!isset($movies[$item->movie_id])) {
					continue;
				}
				if (!empty($item->trailer_id)) {
					$movies[$item->movie_id]->trailerId = $item->trailer_id;
				}
				if (!empty($item->poster)) {
					$movies[$item->movie_id]->poster = $item->poster;
				}
				if (!empty($item->poster_big)) {
					$movies[$item->movie_id]->posterBig = $item->poster_big;
				}
			}
		}

		return $movies;
	}

	/**
	 * Build an SQL query to load the movie DB overrides.
	 *
	 * @return QueryInterface
	 *
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	protected function getListQuery(): QueryInterface
	{
		$movieIds = CinetixxHelper::getMovieIds($this->mandatorId);

		$db    = $this->getDatabase();
		$query = $db->createQuery();

		$query
			->select('id, movie_id, trailer_id, poster, poster_big')
			->from('#__ws_cinetixx_movies')
			->whereIn('movie_id', $movieIds);

		return $query;
	}
}
