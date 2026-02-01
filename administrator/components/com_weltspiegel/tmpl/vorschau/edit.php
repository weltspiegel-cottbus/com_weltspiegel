<?php

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$wa = $this->document->getWebAssetManager();

$wa->useScript('keepalive');
$wa->useScript('form.validate');
?>

<form
        action="<?php echo Route::_('index.php?option=com_weltspiegel&layout=edit&id=' . (int) $this->item->id); ?>"
        method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <?php echo $this->form->renderField('title'); ?>
                    <?php echo $this->form->renderField('tagline', 'attribs'); ?>
                    <?php echo $this->form->renderField('articletext'); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <?php echo $this->form->renderField('state'); ?>
                    <?php echo $this->form->renderField('images'); ?>
                    <?php echo $this->form->renderField('youtube_url', 'attribs'); ?>
                </div>
            </div>
        </div>
    </div>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
