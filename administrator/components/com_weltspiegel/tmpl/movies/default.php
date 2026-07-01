<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Weltspiegel\Component\Weltspiegel\Administrator\View\Movies\HtmlView;

/** @var HtmlView $this */

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirection = $this->escape($this->state->get('list.direction'));

?>
<form action="<?= Route::_('index.php?option=com_weltspiegel&view=movies') ?>" method="post" name="adminForm"
      id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]) ?>

        <table class="table">
            <thead>
            <tr>
                <th>
                    <?php echo HTMLHelper::_('searchtools.sort', 'Film', 'title', $listDirection, $listOrder); ?>
                </th>
                <th>
                    Trailer (YouTube ID)
                </th>
                <th>
                    Poster
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr>
                    <th scope="row">
                        <a href="<?= Route::_('index.php?option=com_weltspiegel&task=movie.edit&id=' . $item->id . '&movie_id=' . $item->movie_id) ?>"
                           title="<?= \Joomla\CMS\Language\Text::_('JACTION_EDIT') ?>">
                            <?= $this->escape($item->title ?: $item->cinetixxTitle) ?>
                        </a>
                        <?php if ($item->title) : ?>
                            <div class="small text-secondary-emphasis">
                                Cinetixx-Titel: <?= $this->escape($item->cinetixxTitle) ?>
                            </div>
                        <?php endif; ?>
                    </th>
                    <td>
                        <?php if ($item->trailer_id) : ?>
                            <span class="badge text-bg-success"><?= $this->escape($item->trailer_id) ?></span>
                        <?php elseif ($item->cinetixxTrailerId) : ?>
                            <span class="text-secondary"><?= $this->escape($item->cinetixxTrailerId) ?> (Cinetixx)</span>
                        <?php else : ?>
                            <span class="badge text-bg-warning">— (kein Trailer)</span>
                        <?php endif; ?>
                        <?php if ($item->trailer_id) : ?>
                            <div class="small text-secondary-emphasis">
                                <span>Link zum Trailer:
                                <a href="https://youtu.be/<?= $item->trailer_id ?>"
                                   title="Öffnet neuen Tab mit dem YouTube-Video"
                                   class="link-body-emphasis link-offset-2 link-underline link-underline-opacity-0"
                                   target="_blank">
                                    youtu.be/<?= $item->trailer_id ?>
                                </a>
                                </span>
                                <?php if ($item->cinetixxTrailerId) : ?>
                                    <br/>
                                    <span>(Cinetixx-Trailer:
                                        <a href="https://youtu.be/<?= $item->cinetixxTrailerId ?>"
                                           title="Öffnet neuen Tab mit dem YouTube-Video"
                                           class="link-body-emphasis link-offset-2 link-underline link-underline-opacity-0"
                                           target="_blank">
                                            youtu.be/<?= $item->cinetixxTrailerId ?>
                                        </a>
                                    )
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($item->cinetixxTrailerId) : ?>
                            <div class="small text-secondary-emphasis">
                            <span>Cinetixx-Trailer:
                                <a href="https://youtu.be/<?= $item->cinetixxTrailerId ?>"
                                   title="Öffnet neuen Tab mit dem YouTube-Video"
                                   class="link-body-emphasis link-offset-2 link-underline link-underline-opacity-0"
                                   target="_blank">
                                    youtu.be/<?= $item->cinetixxTrailerId ?>
                                </a>
                            </span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item->poster || $item->poster_big) : ?>
                            <?php if ($item->poster) : ?>
                                <span class="badge text-bg-success">Poster</span>
                            <?php endif; ?>
                            <?php if ($item->poster_big) : ?>
                                <span class="badge text-bg-success">Groß</span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="text-secondary">Cinetixx</span>
                        <?php endif; ?>
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
