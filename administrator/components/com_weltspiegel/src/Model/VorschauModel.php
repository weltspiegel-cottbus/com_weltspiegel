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
use Joomla\CMS\Table\Content as ContentTable;
use Joomla\CMS\Table\Table;
use stdClass;
use Weltspiegel\Component\Weltspiegel\Administrator\Helper\YouTubeHelper;

/**
 * Item Model for a Vorschau item.
 *
 * @since  1.0.0
 */
class VorschauModel extends AdminModel
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
	public function getTable($name = 'Content', $prefix = 'Table', $options = [])
	{
		return new ContentTable($this->getDbo());
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
		$form = $this->loadForm('com_weltspiegel.vorschau',
			'vorschau', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item && is_object($item))
		{
			// Decode attribs JSON for form binding
			if (!empty($item->attribs))
			{
				$attribs = json_decode($item->attribs, true);
				if (is_array($attribs))
				{
					$item->attribs = $attribs;
				}
			}
		}

		return $item;
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
		$data = $app->getUserState('com_weltspiegel.edit.vorschau.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function save($data)
	{
		// Ensure the article is in the correct category
		$data['catid'] = 8; // Vorschau category

		// Set article type
		if (empty($data['id']))
		{
			$data['created_by'] = Factory::getApplication()->getIdentity()->id;
		}

		// Default state to published
		if (!isset($data['state']))
		{
			$data['state'] = 1;
		}

		// Set language to all languages
		if (!isset($data['language']))
		{
			$data['language'] = '*';
		}

		// Parse and validate YouTube URL/ID
		if (!empty($data['attribs']['youtube_url']))
		{
			$input = trim($data['attribs']['youtube_url']);
			$videoId = YouTubeHelper::parseYoutubeId($input);

			if ($videoId === false)
			{
				// If parsing fails, check if it's already just an ID (11 characters, alphanumeric + dash/underscore)
				if (preg_match('/^[\w-]{11}$/', $input))
				{
					$videoId = $input;
				}
				else
				{
					// Invalid format - set error on the model
					$this->setError('Ungültige YouTube Video ID oder URL. Bitte gib eine gültige YouTube URL oder Video ID ein (z.B. dQw4w9WgXcQ oder https://www.youtube.com/watch?v=dQw4w9WgXcQ).');
					return false;
				}
			}

			// Store only the video ID
			$data['attribs']['youtube_url'] = $videoId;
		}

		return parent::save($data);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   \Joomla\CMS\Table\Table  $table  The Table object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareTable($table)
	{
		// Ensure attribs is a JSON string
		if (isset($table->attribs) && is_array($table->attribs))
		{
			$table->attribs = json_encode($table->attribs);
		}
	}
}
