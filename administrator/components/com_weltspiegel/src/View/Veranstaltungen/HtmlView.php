<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\View\Veranstaltungen
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\View\Veranstaltungen;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;
use Weltspiegel\Component\Weltspiegel\Administrator\Model\VeranstaltungenModel;

/**
 * View class for the list of Veranstaltung items.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of Veranstaltung items
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected array $items;

	/**
	 * The model state
	 *
	 * @var   Registry
	 *
	 * @since 1.0.0
	 */
	protected Registry $state;

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
		/** @var VeranstaltungenModel $model */
		$model = $this->getModel();
		$this->items = $model->getItems();
		$this->state = $model->getState();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		// Check for empty state (no items at all, regardless of filters)
		if (!\count($this->items) && $model->getIsEmptyState())
		{
			$this->setLayout('emptystate');
		}

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar(): void
	{
		ToolbarHelper::title('Veranstaltungen', 'fa fa-calendar');
		ToolbarHelper::addNew('veranstaltung.add');
		ToolbarHelper::editList('veranstaltung.edit');
		ToolbarHelper::publish('veranstaltungen.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('veranstaltungen.unpublish', 'JTOOLBAR_UNPUBLISH', true);

		// Show delete when viewing trashed items, otherwise show trash
		if ($this->state->get('filter.state') == -2)
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'veranstaltungen.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		else
		{
			ToolbarHelper::trash('veranstaltungen.trash');
		}
	}
}
