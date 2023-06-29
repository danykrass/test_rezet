@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">{{ __('Register') }}</div>

        <div class="card-body">
            <form id="registerForm">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
                <button type="submit">Register</button>
                <a href="{{ route('login.google') }}">Login with Google</a>
            </form>

            <div id="registerMessage"></div>
    </div>
@endsection

@section('scripts')
<script>
    const registerForm = document.getElementById('registerForm');
    const registerMessage = document.getElementById('registerMessage');

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const firstName = registerForm.elements['first_name'].value;
        const lastName = registerForm.elements['last_name'].value;
        const email = registerForm.elements['email'].value;
        const password = registerForm.elements['password'].value;
        const confirmPassword = registerForm.elements['password_confirmation'].value;


        if (!validateEmail(email)) {
            registerMessage.textContent = 'Invalid email address';
            return;
        }

        if (password !== confirmPassword) {
            registerMessage.textContent = 'Passwords do not match';
            return;
        }
        try {
        const { latitude, longitude } = await getLocation();

        const formData = new FormData();
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('password_confirmation', confirmPassword);
        formData.append('lat', latitude);
        formData.append('long', longitude);

        fetch('/register', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                registerMessage.textContent = data.message;
            } else {
                registerMessage.textContent = 'Account created successfully';
                registerForm.reset();
                window.location.href = '/';
            }
        })
        .catch(error => {
            registerMessage.textContent = 'Failed to register';
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
