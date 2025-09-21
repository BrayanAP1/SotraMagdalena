// Función para mostrar/ocultar contraseña
  function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  }
  
  // Validación básica del formulario
  document.getElementById('formUsuario').addEventListener('submit', function(e) {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (password.value.length < 6) {
      e.preventDefault();
      alert('La contraseña debe tener al menos 6 caracteres');
      password.focus();
      return false;
    }
    
    if (password.value !== confirmPassword.value) {
      e.preventDefault();
      alert('Las contraseñas no coinciden');
      confirmPassword.focus();
      return false;
    }
  });