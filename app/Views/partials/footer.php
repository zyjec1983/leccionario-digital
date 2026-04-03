    </main>
    
    <footer class="mt-5 py-4 text-center text-muted border-top">
        <div class="container">
            <small>&copy; <?= date('Y') ?> Leccionario Digital. Todos los derechos reservados.</small>
        </div>
    </footer>

    <script src="<?= route('/assets/js/app.js') ?>"></script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
