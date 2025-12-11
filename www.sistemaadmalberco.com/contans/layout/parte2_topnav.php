    <!-- MAIN CONTENT WRAPPER -->
    <div class="main-content">
        <!-- El contenido de cada página irá aquí -->
    </div>

    <!-- FOOTER -->
    <footer style="background: linear-gradient(135deg, #1a1f36 0%, #2c3e50 100%); padding: 2rem; text-align: center; margin-top: 3rem; border-top: 4px solid #FF6B35;">
        <p style="margin: 0; color: #ecf0f1; font-size: 0.95rem;">
            &copy; <?php echo date('Y'); ?> <strong style="background: linear-gradient(135deg, #FF6B35, #FFC107); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.1rem;">Pollería Alberco</strong> - Sistema de Gestión Administrativa
        </p>
        <p style="margin: 0.5rem 0 0 0; color: #bdc3c7; font-size: 0.85rem;">
            <i class="fas fa-code" style="color: #FF6B35;"></i> Desarrollado por <strong style="color: #FF6B35;">D3spiadado</strong> con <i class="fas fa-heart" style="color: #e74c3c; animation: heartbeat 1.5s infinite;"></i>
        </p>
    </footer>

    <style>
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
    </style>

    <!-- Bootstrap 4 -->
    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- AdminLTE App -->
    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/dist/js/adminlte.min.js"></script>

    <!-- DataTables -->
    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

    <script>
        // Collapsible Sections
        document.querySelectorAll('.section-header').forEach(header => {
            header.addEventListener('click', function() {
                const section = this.closest('.collapsible-section');
                section.classList.toggle('collapsed');
            });
        });

        // Mobile Menu Toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navbarMenu = document.querySelector('.navbar-menu');
        
        if (mobileMenuBtn && navbarMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                navbarMenu.style.display = navbarMenu.style.display === 'flex' ? 'none' : 'flex';
                navbarMenu.style.position = 'absolute';
                navbarMenu.style.top = 'var(--navbar-height)';
                navbarMenu.style.left = '0';
                navbarMenu.style.right = '0';
                navbarMenu.style.background = 'white';
                navbarMenu.style.padding = '1rem';
                navbarMenu.style.flexDirection = 'column';
                navbarMenu.style.boxShadow = 'var(--shadow-lg)';
            });
        }

        // Search functionality
        const searchInput = document.querySelector('#globalSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                // Aquí puedes implementar la búsqueda global
                console.log('Searching:', query);
            });
        }

        // Renderizar notificaciones
        function renderNotifications(data) {
            const container = document.getElementById('notifContainer');
            const badge = document.getElementById('notifBadge');
            
            if (!container) return;
            
            if (data.notificaciones && data.notificaciones.length > 0) {
                let html = '';
                data.notificaciones.forEach(notif => {
                    html += `
                        <a href="${notif.url}" class="dropdown-item d-flex align-items-center">
                            <span class="notify-icon">
                                <i class="fas ${notif.icono}"></i>
                            </span>
                            <div class="notify-text">
                                <div class="notify-title">${notif.titulo}</div>
                                <div class="notify-time">${notif.tiempo}</div>
                            </div>
                        </a>
                    `;
                });
                container.innerHTML = html;
                
                // Actualizar badge
                if (badge && data.total > 0) {
                    badge.textContent = data.total > 9 ? '9+' : data.total;
                    badge.style.display = 'block';
                }
            } else {
                container.innerHTML = '<div class="text-center py-3 text-muted"><i class="fas fa-check-circle"></i> Sin notificaciones</div>';
                if (badge) badge.style.display = 'none';
            }
        }

        // Cargar notificaciones al abrir dropdown
        const notifBtn = document.getElementById('notifBtn');
        if (notifBtn) {
            notifBtn.addEventListener('click', function() {
                fetch('<?php echo URL_BASE; ?>/controllers/dashboard/notificaciones.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderNotifications(data);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        }

        // Initialize DataTables if present
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('.datatable').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    }
                });
            }
        });
    </script>
</body>
</html>
