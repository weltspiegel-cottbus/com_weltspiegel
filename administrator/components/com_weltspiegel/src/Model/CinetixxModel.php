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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\CinetixxHelper;

/**
 * Model class supporting a list of Cinetixx items
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
		$config['filter_fields'] = array(
			'event_id',
			'title',
		);

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

		$orderCol = $this->state->get('list.ordering', 'id');
		$orderDirection = $this->state->get('list.direction', 'desc');

		$events = array_map(function ($event) use (&$items) {
			$mergedItem = [
				// Cinetixx event props
				"cinetixxTitle"     => $event->title,
				"cinetixxTrailerId" => $event->trailerId,
				// Database props
				"id"                => 0,
				"event_id"          => $event->eventId,
				"trailer_id"        => null,
			];

			if ($items)
			{
				$itemIx = array_search($event->eventId, array_column($items, 'event_id'));

				if ($itemIx !== false)
				{
					$mergedItem["id"]         = $items[$itemIx]->id;
					$mergedItem["trailer_id"] = $items[$itemIx]->trailer_id;
				}
			}

			return (object) $mergedItem;

		}, $events);

		if($orderCol === 'title') {
			usort($events, function ($a, $b) use ($orderDirection) {
				return ($a->cinetixxTitle <=> $b->cinetixxTitle) * ($orderDirection === 'ASC' ? 1 : -1);
			});
		}

		return $events;
	}

	/**
	 * Build an SQL query to load the Cinetixx items.
	 *
	 * @return QueryInterface
	 *
	 * @throws Exception
	 *
	 * @since   1.0
	 */
	protected function getListQuery(): QueryInterface
	{
		$eventIds = CinetixxHelper::getEventIds($this->mandatorId);

		$db    = $this->getDatabase();
		$query = $db->createQuery();

		$query
			->select('id, event_id, trailer_id')
			->from('#__ws_cinetixx_events')
			->whereIn('event_id', $eventIds);

		return $query;
	}
}