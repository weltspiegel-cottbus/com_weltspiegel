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

			// Extract image_intro from images JSON for media field
			if (!empty($item->images))
			{
				$images = json_decode($item->images, true);
				if (is_array($images) && !empty($images['image_intro']))
				{
					$item->images = $images['image_intro'];
				}
				else
				{
					$item->images = '';
				}
			}

			// Combine introtext and fulltext into articletext for editor
			// (Content table will split it back on save)
			if (!empty($item->fulltext) && trim($item->fulltext) !== '')
			{
				$item->articletext = $item->introtext . '<hr id="system-readmore">' . $item->fulltext;
			}
			else
			{
				$item->articletext = $item->introtext ?? '';
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
		$isNew = empty($data['id']);

		// Ensure the article is in the correct category
		$data['catid'] = 8; // Vorschau category

		// Set defaults for new articles
		if ($isNew)
		{
			$data['created_by'] = Factory::getApplication()->getIdentity()->id;
			// Use 'now' with UTC timezone to avoid timezone issues
			$data['publish_up'] = Factory::getDate('now', 'UTC')->toSql();
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

		// Mark as component-managed article
		if (!isset($data['attribs']) || !is_array($data['attribs'])) {
			$data['attribs'] = [];
		}
		$data['attribs']['source'] = 'com_weltspiegel';

		// Convert single media field to Joomla's images JSON structure
		if (!empty($data['images']) && is_string($data['images']))
		{
			// Extract clean image path (remove Joomla media field metadata after #)
			$imagePath = $data['images'];
			if (str_contains($imagePath, '#'))
			{
				$imagePath = explode('#', $imagePath)[0];
			}

			// Create proper images JSON structure (image_intro for list, image_fulltext for single view)
			$data['images'] = json_encode([
				'image_intro' => $imagePath,
				'image_intro_alt' => '',
				'image_intro_caption' => '',
				'float_intro' => '',
				'image_fulltext' => $imagePath,
				'image_fulltext_alt' => '',
				'image_fulltext_caption' => '',
				'float_fulltext' => ''
			]);
		}
		elseif (empty($data['images']))
		{
			// Ensure empty images is proper JSON
			$data['images'] = json_encode([
				'image_intro' => '',
				'image_intro_alt' => '',
				'image_intro_caption' => '',
				'float_intro' => '',
				'image_fulltext' => '',
				'image_fulltext_alt' => '',
				'image_fulltext_caption' => '',
				'float_fulltext' => ''
			]);
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

		// Save the article
		$result = parent::save($data);

		// Create workflow association for new articles (required for Joomla 4/5)
		if ($result && $isNew)
		{
			$articleId = $this->getState($this->getName() . '.id');
			$this->createWorkflowAssociation($articleId);
		}

		// Clean content cache so new/updated articles appear immediately
		if ($result)
		{
			$this->cleanCache('com_content');
		}

		return $result;
	}

	/**
	 * Method to change the published state of articles.
	 *
	 * @param   array    $pks    A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function publish(&$pks, $value = 1)
	{
		$table = $this->getTable();
		$pks = (array) $pks;

		foreach ($pks as $pk)
		{
			if (!$table->load($pk))
			{
				$this->setError($table->getError());
				return false;
			}

			$table->state = $value;

			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}
		}

		// Clean content cache
		$this->cleanCache('com_content');

		return true;
	}

	/**
	 * Method to delete articles.
	 *
	 * @param   array  $pks  A list of the primary keys to delete.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function delete(&$pks)
	{
		$table = $this->getTable();
		$pks = (array) $pks;
		$db = $this->getDatabase();

		foreach ($pks as $pk)
		{
			if (!$table->load($pk))
			{
				$this->setError($table->getError());
				return false;
			}

			if (!$table->delete($pk))
			{
				$this->setError($table->getError());
				return false;
			}

			// Also delete workflow association
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__workflow_associations'))
				->where($db->quoteName('item_id') . ' = ' . (int) $pk)
				->where($db->quoteName('extension') . ' = ' . $db->quote('com_content.article'));
			$db->setQuery($query);
			$db->execute();
		}

		// Clean content cache
		$this->cleanCache('com_content');

		return true;
	}

	/**
	 * Create workflow association for an article.
	 * Required for articles to appear in Joomla's Content manager.
	 *
	 * @param   int  $articleId  The article ID
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function createWorkflowAssociation(int $articleId): void
	{
		$db = $this->getDatabase();

		// Check if association already exists
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__workflow_associations'))
			->where($db->quoteName('item_id') . ' = ' . $articleId)
			->where($db->quoteName('extension') . ' = ' . $db->quote('com_content.article'));

		$db->setQuery($query);
		if ($db->loadResult() > 0)
		{
			return;
		}

		// Insert workflow association (stage_id 1 = published)
		$query = $db->getQuery(true)
			->insert($db->quoteName('#__workflow_associations'))
			->columns([$db->quoteName('item_id'), $db->quoteName('stage_id'), $db->quoteName('extension')])
			->values($articleId . ', 1, ' . $db->quote('com_content.article'));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Method to save the ordering of rows.
	 * Clears content cache after reordering.
	 *
	 * @param   array  $pks    An array of primary key ids.
	 * @param   array  $order  An array of ordering values.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.1.0
	 */
	public function saveorder($pks = [], $order = [])
	{
		$result = parent::saveorder($pks, $order);

		if ($result)
		{
			$this->cleanCache('com_content');
		}

		return $result;
	}

	/**
	 * Get the reorder conditions for the table.
	 * Ensures reordering is scoped to the same category.
	 *
	 * @param   Table  $table  The table object
	 *
	 * @return  array  Conditions for reordering
	 *
	 * @since   1.1.0
	 */
	protected function getReorderConditions($table)
	{
		return ['catid = ' . (int) $table->catid];
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

		// Set ordering for new articles to end of list
		if (empty($table->id))
		{
			$db = $this->getDatabase();
			$query = $db->getQuery(true)
				->select('MAX(ordering)')
				->from($db->quoteName('#__content'))
				->where($db->quoteName('catid') . ' = ' . (int) $table->catid);
			$db->setQuery($query);
			$table->ordering = (int) $db->loadResult() + 1;
		}
	}
}
