<?php

/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Weltspiegel\Component\Weltspiegel\Administrator\View\Veranstaltungen\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_WELTSPIEGEL_VERANSTALTUNGEN',
    'formURL'    => 'index.php?option=com_weltspiegel&view=veranstaltungen',
    'icon'       => 'icon-calendar',
];

$displayData['createURL'] = 'index.php?option=com_weltspiegel&task=veranstaltung.add';

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
