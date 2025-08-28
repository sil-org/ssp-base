document.addEventListener('DOMContentLoaded', function() {
  const rememberMeCheckbox = document.getElementById('rememberMe');
  if (rememberMeCheckbox) {
    const desiredRememberMeState = localStorage.getItem('desiredRememberMeState');

    if (desiredRememberMeState === null) {
      localStorage.setItem('desiredRememberMeState', rememberMeCheckbox.checked);
    }

    if (desiredRememberMeState === 'true') {
      rememberMeCheckbox.checked = true;
    }

    if (desiredRememberMeState === 'false') {
      rememberMeCheckbox.checked = false;
    }

    rememberMeCheckbox.addEventListener('change', function() {
      localStorage.setItem('desiredRememberMeState', rememberMeCheckbox.checked);
    });
  }
});
