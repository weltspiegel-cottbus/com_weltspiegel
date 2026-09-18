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
 * Answers one thing for every consumer — the model that narrows the movie list
 * and the view that renders the chips: which day is selected?
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
	 * Valid values of the day parameter, mapped to their offset in days.
	 *
	 * @since 2.3.0
	 */
	public const TAGS = [
		'heute'  => 0,
		'morgen' => 1,
	];

	/**
	 * The selected day parameter, if any.
	 *
	 * @return  string|null  One of the keys of self::TAGS
	 *
	 * @since 2.3.0
	 */
	public static function activeTag(): ?string
	{
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
