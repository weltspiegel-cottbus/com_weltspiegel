<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\Helper
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\Helper;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Factory;
use Joomla\Http\Http;
use stdClass;

/**
 * Cinetixx API helper methods
 *
 * @since 1.0.0
 */
abstract class CinetixxHelper
{

	/**
	 * Cinetixx Web Service Url
	 * See: http://services.cinetixx.eu/Services/CinetixxService.asmx
	 *
	 * @since 1.0.0
	 */
	private const string svcUrl = 'https://api.cinetixx.de/Services/CinetixxService.asmx/GetShowInfoV6';

	/**
	 * Internal cached cache controller
	 *
	 * @var CallbackController
	 *
	 * @since 1.0.0
	 */
	private static CallbackController $cache;

	/**
	 * Internal helper to return the cached cache controller or create it initially
	 *
	 * @return CallbackController
	 *
	 * @since 1.0.0
	 */
	private static function getCache(): CallbackController
	{
		return static::$cache ??= Factory::getContainer()
			->get(CacheControllerFactoryInterface::class)
			->createCacheController('callback', ['defaultgroup' => 'com_weltspiegel']);
	}

	/**
	 * Method to retrieve the parsed events from the Cinetixx web service
	 *
	 * @param $mandatorId
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getCinetixxEvents($mandatorId): array
	{
		$url      = static::svcUrl . "?mandatorId=$mandatorId";
		$http     = new Http();
		$response = $http->get($url);

		$xml = simplexml_load_string($response->getBody());

		$eventIds = []; // Collecting unique event ids
		$events   = [];   // Mapped events

		foreach ($xml->Show as $show)
		{

			$eventId = (string) $show->EVENT_ID;
			$event   = null;

			if (!in_array($eventId, $eventIds))
			{
				$eventIds[] = $eventId;
				$event      = new stdClass();

				$event->eventId = $eventId;
				$event->title   = (string) $show->VERANSTALTUNGSTITEL;

				$event->text      = trim($show->TEXT);
				$event->textShort = trim($show->TEXT_SHORT);

				$event->genre         = (string) $show->GENRE;
				$event->duration      = (string) $show->SPIELDAUER_EVENT;
				$event->fsk           = (string) $show->ALTERSFREIGABE;
				$event->languageShort = (string) $show->SPRACHVERSION;
				$event->language      = (string) $show->LANGUAGE;
				$event->is3D          = (string) $show->FLAG_3D === 'true';

				$poster    = trim($show->ARTWORK) ?: null;
				$posterBig = trim($show->ARTWORK_BIG) ?: null;
				$event->poster    = $poster ?? $posterBig;
				$event->posterBig = $posterBig ?? $poster;

				$event->images = array_filter([
					(string) $show->IMAGE_1,
					(string) $show->IMAGE_2,
					(string) $show->IMAGE_3,
				], fn($img) => trim($img) !== '');

				$event->trailerUrl = trim($show->EVENT_TRAILER) ?: false;
				$event->trailerId  = YouTubeHelper::parseYoutubeId($event->trailerUrl);

				$event->startDay     = (string) $show->STARTDAY;
				$event->year         = (string) $show->YEAR;
				$event->country      = (string) $show->COUNTRY;
				$event->actor        = (string) $show->ACTOR;
				$event->director     = (string) $show->DIRECTOR;
				$event->screenwriter = (string) $show->SCREENWRITER;
				$event->music        = (string) $show->MUSIC;
				$event->camera       = (string) $show->CAMERA;

				$events[$eventId] = $event;
			}

			$showTmp = new stdClass();
			$showTmp->showId = (string) $show->SHOW_ID;
			$showTmp->showStart = (string) $show->SHOW_BEGINNING;
			$showTmp->bookingStart = (string) $show->VERKAUFSSTART;
			$showTmp->bookingEnd   = (string) $show->VERKAUFSENDE;
			$showTmp->bookingLink = (string) $show->BOOKING_LINK;
			$showTmp->hall         = (string) $show->SAAL;

			$events[$eventId]->shows[] = $showTmp;
		}

		$app = Factory::getApplication();
		if ($app->isClient("administrator"))
		{
			$app->enqueueMessage("Aktuelle Cinetixx-Daten wurden geladen.");
		}

		return $events;
	}

	/**
	 * Returns array of Cinetixx events
	 *
	 * @param   string  $mandatorId
	 *
	 * @return array
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getEvents(string $mandatorId): array
	{
		return static::getCache()->get([CinetixxHelper::class, "getCinetixxEvents"], [$mandatorId], 'cinetixx.events');
	}

	/**
	 * Returns Cinetixx event by id
	 *
	 * @param   string  $mandatorId
	 * @param   string  $eventId
	 *
	 * @return stdClass|false
	 *
	 * @since 1.0.0
	 */
	public static function getEvent(string $mandatorId, string $eventId): stdClass|false
	{
		$events = static::getCache()->get([CinetixxHelper::class, "getCinetixxEvents"], [$mandatorId], 'cinetixx.events');

		return $events[$eventId] ?? false;
	}

	/**
	 * Returns array of Cinetixx event ids
	 *
	 * @param   string  $mandatorId
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getEventIds(string $mandatorId): array
	{
		$events = static::getCache()->get([CinetixxHelper::class, "getCinetixxEvents"], [$mandatorId], 'cinetixx.events');

		return array_keys($events);
	}

	/**
	 * Parses the Cinetixx web service response into a movie-centric structure.
	 *
	 * Hierarchy: Movie (MOVIE_ID) → Format variant (EVENT_ID) → Show (SHOW_ID)
	 *
	 * @param   string  $mandatorId
	 *
	 * @return array  Keyed by MOVIE_ID
	 *
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public static function getCinetixxMovies(string $mandatorId): array
	{
		$url      = static::svcUrl . "?mandatorId=$mandatorId";
		$http     = new Http();
		$response = $http->get($url);

		$xml    = simplexml_load_string($response->getBody());
		$movies = [];

		foreach ($xml->Show as $show)
		{
			$movieId = (string) $show->MOVIE_ID;
			$eventId = (string) $show->EVENT_ID;

			// Build movie object on first encounter of this MOVIE_ID
			if (!isset($movies[$movieId]))
			{
				$movie = new stdClass();

				$movie->movieId = $movieId;
				$movie->title   = (string) $show->VERANSTALTUNGSKURZTITEL;

				$movie->text      = trim($show->TEXT);
				$movie->textShort = trim($show->TEXT_SHORT);

				$movie->genre    = (string) $show->GENRE;
				$movie->duration = (string) $show->SPIELDAUER_EVENT;
				$movie->fsk      = (string) $show->ALTERSFREIGABE;

				$poster    = trim($show->ARTWORK) ?: null;
				$posterBig = trim($show->ARTWORK_BIG) ?: null;
				$movie->poster    = $poster ?? $posterBig;
				$movie->posterBig = $posterBig ?? $poster;

				$movie->images = array_filter([
					(string) $show->IMAGE_1,
					(string) $show->IMAGE_2,
					(string) $show->IMAGE_3,
				], fn($img) => trim($img) !== '');

				$trailerUrl       = trim($show->EVENT_TRAILER) ?: false;
				$movie->trailerId = YouTubeHelper::parseYoutubeId($trailerUrl);

				$movie->startDay     = (string) $show->STARTDAY;
				$movie->year         = (string) $show->YEAR;
				$movie->country      = (string) $show->COUNTRY;
				$movie->actor        = (string) $show->ACTOR;
				$movie->director     = (string) $show->DIRECTOR;
				$movie->screenwriter = (string) $show->SCREENWRITER;
				$movie->music        = (string) $show->MUSIC;
				$movie->camera       = (string) $show->CAMERA;

				$movie->formats = [];

				$movies[$movieId] = $movie;
			}

			// Build format variant object on first encounter of this EVENT_ID
			if (!isset($movies[$movieId]->formats[$eventId]))
			{
				$format = new stdClass();

				$format->eventId      = $eventId;
				$format->title        = (string) $show->VERANSTALTUNGSTITEL;
				$format->is3D         = (string) $show->FLAG_3D === 'true';
				$format->versionType  = (string) $show->VERSIONTYPE;
				$format->languageShort = (string) $show->SPRACHVERSION;
				$format->language     = (string) $show->LANGUAGE;

				$format->shows = [];

				$movies[$movieId]->formats[$eventId] = $format;
			}

			// Always append the individual show
			$showTmp               = new stdClass();
			$showTmp->showId       = (string) $show->SHOW_ID;
			$showTmp->showStart    = (string) $show->SHOW_BEGINNING;
			$showTmp->bookingStart = (string) $show->VERKAUFSSTART;
			$showTmp->bookingEnd   = (string) $show->VERKAUFSENDE;
			$showTmp->bookingLink  = (string) $show->BOOKING_LINK;
			$showTmp->hall         = (string) $show->SAAL;

			$movies[$movieId]->formats[$eventId]->shows[] = $showTmp;
		}

		$app = Factory::getApplication();
		if ($app->isClient('administrator'))
		{
			$app->enqueueMessage('Aktuelle Cinetixx-Daten wurden geladen.');
		}

		return $movies;
	}

	/**
	 * Returns all movies from the Cinetixx web service (cached)
	 *
	 * @param   string  $mandatorId
	 *
	 * @return array  Keyed by MOVIE_ID
	 *
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public static function getMovies(string $mandatorId): array
	{
		return static::getCache()->get([CinetixxHelper::class, 'getCinetixxMovies'], [$mandatorId], 'cinetixx.movies');
	}

	/**
	 * Returns a single movie by MOVIE_ID (cached)
	 *
	 * @param   string  $mandatorId
	 * @param   string  $movieId
	 *
	 * @return stdClass|false
	 *
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public static function getMovie(string $mandatorId, string $movieId): stdClass|false
	{
		$movies = static::getCache()->get([CinetixxHelper::class, 'getCinetixxMovies'], [$mandatorId], 'cinetixx.movies');

		return $movies[$movieId] ?? false;
	}
}