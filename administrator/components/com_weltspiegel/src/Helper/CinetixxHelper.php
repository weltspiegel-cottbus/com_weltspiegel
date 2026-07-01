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

				// FIXME: Titel-Quelle ist eine ungelöste Baustelle — KEINE der beiden Optionen ist sauber!
				// ---------------------------------------------------------------------------------------
				// Vorher: VERANSTALTUNGSKURZTITEL — gedacht für Kassendisplays (zeichenbegrenzt), daher
				//   teils unschöne, manuell gekürzte Titel (z.B. "Sylvesterkonzert", "Obsession").
				// Jetzt:  VERANSTALTUNGSTITEL — von Cinetixx AUTOGENERIERT aus dem Filmtitel + redundanten
				//   Zusätzen (3D, Sprache D/OmU/OV, FSK). Der Klient entfernt diese Redundanz aktuell
				//   manuell PRO Show im Cinetixx-Desktop-Client. Zusätzlich ist dieser Titel EVENT-Ebene
				//   (variiert je Format-Variante) und wir greifen hier nur die ERSTE Show ab — die nach
				//   wenigen Stunden schon die nächste ist. Also ebenfalls unzuverlässig.
				// → Die finale Lösung erfordert ein Gespräch mit Cinetixx (ein sauberes Titel-Feld) und
				//   ist Aufgabe des Klienten. Bis dahin nutzen wir bewusst VERANSTALTUNGSTITEL, um das
				//   Problem sichtbar zu machen. Siehe docs/API.md (MOVIE→EVENT→SHOW) für Beispiele.
				// Übergangs-Workaround: Der Klient kann seit v2.2.0 pro Film einen Titel-Override im
				//   Admin-Bereich (Cinetixx-Filme bearbeiten) hinterlegen, siehe `#__ws_cinetixx_movies.title`.
				//   Dieser hat Vorrang (siehe site-seitige MovieModel/MoviesModel), ändert aber nichts an
				//   der grundsätzlichen Baustelle hier.
				$movie->title = (string) $show->VERANSTALTUNGSTITEL;

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

	/**
	 * Returns array of MOVIE_IDs (cached)
	 *
	 * @param   string  $mandatorId
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public static function getMovieIds(string $mandatorId): array
	{
		$movies = static::getCache()->get([CinetixxHelper::class, 'getCinetixxMovies'], [$mandatorId], 'cinetixx.movies');

		return array_keys($movies);
	}
}