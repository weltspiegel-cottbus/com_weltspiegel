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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;
use stdClass;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\CinetixxHelper;


/**
 * Model for a single Cinetixx item
 *
 * @since 1.0.0
 */
class CinetixxitemModel extends ItemModel
{

	/**
	 * Method to get a Cinetixx item
	 *
	 * @param $pk int|null
	 *
	 * @return stdClass
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function getItem($pk = null): stdClass
	{
		$eventId = Factory::getApplication()->input->getInt('event_id');

		$params     = ComponentHelper::getParams('com_weltspiegel');
		$mandatorId = $params->get('mandator_id');

		$event = CinetixxHelper::getEvent($mandatorId, $eventId);

		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__ws_cinetixx_events', 'a'))
			->where($db->quoteName('a.event_id') . ' = :event_id')
			->bind(':event_id', $eventId);

		$db->setQuery($query);

		$item = $db->loadObject();
		if (!empty($item))
		{
			if (!empty($item->trailer_id))
			{
				$event->trailerId = $item->trailer_id;
			}
			if (!empty($item->poster))
			{
				$event->poster = $item->poster;
			}
			if (!empty($item->poster_big))
			{
				$event->posterBig = $item->poster_big;
			}
		}

		return $event;
	}
}