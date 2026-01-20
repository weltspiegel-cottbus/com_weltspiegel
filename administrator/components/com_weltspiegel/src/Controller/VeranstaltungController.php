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
 * Controller for a single Veranstaltung item
 *
 * @since  1.0.0
 */
class VeranstaltungController extends FormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_WELTSPIEGEL_VERANSTALTUNG';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $view_list = 'veranstaltungen';
}
