</main>
<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> Travel Guide — Web Technologies Project 01</p>
</footer>
<script src="<?= asset('public/js/app.js') ?>"></script>
<?php if (!empty($extraScripts)): ?>
    <?php foreach ((array) $extraScripts as $script): ?>
        <script src="<?= asset('public/js/' . $script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
