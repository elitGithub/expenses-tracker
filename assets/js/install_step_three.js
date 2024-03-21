const toggleAdminPassword = document.getElementById('toggleAdminPassword');
const showAdminPassword = document.getElementById('showAdminPassword');
const adminPassword = document.getElementById('admin_password');
toggleAdminPassword.addEventListener('click', () => {
  if (adminPassword.type === 'password') {
    adminPassword.type = 'text';
    showAdminPassword.className = 'fa fa-eye-slash';
  } else {
    adminPassword.type = 'password';
    showAdminPassword.className = 'fa fa-eye';
  }
});

