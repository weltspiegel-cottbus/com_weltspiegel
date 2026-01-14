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
 * Controller for a single Cinetixx item
 *
 * @since  1.0.0
 */
class CinetixxItemController extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_WELTSPIEGEL_CINETIXX';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $view_list = 'cinetixx';

	/**
	 *  Method to prepared editing an existing record.
	 *  Injects the eventId in the user session
	 *
	 * @param $key
	 * @param $urlVar
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function edit($key = null, $urlVar = null): bool
	{
		$eventId = $this->input->get('event_id');
		$this->app->setUserState('com_weltspiegel.event_id', $eventId);

		return parent::edit($key, $urlVar);
	}
}