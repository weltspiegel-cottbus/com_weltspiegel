<?php

/**
 * @package     Weltspiegel\Component\Weltspiegel\Administrator
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Weltspiegel\Component\Weltspiegel\Administrator\View\Vorschauen\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_WELTSPIEGEL_VORSCHAUEN',
    'formURL'    => 'index.php?option=com_weltspiegel&view=vorschauen',
    'icon'       => 'icon-eye',
];

$displayData['createURL'] = 'index.php?option=com_weltspiegel&task=vorschau.add';

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
