<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Site\View\Cinetixxitem
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Site\View\Cinetixxitem;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use stdClass;
use Weltspiegel\Component\Weltspiegel\Site\Model\CinetixxitemModel;


/**
 * View class for a single Cinetixx item
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
	 * The single Cinetixx item
	 *
	 * @var stdClass
	 * @since 1.0.0
	 */
	protected stdClass $item;

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
		/** @var CMSApplication $app */
		$app = Factory::getApplication();
		$menu = $app->getMenu();

		// Questionable: does this always return the "Programm" top link?
		$topMenuItem = $menu->getItems('component', 'com_weltspiegel', true);
		$menu->setActive($topMenuItem->id);

		/** @var CinetixxitemModel $model */
		$model = $this->getModel();
		$this->item = $model->getItem();

		$this->title = $this->item->title;
		$this->setDocumentTitle($this->title);

		parent::display($tpl);
	}
}