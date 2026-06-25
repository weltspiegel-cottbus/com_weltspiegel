<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$movie = $this->item;
?>
<div class="border border-dark p-2 mb-4">
    <div class="d-flex d-sm-block gap-3 flex-column clearfix">
        <div class="float-start align-self-center p-1 bg-dark me-3 mb-1" style="height: 15rem; width: 10.75rem">
            <img src="<?= $movie->poster ?>" alt="Filmplakat <?= $movie->title ?>">
        </div>
        <h2 class="order-first"><?= $this->title ?></h2>
        <div><?= $movie->text ?></div>
        <div class="mt-sm-3 fst-italic">
            Dauer: <?= $movie->duration ?>,
            FSK: <?= $movie->fsk ?>
        </div>
    </div>

    <ul>
        <?php foreach ($movie->formats as $format): ?>
            <?php foreach ($format->shows as $show): ?>
                <li>
                    <?= htmlspecialchars($format->title) ?> —
                    <?= htmlspecialchars($show->showStart) ?> —
                    <?= htmlspecialchars($show->hall) ?>
                    <?php if ($show->bookingLink): ?>
                        — <a href="<?= htmlspecialchars($show->bookingLink) ?>">Tickets</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>

    <?php if (!empty($movie->trailerId)): ?>
        <?= LayoutHelper::render('com_weltspiegel.youtube.embed', ['videoId' => $movie->trailerId]) ?>
    <?php endif; ?>

    <div class="mt-4 d-flex justify-content-around gap-2">
        <?php foreach ($movie->images as $ix => $image): ?>
            <div>
                <img class="p-1 bg-dark" src="<?= $image ?>" alt="Filmbild <?= ($ix + 1) . ' ' . $movie->title ?>">
            </div>
        <?php endforeach; ?>
    </div>
</div>
