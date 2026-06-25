<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\Model
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\Model;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\CinetixxHelper;

/**
 * Model class supporting a list of movies
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
		$config['filter_fields'] = [
			'movie_id',
			'title',
		];

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

		$orderCol       = $this->state->get('list.ordering', 'id');
		$orderDirection = $this->state->get('list.direction', 'desc');

		$movies = array_map(function ($movie) use (&$items) {
			$mergedItem = [
				// Cinetixx movie props
				'cinetixxTitle'     => $movie->title,
				'cinetixxTrailerId' => $movie->trailerId,
				'cinetixxPoster'    => $movie->poster,
				'cinetixxPosterBig' => $movie->posterBig,
				// Database override props
				'id'                => 0,
				'movie_id'          => $movie->movieId,
				'trailer_id'        => null,
				'poster'            => null,
				'poster_big'        => null,
			];

			if ($items)
			{
				$itemIx = array_search($movie->movieId, array_column($items, 'movie_id'));

				if ($itemIx !== false)
				{
					$mergedItem['id']         = $items[$itemIx]->id;
					$mergedItem['trailer_id'] = $items[$itemIx]->trailer_id;
					$mergedItem['poster']     = $items[$itemIx]->poster;
					$mergedItem['poster_big'] = $items[$itemIx]->poster_big;
				}
			}

			return (object) $mergedItem;
		}, $movies);

		if ($orderCol === 'title') {
			usort($movies, function ($a, $b) use ($orderDirection) {
				return ($a->cinetixxTitle <=> $b->cinetixxTitle) * ($orderDirection === 'ASC' ? 1 : -1);
			});
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
