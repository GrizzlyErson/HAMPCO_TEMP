const toggleButton = document.getElementById('toggle-btn')
const sidebar = document.getElementById('sidebar')

function isMobile() {
  return window.innerWidth <= 800;
}

function toggleSidebar(){
  // On mobile, don't toggle the sidebar closed
  if (!isMobile()) {
    sidebar.classList.toggle('close')
    toggleButton.classList.toggle('rotate')
    closeAllSubMenus()
    
    // Remove rotate class from dropdown buttons when sidebar closes
    if(sidebar.classList.contains('close')){
      Array.from(sidebar.querySelectorAll('.dropdown-btn.rotate')).forEach(btn => {
        btn.classList.remove('rotate')
      })
    }
  }
}


function toggleSubMenu(button){

  if(!button.nextElementSibling.classList.contains('show')){
    closeAllSubMenus()
  }

  button.nextElementSibling.classList.toggle('show')
  button.classList.toggle('rotate')

  if(sidebar.classList.contains('close')){
    sidebar.classList.toggle('close')
    toggleButton.classList.toggle('rotate')
  }
}

function closeAllSubMenus(){
  Array.from(sidebar.getElementsByClassName('show')).forEach(ul => {
    ul.classList.remove('show')
    ul.previousElementSibling.classList.remove('rotate')

  })
}

// Ensure sidebar is visible on mobile
function ensureSidebarVisibleOnMobile() {
  if (!sidebar) return;
  
  if (isMobile()) {
    // On mobile, always show the sidebar
    sidebar.classList.remove('close');
    if (toggleButton) {
      toggleButton.classList.remove('rotate');
    }
    sidebar.style.display = 'block';
    sidebar.style.visibility = 'visible';
  } else {
    // On desktop, allow normal behavior
    sidebar.style.display = '';
    sidebar.style.visibility = '';
  }
}

// Handle window resize to ensure sidebar is visible on mobile
window.addEventListener('resize', function() {
  ensureSidebarVisibleOnMobile();
});

// Initial check on page load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(ensureSidebarVisibleOnMobile, 100);
  });
} else {
  setTimeout(ensureSidebarVisibleOnMobile, 100);
}