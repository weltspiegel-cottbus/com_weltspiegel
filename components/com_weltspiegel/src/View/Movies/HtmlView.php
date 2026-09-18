<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Site\View\Movies
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Site\View\Movies;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\DayFilterHelper;
use Weltspiegel\Component\Weltspiegel\Site\Model\MoviesModel;

/**
 * View class for the list of movies.
 *
 * @since 1.5.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var string
	 * @since 1.5.0
	 */
	protected string $title;

	/**
	 * @var array
	 * @since 1.5.0
	 */
	protected array $items;

	/**
	 * Whether the day filter is shown at all (temporary preview flag).
	 *
	 * @var bool
	 * @since 2.3.0
	 */
	protected bool $filterEnabled = false;

	/**
	 * Selected day, one of the keys of DayFilterHelper::TAGS, or null.
	 *
	 * @var string|null
	 * @since 2.3.0
	 */
	protected ?string $activeTag = null;

	/**
	 * Day that was asked for but had nothing left, if another one is shown.
	 *
	 * @var string|null
	 * @since 2.3.0
	 */
	protected ?string $fallbackFrom = null;

	/**
	 * Which selectable days have something on offer, keyed like DayFilterHelper::TAGS.
	 *
	 * @var array<string, bool>
	 * @since 2.3.0
	 */
	protected array $availableTags = [];

	/**
	 * The day the listing shows (Y-m-d), used to highlight it in the show times.
	 *
	 * @var string|null
	 * @since 2.3.0
	 */
	protected ?string $highlightDate = null;

	/**
	 * Editorial notice shown above the listing, empty when switched off.
	 *
	 * Independent of the day filter: it has to work in production regardless of
	 * any preview flag. Used when the house is let to an outside event and no own
	 * programme runs — the text stays stored while the switch is off, so it can be
	 * reused next year.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected string $notice = '';

	/**
	 * @param   string  $tpl
	 * @throws Exception
	 * @since 1.5.0
	 */
	public function display($tpl = null): void
	{
		/** @var MoviesModel $model */
		$model       = $this->getModel();
		$this->items = $model->getItems();

		$this->filterEnabled = DayFilterHelper::isPreviewEnabled();
		// The chips and the title describe what is on screen, not what was asked
		// for: after a fallback the requested day is not the one being shown.
		$this->activeTag     = $model->getEffectiveTag();
		$this->fallbackFrom  = $model->getFallbackFrom();
		$this->availableTags = $this->filterEnabled ? $model->getAvailableTags() : [];
		$this->highlightDate = $model->getEffectiveDay()?->format('Y-m-d');

		$params       = ComponentHelper::getParams('com_weltspiegel');
		$this->notice = $params->get('notice_enabled', 0)
			? trim((string) $params->get('notice_text', ''))
			: '';

		$this->title = 'Programmübersicht';

		// The visible heading stays as it is: it is the rotated h1 with
		// white-space:nowrap, which a longer text would push out of its container.
		// Only the document title varies, so that shared links and search results
		// say what they actually show.
		$this->setDocumentTitle(match ($this->activeTag) {
			'heute'  => 'Was läuft heute?',
			'morgen' => 'Was läuft morgen?',
			default  => $this->title,
		});

		parent::display($tpl);
	}
}
