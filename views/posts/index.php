<h1 class="page-title">Browse Destinations</h1>

<div class="filter-panel">
    <div class="form-group" style="margin-bottom:16px">
        <label for="search-q">Live search</label>
        <input type="search" id="search-q" placeholder="Search title or country..." autocomplete="off">
    </div>
    <div class="filter-row">
        <div class="form-group">
            <label for="filter-country">Country</label>
            <select id="filter-country">
                <option value="">All</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?= Security::e($c) ?>"><?= Security::e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Cost level</label>
            <div class="checkbox-group">
                <?php foreach (['low', 'medium', 'high'] as $cost): ?>
                    <label><input type="radio" name="filter-cost" value="<?= $cost ?>"> <?= ucfirst($cost) ?></label>
                <?php endforeach; ?>
                <label><input type="radio" name="filter-cost" value="" checked> Any</label>
            </div>
        </div>
        <div class="form-group">
            <label>Genre</label>
            <div class="checkbox-group" id="filter-genres">
                <?php foreach ($genres as $g): ?>
                    <label><input type="checkbox" value="<?= Security::e($g) ?>"> <?= Security::e(ucfirst($g)) ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="button" class="btn btn-primary" id="apply-filters">Apply filters</button>
    </div>
</div>

<div id="posts-container">
<?php
$posts = $posts;
require ROOT_DIR . '/views/partials/post_cards.php';
?>
</div>
