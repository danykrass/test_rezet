@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">{{ __('Login') }}</div>

        <div class="card-body">
            <form id="loginForm">
                @csrf
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
                <a href="{{ route('login.google') }}">Login with Google</a>
            </form>

            <div id="loginMessage"></div>
        </div>
    </div>
@endsection
@section('scripts')
<script>

    const loginForm = document.getElementById('loginForm');
    const loginMessage = document.getElementById('loginMessage');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = loginForm.elements['email'].value;
        const password = loginForm.elements['password'].value;

        if (!validateEmail(email)) {
            loginMessage.textContent = 'Invalid email address';
            return;
        }

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
                    if (data.message) {
                        loginMessage.textContent = data.message;
                    } else {
                        loginMessage.textContent = 'Logged in successfully';
                        loginForm.reset();
                        window.location.href = '/';
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

    function validateEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }

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