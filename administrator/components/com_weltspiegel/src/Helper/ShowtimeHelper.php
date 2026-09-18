<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\Helper
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\Helper;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Joomla\CMS\Factory;
use stdClass;

/**
 * Time related questions about Cinetixx shows.
 *
 * Single source of truth for "is this show still to come". The rule matters:
 * the API keeps delivering a show until its sales window closes, which is five
 * minutes *after* it started. Without this filter an already running film keeps
 * appearing as if it were still on — exactly the complaint that led to the
 * show-start filter on the home page. Every place that answers "what can I still
 * see" must use the same definition, or the pages contradict each other.
 *
 * @since 2.3.0
 */
abstract class ShowtimeHelper
{
	/**
	 * The timezone show times are interpreted in.
	 *
	 * Deliberately taken from the site configuration instead of relying on
	 * date()/DateTime defaults: Joomla never calls date_default_timezone_set(),
	 * so PHP would silently use whatever php.ini says. With a UTC default,
	 * "today" would end two hours early in summer.
	 *
	 * @return  DateTimeZone
	 *
	 * @since 2.3.0
	 */
	public static function timezone(): DateTimeZone
	{
		$offset = (string) Factory::getApplication()->get('offset', '');

		try {
			return new DateTimeZone($offset ?: 'Europe/Berlin');
		} catch (Exception) {
			return new DateTimeZone('Europe/Berlin');
		}
	}

	/**
	 * The current moment in the site's timezone.
	 *
	 * @return  DateTimeImmutable
	 *
	 * @since 2.3.0
	 */
	public static function now(): DateTimeImmutable
	{
		return new DateTimeImmutable('now', static::timezone());
	}

	/**
	 * Whether a show has not started yet.
	 *
	 * @param   stdClass                $show  A show as built by CinetixxHelper
	 * @param   DateTimeImmutable|null  $now   Reference point, defaults to now
	 *
	 * @return  bool  False for anything unparseable, so broken data never shows up
	 *
	 * @since 2.3.0
	 */
	public static function isUpcoming(stdClass $show, ?DateTimeImmutable $now = null): bool
	{
		$start = static::startOf($show);

		return $start !== null && $start > ($now ?? static::now());
	}

	/**
	 * Whether a show starts on a given calendar day and is still to come.
	 *
	 * @param   stdClass                $show  A show as built by CinetixxHelper
	 * @param   DateTimeImmutable       $day   Any moment on the wanted day
	 * @param   DateTimeImmutable|null  $now   Reference point, defaults to now
	 *
	 * @return  bool
	 *
	 * @since 2.3.0
	 */
	public static function isUpcomingOn(stdClass $show, DateTimeImmutable $day, ?DateTimeImmutable $now = null): bool
	{
		if (!static::isUpcoming($show, $now)) {
			return false;
		}

		$start = static::startOf($show);

		return $start !== null
			&& $start->setTimezone(static::timezone())->format('Y-m-d') === $day->format('Y-m-d');
	}

	/**
	 * Whether a movie has at least one show still to come on a given day.
	 *
	 * @param   stdClass                $movie  A movie as built by CinetixxHelper
	 * @param   DateTimeImmutable       $day    Any moment on the wanted day
	 * @param   DateTimeImmutable|null  $now    Reference point, defaults to now
	 *
	 * @return  bool
	 *
	 * @since 2.3.0
	 */
	public static function hasUpcomingShowOn(stdClass $movie, DateTimeImmutable $day, ?DateTimeImmutable $now = null): bool
	{
		$now ??= static::now();

		foreach ($movie->formats ?? [] as $format) {
			foreach ($format->shows ?? [] as $show) {
				if (static::isUpcomingOn($show, $day, $now)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Parse a show's start time.
	 *
	 * @param   stdClass  $show  A show as built by CinetixxHelper
	 *
	 * @return  DateTimeImmutable|null  Null when the value is missing or unparseable
	 *
	 * @since 2.3.0
	 */
	private static function startOf(stdClass $show): ?DateTimeImmutable
	{
		$start = trim((string) ($show->showStart ?? ''));

		if ($start === '') {
			return null;
		}

		try {
			// The API sends an ISO-8601 value including its offset, so no
			// timezone needs to be supplied here.
			return new DateTimeImmutable($start);
		} catch (Exception) {
			return null;
		}
	}
}
