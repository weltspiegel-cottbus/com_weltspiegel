<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

$now                = new DateTime();
$futureHeadingShown = false;

?>
<div class="mb-4">
    <h1><?php echo $this->escape($this->title); ?></h1>

    <div class="d-flex flex-column gap-3">
        <?php foreach ($this->items as $movie) : ?>

            <?php
            // Find earliest show across all formats for "Demnächst" detection
            $firstShowStart = null;
            foreach ($movie->formats as $format) {
                foreach ($format->shows as $show) {
                    if ($firstShowStart === null || $show->showStart < $firstShowStart) {
                        $firstShowStart = $show->showStart;
                    }
                }
            }

            if (!$futureHeadingShown && $firstShowStart !== null) {
                try {
                    $firstShowDate      = new DateTime($firstShowStart);
                    $daysUntilFirstShow = $now->diff($firstShowDate)->days;
                    if ($daysUntilFirstShow >= 7) {
                        echo '<div class="h1 mt-4 mb-3">Demnächst</div>';
                        $futureHeadingShown = true;
                    }
                } catch (Exception $e) {
                    // Skip if date parsing fails
                }
            }

            $detailRoute = Route::_('index.php?option=com_weltspiegel&view=movie&movie_id=' . $movie->movieId);
            ?>
            <div class="border border-dark p-2">
                <div class="d-flex d-sm-block gap-3 flex-column clearfix">
                    <div class="float-start align-self-center p-1 bg-dark me-3 mb-1" style="height: 15rem; width: 10.75rem">
                        <img src="<?= $movie->poster ?>" alt="Filmplakat <?= $movie->title ?>">
                    </div>
                    <h3 class="order-first"><a href="<?= $detailRoute ?>"><?= $movie->title ?></a></h3>
                    <div><?= $movie->text ?></div>
                </div>
                <div class="mt-sm-3 fst-italic">
                    Dauer: <?= $movie->duration ?>,
                    FSK: <?= $movie->fsk ?>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

</div>
