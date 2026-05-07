<?php
/**
 * Public index — list of root-level archival_units (or search results).
 *
 * @var list<array<string, mixed>> $rows
 * @var int                        $total
 * @var string|null                $q
 * @var string|null                $level
 * @var string|null                $date_from
 * @var string|null                $date_to
 * @var bool|null                  $isSearch
 */
declare(strict_types=1);

$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$levelLabel = [
    'fonds'  => __('Fondo'),
    'series' => __('Serie'),
    'file'   => __('Fascicolo'),
    'item'   => __('Unità'),
];
$levelBadgeClass = [
    'fonds'  => 'text-bg-primary',
    'series' => 'text-bg-info',
    'file'   => 'text-bg-success',
    'item'   => 'text-bg-secondary',
];
$archiveBase = \App\Support\RouteTranslator::route('archives') ?: '/archive';
$q        = $q        ?? '';
$level    = $level    ?? '';
$dateFrom = $date_from ?? '';
$dateTo   = $date_to   ?? '';
$isSearch = $isSearch  ?? false;
$archiveUrl = htmlspecialchars(url($archiveBase), ENT_QUOTES, 'UTF-8');
?>
<link rel="stylesheet" href="<?= $e(url('/plugins/archives/assets/css/archives-public.css')) ?>">

<main class="container py-4">
    <section class="archive-hero-index">
        <h1><?= __("Archivio") ?></h1>
        <p>
            <?= __("Consulta i fondi archivistici e le collezioni documentarie. Ogni unità è descritta secondo lo standard ISAD(G) — navigazione gerarchica per fondo, serie, fascicolo, unità.") ?>
        </p>
    </section>

    <!-- Barra di ricerca -->
    <form method="GET" action="<?= $archiveUrl ?>" class="archive-search-form mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label for="arc-q" class="form-label small fw-semibold text-muted mb-1">
                    <?= __("Ricerca") ?>
                </label>
                <div class="input-group">
                    <span class="input-group-text archive-search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
                        </svg>
                    </span>
                    <input id="arc-q" type="search" name="q" value="<?= $e($q) ?>"
                           class="form-control"
                           placeholder="<?= $e(__("Titolo, reference code, descrizione…")) ?>">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label for="arc-level" class="form-label small fw-semibold text-muted mb-1">
                    <?= __("Livello") ?>
                </label>
                <select id="arc-level" name="level" class="form-select">
                    <option value=""><?= __("Tutti") ?></option>
                    <option value="fonds"  <?= $level === 'fonds'  ? 'selected' : '' ?>><?= __("Fondo")     ?></option>
                    <option value="series" <?= $level === 'series' ? 'selected' : '' ?>><?= __("Serie")     ?></option>
                    <option value="file"   <?= $level === 'file'   ? 'selected' : '' ?>><?= __("Fascicolo") ?></option>
                    <option value="item"   <?= $level === 'item'   ? 'selected' : '' ?>><?= __("Unità")     ?></option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="arc-from" class="form-label small fw-semibold text-muted mb-1">
                    <?= __("Anno dal") ?>
                </label>
                <input id="arc-from" type="number" name="date_from" value="<?= $e($dateFrom) ?>"
                       min="-9999" max="9999"
                       class="form-control"
                       placeholder="<?= $e(__("es. 1900")) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label for="arc-to" class="form-label small fw-semibold text-muted mb-1">
                    <?= __("Anno al") ?>
                </label>
                <input id="arc-to" type="number" name="date_to" value="<?= $e($dateTo) ?>"
                       min="-9999" max="9999"
                       class="form-control"
                       placeholder="<?= $e(__("es. 1950")) ?>">
            </div>
            <div class="col-6 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <?= __("Cerca") ?>
                </button>
                <?php if ($isSearch): ?>
                    <a href="<?= $archiveUrl ?>" class="btn btn-outline-secondary" title="<?= $e(__("Azzera filtri")) ?>">
                        &times;
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if ($isSearch && !empty($rows)): ?>
        <p class="text-muted small mb-3">
            <?= sprintf(__("%d risultati"), $total) ?>
            <?php if ($q !== ''): ?>
                <?= __("per") ?> <strong><?= $e($q) ?></strong>
            <?php endif; ?>
            <?php if ($level !== ''): ?>
                · <?= $e($levelLabel[$level] ?? $level) ?>
            <?php endif; ?>
            <?php if ($dateFrom !== '' || $dateTo !== ''): ?>
                · <?= $dateFrom !== '' ? $e($dateFrom) : '…' ?>–<?= $dateTo !== '' ? $e($dateTo) : '…' ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if (empty($rows)): ?>
        <?php if ($isSearch): ?>
            <div class="alert alert-secondary" role="alert">
                <?= __("Nessun risultato") ?>
                <?php if ($q !== ''): ?> <?= __("per") ?> <strong><?= $e($q) ?></strong><?php endif; ?>.
                <a href="<?= $archiveUrl ?>" class="alert-link"><?= __("Mostra tutto") ?></a>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                <strong><?= __("Nessun fondo pubblicato.") ?></strong>
                <?= __("L'archivio non contiene ancora unità di primo livello.") ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($rows as $row):
                $lvl  = (string) $row['level'];
                $badge = $levelBadgeClass[$lvl] ?? 'text-bg-secondary';
                $detailUrl = $e(url($archiveBase . '/' . slugify_text((string) $row['constructed_title']) . '-' . (int) $row['id']));
                $dateRange = '';
                if (!empty($row['date_start'])) {
                    $dateRange = (string) $row['date_start'];
                    if (!empty($row['date_end']) && $row['date_end'] !== $row['date_start']) {
                        $dateRange .= '–' . (string) $row['date_end'];
                    }
                }
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card archive-card rounded-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge <?= $e($badge) ?>"><?= $e($levelLabel[$lvl] ?? $lvl) ?></span>
                                <span class="archive-ref"><?= $e((string) $row['reference_code']) ?></span>
                            </div>
                            <h2 class="card-title h6 mb-1">
                                <a href="<?= $detailUrl ?>"><?= $e((string) $row['constructed_title']) ?></a>
                            </h2>
                            <?php if ($dateRange !== ''): ?>
                                <p class="text-muted small mb-2"><?= $e($dateRange) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($row['scope_content'])): ?>
                                <p class="card-text small text-body-secondary mb-2">
                                    <?= $e(mb_substr((string) $row['scope_content'], 0, 180)) ?><?= mb_strlen((string) $row['scope_content']) > 180 ? '…' : '' ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($row['extent'])): ?>
                                <p class="small fst-italic text-muted mb-0"><?= $e((string) $row['extent']) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!$isSearch): ?>
            <p class="text-muted small mt-3">
                <?= sprintf(__("%d unità archivistiche di primo livello."), $total) ?>
            </p>
        <?php endif; ?>
    <?php endif; ?>
</main>
