 <!-- Main Content goes here -->
 </main>
</div>
<script src="app.js"></script>

<!-- Initialize overlay functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('overlay');
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const pc = document.getElementById('profcard');
    
    if (overlay && sidebar && toggleBtn) {
        // Toggle sidebar
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            
        });
    }
});
</script>
</body>
</html>