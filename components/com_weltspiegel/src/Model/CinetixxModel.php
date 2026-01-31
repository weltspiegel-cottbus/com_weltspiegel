<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Site\Model
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Site\Model;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\CinetixxHelper;

/**
 * This model supports retrieving a list of Cinetixx items.
 *
 * @since 1.0.0
 */
class CinetixxModel extends ListModel
{
	/**
	 * Cinetixx Mandator ID
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private string $mandatorId;

	/**
	 * Constructor
	 *
	 * @param   array                     $config
	 * @param   MVCFactoryInterface|null  $factory
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$params           = ComponentHelper::getParams('com_weltspiegel');
		$this->mandatorId = $params->get('mandator_id');
	}

	/**
	 * Method to get an array of Cinetixx items.
	 *
	 * @return array|false An array of Cinetixx items on success, false on failure.
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function getItems(): array|false
	{
		$events = CinetixxHelper::getEvents($this->mandatorId);
		$items  = parent::getItems();

		if ($items) {
			foreach ($items as $item) {
				if(!empty($item->trailer_id)) {
					$events[$item->event_id]->trailerId = $item->trailer_id;
				}
				if(!empty($item->poster)) {
					$events[$item->event_id]->poster = $item->poster;
				}
				if(!empty($item->poster_big)) {
					$events[$item->event_id]->posterBig = $item->poster_big;
				}
			}
		}

		return $events;
	}

	/**
	 * Build an SQL query to load the Cinetixx items.
	 *
	 * @return QueryInterface
	 *
	 * @throws \Exception
	 *
	 * @since 1.0.0
	 */
	protected function getListQuery(): QueryInterface
	{
		$eventIds = CinetixxHelper::getEventIds($this->mandatorId);

		$db    = $this->getDatabase();
		$query = $db->createQuery();

		$query
			->select('id, event_id, trailer_id, poster, poster_big')
			->from('#__ws_cinetixx_events')
			->whereIn('event_id', $eventIds);

		return $query;
	}
}