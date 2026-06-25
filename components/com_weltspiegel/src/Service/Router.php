<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Site\Service
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Menu\AbstractMenu;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\CinetixxHelper;

/**
 * Routing class for com_weltspiegel
 *
 * @since 1.0.0
 */
class Router extends RouterView
{
	/**
	 * Constructor
	 *
	 * @param   SiteApplication  $app   The application object
	 * @param   AbstractMenu     $menu  The menu object
	 *
	 * @since 1.0.0
	 */
	public function __construct(SiteApplication $app, AbstractMenu $menu)
	{
		$cinetixx = new RouterViewConfiguration('cinetixx');
		$this->registerView($cinetixx);

		$cinetixxitem = new RouterViewConfiguration('cinetixxitem');
		$cinetixxitem->setKey('event_id')->setParent($cinetixx);
		$this->registerView($cinetixxitem);

		$movies = new RouterViewConfiguration('movies');
		$this->registerView($movies);

		$movie = new RouterViewConfiguration('movie');
		$movie->setKey('movie_id')->setParent($movies);
		$this->registerView($movie);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/**
	 * Method to get the segment for a movie view
	 *
	 * @param   string  $id     MOVIE_ID
	 * @param   array   $query  The request query
	 *
	 * @return  array  The segment for this movie
	 *
	 * @since 1.5.0
	 */
	public function getMovieSegment(string $id, array $query): array
	{
		if (empty($id)) {
			return [];
		}

		$params     = ComponentHelper::getParams('com_weltspiegel');
		$mandatorId = $params->get('mandator_id');

		$movie = CinetixxHelper::getMovie($mandatorId, $id);

		if ($movie === false || empty($movie->title)) {
			return [$id => $id];
		}

		$slug = OutputFilter::stringURLSafe($movie->title, 'de-DE');

		if (empty($slug)) {
			return [$id => $id];
		}

		return [$id => $id . '-' . $slug];
	}

	/**
	 * Method to get the MOVIE_ID for a movie view from a segment
	 *
	 * @param   string  $segment  Segment to retrieve the MOVIE_ID from
	 * @param   array   $query    The request query
	 *
	 * @return  string|false  The MOVIE_ID or false if not found
	 *
	 * @since 1.5.0
	 */
	public function getMovieId(string $segment, array $query): string|false
	{
		if (empty($segment)) {
			return false;
		}

		$parts   = explode('-', $segment, 2);
		$movieId = $parts[0];

		if (empty($movieId)) {
			return false;
		}

		$params     = ComponentHelper::getParams('com_weltspiegel');
		$mandatorId = $params->get('mandator_id');

		$movie = CinetixxHelper::getMovie($mandatorId, $movieId);

		if ($movie === false) {
			return false;
		}

		return $movieId;
	}

	/**
	 * Method to get the segment for a cinetixxitem view
	 *
	 * @param   string  $id     ID of the event
	 * @param   array   $query  The request query
	 *
	 * @return  array  The segment for this item
	 *
	 * @since 1.0.0
	 */
	public function getCinetixxitemSegment($id, $query): array
	{
		if (empty($id)) {
			return [];
		}

		$params = ComponentHelper::getParams('com_weltspiegel');
		$mandatorId = $params->get('mandator_id');

		$event = CinetixxHelper::getEvent($mandatorId, $id);

		if ($event === false || empty($event->title)) {
			return [$id => $id];
		}

		$slug = OutputFilter::stringURLSafe($event->title, 'de-DE');

		if (empty($slug)) {
			return [$id => $id];
		}

		return [$id => $id . '-' . $slug];
	}

	/**
	 * Method to get the id for a cinetixxitem view from a segment
	 *
	 * @param   string  $segment  Segment to retrieve the ID from
	 * @param   array   $query    The request query
	 *
	 * @return  int|false  The ID or false if not found
	 *
	 * @since 1.0.0
	 */
	public function getCinetixxitemId($segment, $query): int|false
	{
		if (empty($segment)) {
			return false;
		}

		$parts = explode('-', $segment, 2);
		$id = (int) $parts[0];

		if ($id <= 0) {
			return false;
		}

		$params = ComponentHelper::getParams('com_weltspiegel');
		$mandatorId = $params->get('mandator_id');

		$event = CinetixxHelper::getEvent($mandatorId, (string) $id);

		if ($event === false) {
			return false;
		}

		return $id;
	}
}
