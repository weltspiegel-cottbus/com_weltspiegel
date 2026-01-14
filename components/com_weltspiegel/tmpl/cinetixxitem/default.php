<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$event = $this->item;
?>
<div class="border border-dark p-2 mb-4">
    <div class="d-flex d-sm-block gap-3 flex-column clearfix">
            <div class="float-start align-self-center p-1 bg-dark me-3 mb-1" style="height: 15rem; width: 10.75rem">
                <img src="<?= $event->poster ?>" alt="Filmplakat <?= $event->title ?>">
            </div>
            <h2 class="order-first"><?= $this->title ?></h2>
            <div><?= $event->text ?></div>
            <div class="mt-sm-3 fst-italic">
                Dauer: <?= $event->duration ?>,
                Sprache: <?= $event->languageShort ?>,
                FSK: <?= $event->fsk ?>
            </div>
    </div>

    <?= LayoutHelper::render('booking.showbox', $event) ?>

    <?php if (!empty($event->trailerId)): ?>
        <?= LayoutHelper::render('youtube.embed', ['videoId' => $event->trailerId]) ?>
    <?php endif; ?>

    <div class="mt-4 d-flex justify-content-around gap-2">
        <?php foreach ($event->images as $ix => $image): ?>
        <div>
            <img class="p-1 bg-dark" src="<?= $image ?>" alt="Filmbild <?= ($ix + 1) . $event->title ?>">
        </div>
        <?php endforeach; ?>
    </div>
</div>



