<?php if (!defined('BASE_URL')) { die('Akses langsung tidak diizinkan.'); } ?>
</main>

<footer class="site-footer">
    <div class="container">
        <p>MABAVERSE, portal resmi Masa Orientasi Mahasiswa Baru.</p>
    </div>
</footer>

<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<?php if (isset($page_script)): ?>
<script src="<?= BASE_URL ?>assets/js/<?= e($page_script) ?>"></script>
<?php endif; ?>
</body>
</html>
