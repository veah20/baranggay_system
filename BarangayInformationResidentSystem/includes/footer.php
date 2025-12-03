    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.js"></script>
    
    <!-- Include Modal Component -->
    <?php include 'modal.php'; ?>
    
    <!-- Custom JS -->
    <script>
        // Sidebar toggle for mobile with accessibility
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                const isExpanded = sidebar.classList.contains('active');
                sidebarToggle.setAttribute('aria-expanded', isExpanded);
            });
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                        sidebar.classList.remove('active');
                        sidebarToggle.setAttribute('aria-expanded', false);
                    }
                }
            });
        }
        
        // Initialize DataTables with accessibility features
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('.data-table').DataTable({
                    "pageLength": <?php echo RECORDS_PER_PAGE; ?>,
                    "ordering": true,
                    "searching": true,
                    "language": {
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        },
                        "emptyTable": "No data available in table"
                    },
                    "columnDefs": [{
                        "targets": "_all",
                        "render": function(data) {
                            // Ensure proper content escaping
                            return data !== null ? data : '';
                        }
                    }]
                });
            }
        });
        
        // Confirm delete with accessible dialog
        function confirmDelete(message) {
            const confirmMessage = message || 'Are you sure you want to delete this record?';
            return confirm(confirmMessage);
        }
        
        // Auto-hide alerts with accessibility announcement
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.addEventListener('transitionend', function() {
                    alert.remove();
                });
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
            });
        }, 5000);
        
        // Keyboard navigation enhancement
        document.addEventListener('keydown', function(e) {
            // Close dropdowns with Escape key
            if (e.key === 'Escape') {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>
