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
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
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
	 * @param   string  $tpl
	 * @throws Exception
	 * @since 1.5.0
	 */
	public function display($tpl = null): void
	{
		/** @var MoviesModel $model */
		$model       = $this->getModel();
		$this->items = $model->getItems();

		$this->title = 'Programmübersicht';
		$this->setDocumentTitle($this->title);

		parent::display($tpl);
	}
}
