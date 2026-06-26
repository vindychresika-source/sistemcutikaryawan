</div> <!-- End #content-wrapper -->

    <!-- Bootstrap 5 Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Sidebar Toggle Script for Mobile -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById("sidebarToggle");
            const sidebar = document.getElementById("sidebar");
            const content = document.getElementById("content-wrapper");

            if (toggleBtn) {
                toggleBtn.addEventListener("click", function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle("active");
                });
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener("click", function(e) {
                if (window.innerWidth < 992) {
                    if (sidebar.classList.contains("active") && !sidebar.contains(e.target) && e.target !== toggleBtn && !toggleBtn.contains(e.target)) {
                        sidebar.classList.remove("active");
                    }
                }
            });
        });
    </script>
</body>
</html>
