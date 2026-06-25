<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\Controller
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Controller for a single movie
 *
 * @since  1.5.0
 */
class MovieController extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.5.0
	 */
	protected $text_prefix = 'COM_WELTSPIEGEL_MOVIE';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  1.5.0
	 */
	protected $view_list = 'movies';

	/**
	 * Method to prepare editing an existing record.
	 * Injects the movie_id in the user session.
	 *
	 * @param $key
	 * @param $urlVar
	 *
	 * @return bool
	 *
	 * @since 1.5.0
	 */
	public function edit($key = null, $urlVar = null): bool
	{
		$movieId = $this->input->getString('movie_id');
		$this->app->setUserState('com_weltspiegel.movie_id', $movieId);

		return parent::edit($key, $urlVar);
	}
}
