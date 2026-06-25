<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Site\View\Movie
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Site\View\Movie;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use stdClass;
use Weltspiegel\Component\Weltspiegel\Site\Model\MovieModel;

/**
 * View class for a single movie.
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
	 * @var stdClass
	 * @since 1.5.0
	 */
	protected stdClass $item;

	/**
	 * @param   string  $tpl
	 * @throws Exception
	 * @since 1.5.0
	 */
	public function display($tpl = null): void
	{
		/** @var CMSApplication $app */
		$app  = Factory::getApplication();
		$menu = $app->getMenu();

		$topMenuItem = $menu->getItems('component', 'com_weltspiegel', true);
		$menu->setActive($topMenuItem->id);

		/** @var MovieModel $model */
		$model      = $this->getModel();
		$this->item = $model->getItem();

		$this->title = $this->item->title;
		$this->setDocumentTitle($this->title);

		parent::display($tpl);
	}
}
