const toggleButton = document.getElementById('toggle-btn');
const sidebar = document.getElementById('sidebar');

function toggleSidebar() {
  sidebar.classList.toggle('close');
  toggleButton.classList.toggle('rotate');

  // Close all sub-menus when toggling sidebar
  closeAllSubMenus();

  // Remove rotate class from dropdown buttons when sidebar closes
  if (sidebar.classList.contains('close')) {
    Array.from(sidebar.querySelectorAll('.dropdown-btn.rotate')).forEach(btn => {
      btn.classList.remove('rotate');
    });
  }
}

function toggleSubMenu(button) {
  if (!button.nextElementSibling.classList.contains('show')) {
    closeAllSubMenus();
  }

  button.nextElementSibling.classList.toggle('show');
  button.classList.toggle('rotate');

  if (sidebar.classList.contains('close')) {
    sidebar.classList.toggle('close');
    toggleButton.classList.toggle('rotate');
  }
}

function closeAllSubMenus() {
  Array.from(sidebar.getElementsByClassName('show')).forEach(ul => {
    ul.classList.remove('show');
    ul.previousElementSibling.classList.remove('rotate');
  });

  const element = document.getElementById("profcard");

  if (element) {
    if (sidebar.classList.contains('close')) {
      element.classList.add('hidden'); // Tailwind or CSS-based hiding
    } else {
      element.classList.remove('hidden');
    }
  }
}