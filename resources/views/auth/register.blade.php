@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">{{ __('Register') }}</div>

        <div class="card-body">
            <form id="registerForm">
                @csrf
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" name="first_name" required>
                    <span class="text-danger" id="firstNameError"></span>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" name="last_name" required>
                    <span class="text-danger" id="lastNameError"></span>
                </div>
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
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" class="form-control" name="password_confirmation" required>
                    <span class="text-danger" id="confirmPasswordError"></span>
                </div>
                <button type="submit" class="btn btn-success">Register</button>
                <a href="{{ route('login.google') }}" class="btn btn-danger" aria-label="Login with Google+" title="Login with Google+"><svg xmlns="http://www.w3.org/2000/svg" height="1.25em" viewBox="0 0 488 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg></a>
                <a href="{{ route('login') }}" class="btn btn-primary" title="Login">Sign in</a>
                
            </form>
            <div id="registerMessage"></div>
        </div>
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
                if (data.errors) {
                    let errorMessage = '';
                    for (const key in data.errors) {
                        errorMessage += data.errors[key][0] + ' ';
                    }
                    registerMessage.textContent = errorMessage;
                } else if (data.message) {
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
            registerMessage.textContent = 'Failed to get geolocation';
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