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
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\DayFilterHelper;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\ShowtimeHelper;

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
	 * Set when the requested day had nothing left and another one is shown instead.
	 *
	 * @var string|null
	 *
	 * @since 2.3.0
	 */
	private ?string $fallbackFrom = null;

	/**
	 * The complete movie list, before any day filtering.
	 *
	 * @var array|null
	 *
	 * @since 2.3.0
	 */
	private ?array $allMovies = null;

	/**
	 * The day the listing actually shows — differs from the requested one after a fallback.
	 *
	 * @var \DateTimeImmutable|null
	 *
	 * @since 2.3.0
	 */
	private ?\DateTimeImmutable $effectiveDay = null;

	/**
	 * The day the listing actually shows, as a tag — null once it shows everything.
	 *
	 * @var string|null
	 *
	 * @since 2.3.0
	 */
	private ?string $effectiveTag = null;

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
				if (!empty($item->title)) {
					$movies[$item->movie_id]->title = $item->title;
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

		$this->allMovies = $movies;

		$day = DayFilterHelper::activeDay();

		if ($day === null) {
			return $movies;
		}

		$tag                = DayFilterHelper::activeTag();
		$this->effectiveDay = $day;
		$this->effectiveTag = $tag;

		$filtered = $this->onDay($movies, $day);

		if ($filtered) {
			return $filtered;
		}

		// The requested day is empty. That is not an edge case: "today" runs out
		// every evening, and there are whole days without a screening — in November
		// the cinema is booked for an event for a week. Rather than a dead end,
		// typically reached from a bookmark, step outwards until something is left:
		// today falls back to tomorrow, and anything still empty to the full
		// programme. The view explains which of the two happened.
		$this->fallbackFrom = $tag;

		if ($tag === 'heute') {
			$tomorrow = $day->modify('+1 day');
			$filtered = $this->onDay($movies, $tomorrow);

			if ($filtered) {
				$this->effectiveDay = $tomorrow;
				$this->effectiveTag = 'morgen';

				return $filtered;
			}
		}

		// Nothing on the chosen days — show everything that is coming up.
		$this->effectiveDay = null;
		$this->effectiveTag = null;

		return $movies;
	}

	/**
	 * Reduce a movie list to those still playing on a given day.
	 *
	 * Only the list is narrowed; the show times of each movie stay complete. The
	 * day filter is a question of convenience, not of identity — someone asking
	 * for tomorrow is still interested in the film's other dates. (The feature
	 * filter for cinema series works the other way round, see docs.)
	 *
	 * @param   array              $movies  Movies keyed by movie id
	 * @param   \DateTimeImmutable  $day     Any moment on the wanted day
	 *
	 * @return  array
	 *
	 * @since 2.3.0
	 */
	private function onDay(array $movies, \DateTimeImmutable $day): array
	{
		$now = ShowtimeHelper::now();

		return array_filter(
			$movies,
			static fn($movie): bool => ShowtimeHelper::hasUpcomingShowOn($movie, $day, $now)
		);
	}

	/**
	 * Which day was asked for when its result was empty and another is shown.
	 *
	 * @return  string|null
	 *
	 * @since 2.3.0
	 */
	public function getFallbackFrom(): ?string
	{
		return $this->fallbackFrom;
	}

	/**
	 * Which of the selectable days actually have something on offer.
	 *
	 * Lets the view render a day without shows as plain text instead of a link
	 * into an empty page. "Today" runs out every evening, so this is the normal
	 * state for part of the day, not an edge case.
	 *
	 * @return  array<string, bool>  Keyed like DayFilterHelper::TAGS
	 *
	 * @throws Exception
	 *
	 * @since 2.3.0
	 */
	/**
	 * The day the listing actually shows, if it is filtered at all.
	 *
	 * After a fallback this is not the day that was asked for — the show times
	 * must highlight what is on screen, not what was requested.
	 *
	 * @return  \DateTimeImmutable|null
	 *
	 * @since 2.3.0
	 */
	public function getEffectiveDay(): ?\DateTimeImmutable
	{
		return $this->effectiveDay;
	}

	/**
	 * Which day the listing actually shows, as a tag.
	 *
	 * Drives the chips and the document title, so both describe what is on screen
	 * rather than what was asked for. Null means the full programme is shown.
	 *
	 * @return  string|null
	 *
	 * @since 2.3.0
	 */
	public function getEffectiveTag(): ?string
	{
		return $this->effectiveTag;
	}

	public function getAvailableTags(): array
	{
		if ($this->allMovies === null) {
			$this->getItems();
		}

		$now       = ShowtimeHelper::now();
		$available = [];

		foreach (DayFilterHelper::TAGS as $tag => $dayOffset) {
			$day = $now->modify('+' . $dayOffset . ' days')->setTime(0, 0);

			$available[$tag] = (bool) $this->onDay($this->allMovies ?? [], $day);
		}

		return $available;
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
			->select('id, movie_id, title, trailer_id, poster, poster_big')
			->from('#__ws_cinetixx_movies')
			->whereIn('movie_id', $movieIds);

		return $query;
	}
}
