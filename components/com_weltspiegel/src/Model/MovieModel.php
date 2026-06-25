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
 * Model for a single movie
 *
 * @since 1.5.0
 */
class MovieModel extends ItemModel
{
	/**
	 * Method to get a movie merged with DB overrides
	 *
	 * @param   int|null  $pk
	 *
	 * @return stdClass
	 *
	 * @throws Exception
	 *
	 * @since 1.5.0
	 */
	public function getItem($pk = null): stdClass
	{
		$movieId = Factory::getApplication()->input->getString('movie_id');

		$params     = ComponentHelper::getParams('com_weltspiegel');
		$mandatorId = $params->get('mandator_id');

		$movie = CinetixxHelper::getMovie($mandatorId, $movieId);

		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__ws_cinetixx_movies', 'a'))
			->where($db->quoteName('a.movie_id') . ' = :movie_id')
			->bind(':movie_id', $movieId);

		$db->setQuery($query);

		$item = $db->loadObject();
		if (!empty($item))
		{
			if (!empty($item->trailer_id))
			{
				$movie->trailerId = $item->trailer_id;
			}
			if (!empty($item->poster))
			{
				$movie->poster = $item->poster;
			}
			if (!empty($item->poster_big))
			{
				$movie->posterBig = $item->poster_big;
			}
		}

		return $movie;
	}
}
