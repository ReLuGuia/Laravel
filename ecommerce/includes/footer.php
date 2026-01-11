<?php
// includes/footer.php
?>
    </div> <!-- Fecha container do header -->

    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo defined('SITE_NOME') ?  : 'E-commerce'; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scripts básicos
        document.addEventListener('DOMContentLoaded', function() {
            // Exibir mensagens de alerta
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 3000);
        });
    </script>
</body>
</html>