@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">{{ __('Login') }}</div>

        <div class="card-body">
            <form id="loginForm">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" required>
                    <span class="text-danger" id="emailError"></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" required>
                    <span class="text-danger" id="passwordError"></span>
                </div>
                <button type="submit" class="btn btn-success">Login</button>
                <a href="{{ route('login.google') }}" class="btn btn-danger" aria-label="Login with Google+" title="Login with Google+"><svg xmlns="http://www.w3.org/2000/svg" height="1.25em" viewBox="0 0 488 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg></a>
                <a href="{{ route('register') }}" class="btn btn-primary" title="Registration">Sign up</a>
            </form>
            <div id="loginMessage"></div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const loginForm = document.getElementById('loginForm');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const loginMessage = document.getElementById('loginMessage');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = loginForm.elements['email'].value;
        const password = loginForm.elements['password'].value;

        try {
            const { latitude, longitude } = await getLocation();

            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('lat', latitude);
            formData.append('long', longitude);

            fetch('/login', {
                method: 'POST',
                body: formData,
            })
            .then((response) => response.json())
            .then((data) => {
                // Clear previous error messages
                emailError.textContent = '';
                passwordError.textContent = '';
                loginMessage.textContent = '';

                if (data.errors) {
                    if (data.errors.email) {
                        emailError.textContent = data.errors.email;
                    }
                    if (data.errors.password) {
                        passwordError.textContent = data.errors.password;
                    }
                } else if (data.token) {
                    loginMessage.textContent = 'Logged in successfully';
                    loginForm.reset();
                    window.location.href = '/';
                } else {
                    loginMessage.textContent = 'Failed to login';
                }
            })
            .catch((error) => {
                loginMessage.textContent = 'Failed to login';
                console.error(error);
            });
        } catch (error) {
            loginMessage.textContent = 'Failed to get geolocation';
            console.error(error);
        }
    });

    function getLocation() {
        return new Promise((resolve, reject) => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude, longitude } = position.coords;
                        resolve({ latitude, longitude });
                    },
                    (error) => {
                        reject(error);
                    }
                );
            } else {
                reject(new Error('Geolocation is not supported by this browser.'));
            }
        });
    }
</script>
@endsection
