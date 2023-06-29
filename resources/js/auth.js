document.addEventListener('DOMContentLoaded', function () {
    var loginForm = document.getElementById('login-form');
    var registerForm = document.getElementById('register-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            if (!loginForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            loginForm.classList.add('was-validated');
        }, false);
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            if (!registerForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            registerForm.classList.add('was-validated');
        }, false);

        navigator.geolocation.getCurrentPosition(
            function(position) {
              const latitude = position.coords.latitude;
              const longitude = position.coords.longitude;
              // Отправьте полученные координаты на сервер для сохранения
            },
            function(error) {
              console.error('Ошибка получения геолокации:', error);
            }
          );
    }
});

  