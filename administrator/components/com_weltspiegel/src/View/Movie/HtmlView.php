<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\View\Movie
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\View\Movie;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use stdClass;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\CinetixxHelper;
use Weltspiegel\Component\Weltspiegel\Administrator\Model\MovieModel;

/**
 * View to edit a movie.
 *
 * @since  1.5.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The form instance
	 *
	 * @var Form
	 * @since 1.5.0
	 */
	protected Form $form;

	/**
	 * The movie DB item
	 *
	 * @var stdClass
	 * @since 1.5.0
	 */
	protected stdClass $item;

	/**
	 * The movie title from Cinetixx
	 *
	 * @var string
	 * @since 1.5.0
	 */
	protected string $cinetixxTitle;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public function display($tpl = null): void
	{
		/** @var MovieModel $model */
		$model      = $this->getModel();
		$this->form = $model->getForm();
		$this->item = $model->getItem();

		$app     = Factory::getApplication();
		$movieId = $app->getUserState('com_weltspiegel.movie_id');

		$params     = ComponentHelper::getParams('com_weltspiegel');
		$mandatorId = $params->get('mandator_id');

		$movie               = CinetixxHelper::getMovie($mandatorId, $movieId);
		$this->cinetixxTitle = $movie ? $movie->title : $movieId;

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @throws Exception
	 *
	 * @since   1.5.0
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		ToolbarHelper::title('Cinetixx Filme: Bearbeiten', 'fa fa-film');

		ToolbarHelper::apply('movie.apply');
		ToolbarHelper::save('movie.save');
		ToolbarHelper::cancel('movie.cancel', 'JTOOLBAR_CLOSE');
	}
}
