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
use RuntimeException;
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
	 * Seconds the Cinetixx service may take, for connecting and in total.
	 *
	 * Without a limit the request hangs until PHP's own max_execution_time ends
	 * the whole page with a fatal error. The service needs 1-3 seconds when all is
	 * well and a good deal more over a poor line, so this is generous - but well
	 * below the 30 seconds PHP allows.
	 *
	 * @since 2.5.0
	 */
	private const int requestTimeout = 15;

	/**
	 * How old the last good copy of the programme may be and still stand in for
	 * a service that does not answer, in seconds. Past shows are filtered out
	 * further down anyway, so what a stale copy still offers is only what lies
	 * ahead - a week is the point where that is no longer worth showing.
	 *
	 * @since 2.5.0
	 */
	private const int staleLifetime = 604800;

	/**
	 * The movie list as already loaded in this request, per mandator.
	 *
	 * One page asks for it many times - the router alone once per link. With the
	 * system cache switched off (as in development) every one of those asks used
	 * to be a request to the service; with it on, a read and unserialize of the
	 * whole cached list.
	 *
	 * @var array<string, array>
	 *
	 * @since 2.5.0
	 */
	private static array $loaded = [];

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
		try {
			$movies = static::fetchMovies($mandatorId);
		} catch (Exception $e) {
			// The service does not answer, or not usably. A page that still shows
			// the programme as it was a moment ago serves visitors better than an
			// error page - and since this result is cached like any other, the
			// service is asked again only after the cache lifetime, not on every
			// request.
			$stale = static::readLastGood();

			if ($stale === null) {
				throw new RuntimeException('Das Programm kann gerade nicht geladen werden.', 503, $e);
			}

			$app = Factory::getApplication();

			if ($app->isClient('administrator')) {
				$app->enqueueMessage('Cinetixx antwortet nicht - angezeigt wird der zuletzt geladene Stand.', 'warning');
			}

			return $stale;
		}

		static::writeLastGood($movies);

		return $movies;
	}

	/**
	 * Asks the Cinetixx web service for the programme and parses the answer.
	 *
	 * @param   string  $mandatorId
	 *
	 * @return array  Keyed by MOVIE_ID
	 *
	 * @throws RuntimeException  When the service is unreachable, slow or answers with something unusable
	 *
	 * @since 2.5.0
	 */
	private static function fetchMovies(string $mandatorId): array
	{
		$url      = static::svcUrl . "?mandatorId=$mandatorId";
		$http     = new Http();
		$response = $http->get($url, [], static::requestTimeout);

		if ($response->getStatusCode() !== 200) {
			throw new RuntimeException('Cinetixx antwortet mit Status ' . $response->getStatusCode());
		}

		// An HTML error page instead of the XML would otherwise put a dozen parser
		// warnings into the server log for every request.
		$previous = libxml_use_internal_errors(true);
		$xml      = simplexml_load_string((string) $response->getBody());
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		if ($xml === false) {
			throw new RuntimeException('Cinetixx liefert keine auswertbare Antwort');
		}

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
	 * The movie list, from the system cache or the service, loaded once per request.
	 *
	 * @param   string  $mandatorId
	 *
	 * @return array  Keyed by MOVIE_ID
	 *
	 * @throws Exception
	 *
	 * @since 2.5.0
	 */
	private static function load(string $mandatorId): array
	{
		return static::$loaded[$mandatorId] ??= static::getCache()
			->get([CinetixxHelper::class, 'getCinetixxMovies'], [$mandatorId], 'cinetixx.movies');
	}

	/**
	 * Where the last good copy of the programme is kept.
	 *
	 * A plain file next to the system cache rather than an entry in it: the cache
	 * may be switched off, and its entries expire after minutes, which is just
	 * what this copy has to outlive.
	 *
	 * @return string
	 *
	 * @since 2.5.0
	 */
	private static function lastGoodFile(): string
	{
		return rtrim((string) Factory::getApplication()->get('cache_path', JPATH_CACHE), '/')
			. '/com_weltspiegel-cinetixx-last-good.ser';
	}

	/**
	 * Remembers a successfully loaded programme. Best effort: failing to write
	 * only means no stand-in is available later.
	 *
	 * @param   array  $movies
	 *
	 * @return void
	 *
	 * @since 2.5.0
	 */
	private static function writeLastGood(array $movies): void
	{
		$file = static::lastGoodFile();
		$temp = $file . '.' . getmypid() . '.tmp';

		// Written aside and moved into place, so a reader never meets half a file.
		if (@file_put_contents($temp, serialize($movies), LOCK_EX) === false) {
			return;
		}

		if (!@rename($temp, $file)) {
			@unlink($temp);
		}
	}

	/**
	 * The last good copy of the programme, if there is one that is recent enough.
	 *
	 * @return array|null
	 *
	 * @since 2.5.0
	 */
	private static function readLastGood(): ?array
	{
		$file = static::lastGoodFile();

		if (!is_file($file) || time() - (int) filemtime($file) > static::staleLifetime) {
			return null;
		}

		$data = @unserialize((string) @file_get_contents($file), ['allowed_classes' => [stdClass::class]]);

		return \is_array($data) && $data !== [] ? $data : null;
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
		return static::load($mandatorId);
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
		$movies = static::load($mandatorId);

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
		$movies = static::load($mandatorId);

		return array_keys($movies);
	}
}