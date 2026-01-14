<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Site\View\Cinetixx
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Site\View\Cinetixx;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Weltspiegel\Component\Weltspiegel\Site\Model\CinetixxModel;

/**
 * View class for the list of Cinetixx items.
 *
 * @since 1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Page title, will be used in browser title and page.
	 * Overrides menu item settings
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected string $title;

	/**
	 * The list of Cinetixx items
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected array $items;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function display($tpl = null): void
	{
		/** @var CinetixxModel $model */
		$model = $this->getModel();
		$this->items = $model->getItems();

		$this->title = 'Programmübersicht';
		$this->setDocumentTitle($this->title);

		parent::display($tpl);
	}
}