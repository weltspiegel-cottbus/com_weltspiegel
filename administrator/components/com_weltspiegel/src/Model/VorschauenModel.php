<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\Model
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\Model;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

/**
 * Model class supporting a list of Vorschau items
 *
 * @since 1.0.0
 */
class VorschauenModel extends ListModel
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct($config = [])
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'id', 'a.id',
				'title', 'a.title',
				'created', 'a.created',
				'modified', 'a.modified',
				'state', 'a.state',
			];
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list of Vorschau items.
	 *
	 * @return QueryInterface
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function getListQuery(): QueryInterface
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select from content (articles) table
		// Only show component-managed articles (with source marker)
		$query
			->select('a.id, a.title, a.alias, a.state, a.created, a.modified, a.catid')
			->from($db->quoteName('#__content', 'a'))
			->where($db->quoteName('a.catid') . ' = 8')
			->where($db->quoteName('a.attribs') . ' LIKE ' . $db->quote('%"source":"com_weltspiegel"%'));

		// Filter by published state
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where($db->quoteName('a.state') . ' = ' . (int) $state);
		}

		// Filter by search
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(a.title LIKE ' . $search . ')');
		}

		// Add ordering
		$orderCol = $this->state->get('list.ordering', 'a.created');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Provide a query to be used to evaluate if this is an Empty State.
	 * Override to check only for Vorschau articles, not all content.
	 *
	 * @return QueryInterface
	 *
	 * @since 1.0.0
	 */
	protected function getEmptyStateQuery(): QueryInterface
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query
			->select('COUNT(*)')
			->from($db->quoteName('#__content', 'a'))
			->where($db->quoteName('a.catid') . ' = 8')
			->where($db->quoteName('a.attribs') . ' LIKE ' . $db->quote('%"source":"com_weltspiegel"%'));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = 'a.created', $direction = 'DESC')
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		parent::populateState($ordering, $direction);
	}
}
