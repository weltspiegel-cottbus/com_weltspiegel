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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use stdClass;

/**
 * Item Model for a Cinetixx item.
 *
 * @since  1.0.0
 */
class CinetixxItemModel extends AdminModel
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   1.0.0
	 * @throws  \Exception
	 */
	public function getTable($name = 'Cinetixx', $prefix = 'Administrator', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A form object on success, false on failure
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = [], $loadData = true): false|Form
	{
		// Get the form.
		$form = $this->loadForm('com_weltspiegel.cinetixx',
			'cinetixx', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return false|array|stdClass
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function loadFormData(): false|array|stdClass
	{
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_weltspiegel.edit.cinetixx.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
			if (empty($data->event_id)) {
				$data->event_id = $app->getUserState("com_weltspiegel.event_id");
			}
		}

		return $data;
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function save($data)
	{
		// Need to do this manually, not sure why
		$input      = Factory::getApplication()->getInput();
		if(empty($data['id'])) {
			$data['id'] = $input->getInt('id');
		}

		return parent::save($data);
	}
}