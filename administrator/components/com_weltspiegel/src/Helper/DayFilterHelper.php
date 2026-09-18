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
use Joomla\CMS\Factory;

/**
 * The day filter of the programme page ("what is on today / tomorrow").
 *
 * Answers two things for every consumer — the model that narrows the movie list
 * and the view that renders the chips: is the feature switched on at all, and
 * which day is selected?
 *
 * @since 2.3.0
 */
abstract class DayFilterHelper
{
	/**
	 * Request parameter carrying the selected day.
	 *
	 * @since 2.3.0
	 */
	public const PARAM = 'tag';

	/**
	 * Request parameter switching the feature preview on (1) or off (0).
	 *
	 * @since 2.3.0
	 */
	public const PREVIEW_PARAM = 'preview';

	/**
	 * Where the preview state is remembered between requests.
	 *
	 * @since 2.3.0
	 */
	private const SESSION_KEY = 'com_weltspiegel.dayfilter.preview';

	/**
	 * Valid values of the day parameter, mapped to their offset in days.
	 *
	 * @since 2.3.0
	 */
	public const TAGS = [
		'heute'  => 0,
		'morgen' => 1,
	];

	/**
	 * Whether the day filter is currently visible to this visitor.
	 *
	 * TEMPORARY. The feature ships behind this flag so it can be tried out on
	 * the live site without showing up for visitors. `?preview=1` switches it on,
	 * `?preview=0` off, and the state is kept in the session — so it survives
	 * ordinary navigation and the chips do not have to carry the flag around.
	 * That way the links under test are exactly the ones that will ship.
	 *
	 * Remove this method, its callers and the parameter once the filter goes live.
	 *
	 * @return  bool
	 *
	 * @since 2.3.0
	 */
	public static function isPreviewEnabled(): bool
	{
		$app     = Factory::getApplication();
		$session = $app->getSession();
		$input   = $app->getInput();

		if ($input->exists(static::PREVIEW_PARAM)) {
			$enabled = $input->getInt(static::PREVIEW_PARAM, 0) === 1;
			$session->set(static::SESSION_KEY, $enabled);

			return $enabled;
		}

		return (bool) $session->get(static::SESSION_KEY, false);
	}

	/**
	 * The selected day parameter, if any.
	 *
	 * Returns null while the preview is off, so a shared `?tag=heute` link cannot
	 * show an ordinary visitor a filtered list without any way to undo it.
	 *
	 * @return  string|null  One of the keys of self::TAGS
	 *
	 * @since 2.3.0
	 */
	public static function activeTag(): ?string
	{
		if (!static::isPreviewEnabled()) {
			return null;
		}

		$tag = Factory::getApplication()->getInput()->getCmd(static::PARAM, '');

		return \array_key_exists($tag, static::TAGS) ? $tag : null;
	}

	/**
	 * Midnight of the selected day, if any.
	 *
	 * @return  DateTimeImmutable|null
	 *
	 * @since 2.3.0
	 */
	public static function activeDay(): ?DateTimeImmutable
	{
		$tag = static::activeTag();

		if ($tag === null) {
			return null;
		}

		return ShowtimeHelper::now()
			->modify('+' . static::TAGS[$tag] . ' days')
			->setTime(0, 0);
	}
}
