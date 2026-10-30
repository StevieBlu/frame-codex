    </main>
    <footer class="site-footer">
        <div class="container py-4">
            <p class="mb-1">Frame Codex is a fan-made catalog for practice and portfolio use.</p>
            <p class="mb-0">Warframe is a trademark of Digital Extremes. This site is not affiliated with or endorsed by Digital Extremes.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($loadCatalogJs): ?>
        <script src="js/catalog.js"></script>
    <?php endif; ?>
    <?php if (!empty($loadSquadJs)): ?>
        <script src="js/squad.js"></script>
    <?php endif; ?>
</body>
</html>
