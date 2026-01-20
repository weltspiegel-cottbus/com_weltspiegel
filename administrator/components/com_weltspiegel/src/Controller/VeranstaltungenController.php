<?php
/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator\Controller
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

namespace Weltspiegel\Component\Weltspiegel\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Veranstaltungen list controller class.
 *
 * @since 1.0.0
 */
class VeranstaltungenController extends AdminController
{

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  Array of configuration parameters.
	 *
	 * @return BaseDatabaseModel
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Veranstaltung', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
	{
		return parent::getModel($name, $prefix, $config);
	}
}
