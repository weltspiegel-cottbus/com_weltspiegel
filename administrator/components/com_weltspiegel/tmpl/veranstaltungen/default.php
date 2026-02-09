<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Weltspiegel\Component\Weltspiegel\Administrator\View\Veranstaltungen\HtmlView;

/** @var HtmlView $this */

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirection = $this->escape($this->state->get('list.direction'));
$saveOrder     = ($listOrder === 'a.ordering');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_weltspiegel&task=veranstaltungen.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>
<form action="<?= Route::_('index.php?option=com_weltspiegel&view=veranstaltungen') ?>" method="post" name="adminForm"
      id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]) ?>

        <table class="table">
            <thead>
            <tr>
                <th style="width:1%" class="text-center d-none d-md-table-cell">
                    <?= HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirection, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-ellipsis-v') ?>
                </th>
                <th style="width:1%">
                    <?= HTMLHelper::_('grid.checkall') ?>
                </th>
                <th style="width:1%">
                    <?= HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirection, $listOrder) ?>
                </th>
                <th>
                    <?= HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirection, $listOrder) ?>
                </th>
                <th style="width:10%">
                    <?= HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $listDirection, $listOrder) ?>
                </th>
                <th style="width:5%">
                    <?= HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirection, $listOrder) ?>
                </th>
            </tr>
            </thead>
            <tbody<?php if ($saveOrder) : ?> class="js-draggable" data-url="<?= $saveOrderingUrl ?>" data-direction="<?= strtolower($listDirection) ?>"<?php endif; ?>>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr data-draggable-group="1">
                    <td class="text-center d-none d-md-table-cell">
                        <?php if ($saveOrder) : ?>
                            <span class="icon-ellipsis-v" aria-hidden="true"></span>
                            <input type="text" class="hidden" name="order[]" size="5" value="<?= (int) $item->ordering ?>" />
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= HTMLHelper::_('grid.id', $i, $item->id) ?>
                    </td>
                    <td>
                        <?= HTMLHelper::_('jgrid.published', $item->state, $i, 'veranstaltungen.', true) ?>
                    </td>
                    <td>
                        <a href="<?= Route::_("index.php?option=com_weltspiegel&task=veranstaltung.edit&id=" . $item->id) ?>"
                           title="<?= \Joomla\CMS\Language\Text::_('JACTION_EDIT') ?>">
                            <?= $this->escape($item->title); ?>
                        </a>
                    </td>
                    <td>
                        <?= HTMLHelper::_('date', $item->created, \Joomla\CMS\Language\Text::_('DATE_FORMAT_LC4')) ?>
                    </td>
                    <td>
                        <?= (int) $item->id ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?= HTMLHelper::_('form.token') ?>
    </div>
</form>
