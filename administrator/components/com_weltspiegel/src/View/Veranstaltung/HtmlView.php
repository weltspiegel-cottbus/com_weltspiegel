<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\View\Veranstaltung
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\View\Veranstaltung;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use stdClass;
use Weltspiegel\Component\Weltspiegel\Administrator\Model\VeranstaltungModel;

/**
 * View to edit a Veranstaltung item.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The form instance
	 *
	 * @var Form
	 * @since 1.0.0
	 */
	protected Form $form;

	/**
	 * The Veranstaltung item
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
		/** @var VeranstaltungModel $model */
		$model      = $this->getModel();
		$this->form = $model->getForm();
		$this->item = $model->getItem();

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);
		$title = $isNew ? 'Veranstaltung erstellen' : 'Veranstaltung bearbeiten';

		ToolbarHelper::title($title, 'fa fa-calendar');

		ToolbarHelper::apply('veranstaltung.apply');
		ToolbarHelper::save('veranstaltung.save');
		ToolbarHelper::cancel('veranstaltung.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
