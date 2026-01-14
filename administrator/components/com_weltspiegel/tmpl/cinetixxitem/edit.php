<?php

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$wa = $this->document->getWebAssetManager();

$wa->useScript('keepalive');
$wa->useScript('form.validate');
?>
<p class="fs-3 my-3">Trailer für <span class="fst-italic fw-medium text-primary-emphasis"><?= $this->cinetixxTitle ?></span></p>
<form
        action="<?php echo Route::_('index.php?option=com_weltspiegel&layout=edit&id=' . (int) $this->item->id); ?>"
        method="post" name="adminForm" id="item-form" class="form-validate">

    <?php echo $this->form->renderField('event_id'); ?>
    <?php echo $this->form->renderField('trailer_id'); ?>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
