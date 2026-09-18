<?php

/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\Helper
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Session-backed preview switch for features that are not public yet.
 *
 * Lets a finished feature ship to the live site while staying invisible to
 * visitors, so it can be tried out on real data and shown to the customer
 * without a second environment.
 *
 * `?preview=<feature>` switches one on, `?preview=0` switches everything off
 * again. The state lives in the session, so it survives ordinary navigation and
 * the feature's own links need not carry the parameter around — which matters,
 * because it means the links under test are exactly the ones that will ship.
 *
 * Deliberately session-based and not a component option: an option would be
 * visible to every visitor at once, which is the opposite of what this is for.
 *
 * Usage:
 *     if (FeaturePreviewHelper::isEnabled('someFeature')) { … }
 *
 * When the feature goes live, drop the call - not this class.
 *
 * @since 2.4.0
 */
abstract class FeaturePreviewHelper
{
	/**
	 * Request parameter carrying the feature name, or 0 to switch all off.
	 *
	 * @since 2.4.0
	 */
	public const PARAM = 'preview';

	/**
	 * Where the enabled features are remembered between requests.
	 *
	 * @since 2.4.0
	 */
	private const SESSION_KEY = 'com_weltspiegel.preview';

	/**
	 * Whether the given feature is previewed for this visitor.
	 *
	 * @param   string  $feature  Feature name, as used in ?preview=<feature>
	 *
	 * @return  bool
	 *
	 * @since   2.4.0
	 */
	public static function isEnabled(string $feature): bool
	{
		$app     = Factory::getApplication();
		$session = $app->getSession();
		$input   = $app->getInput();

		$enabled = (array) $session->get(static::SESSION_KEY, []);

		if ($input->exists(static::PARAM)) {
			$requested = $input->getCmd(static::PARAM, '');

			// Anything falsy clears the lot, so one link switches everything off.
			if ($requested === '' || $requested === '0') {
				$enabled = [];
			} elseif (!\in_array($requested, $enabled, true)) {
				$enabled[] = $requested;
			}

			$session->set(static::SESSION_KEY, $enabled);
		}

		return \in_array($feature, $enabled, true);
	}
}
