<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$wa->addInlineStyle('
.truncate-overflow {
  position: relative;
  max-height: 15rem;
  overflow-y: hidden;
}
.show-ellipsis::before {
  position: absolute;
  content: "...";
  background-color: white;
  padding: 0 0.5rem;
  bottom: 0;
  right: 0;
}
.show-ellipsis::after {
  content: "";
  position: absolute;
  right: 0;
  width: 1rem;
  height: 1rem;
  background: white;
}
');

$wa->addInlineScript('
function checkOverflow(el)
{
   var curOverflow = el.style.overflow;

   if ( !curOverflow || curOverflow === "visible" )
      el.style.overflow = "hidden";

   var isOverflowing = el.clientWidth < el.scrollWidth 
      || el.clientHeight < el.scrollHeight;

   el.style.overflow = curOverflow;

   return isOverflowing;
}
function truncate() {
  const truncates = document.querySelectorAll(".truncate-overflow");
  truncates.forEach(el => { 
    el.classList.toggle("show-ellipsis", checkOverflow(el));
  });
}
document.addEventListener("DOMContentLoaded", truncate);
window.addEventListener("resize", truncate);
');

$now = new DateTime();
$futureHeadingShown = false;

?>
<div class="mb-4">
	<h1><?php echo $this->escape($this->title); ?></h1>

	<div class="d-flex flex-column gap-3">
		<?php foreach ($this->items as $id => $event) : ?>

			<?php
			// Check if this is the first movie outside current week
			if (!$futureHeadingShown && !empty($event->shows)) {
			    try {
			        $firstShowDate = new DateTime($event->shows[0]->showStart);
			        $daysUntilFirstShow = $now->diff($firstShowDate)->days;
			        if ($daysUntilFirstShow >= 7) {
			            echo '<div class="h1 mt-4 mb-3">Demnächst</div>';
			            $futureHeadingShown = true;
			        }
			    } catch (Exception $e) {
			        // Skip if date parsing fails
			    }
			}

			$detailRoute = Route::_('index.php?view=event&event_id=' . $id);
			?>
        <div class="border border-dark p-2">
            <div class="d-flex d-sm-block gap-3 flex-column clearfix">
                <div class="float-start align-self-center p-1 bg-dark me-3 mb-1" style="height: 15rem; width: 10.75rem">
                    <img src="<?= $event->poster ?>" alt="Filmplakat <?= $event->title ?>">
                </div>
                <h3 class="order-first"><a href="<?= $detailRoute ?>"><?= $event->title ?></a></h3>
                <div class="truncate-overflow"><?= $event->text ?></div>
            </div>
            <div class="mt-sm-3 fst-italic">
                Dauer: <?= $event->duration ?>,
                Sprache: <?= $event->languageShort ?>,
                FSK: <?= $event->fsk ?>
            </div>
            <?= LayoutHelper::render('booking.showbox', $event) ?>
        </div>
		<?php endforeach; ?>

	</div>

</div>