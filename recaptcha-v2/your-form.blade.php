<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<form method="POST" action="{{ route('your-route-name') }}">
    @csrf

    <input type="text" name="name" value="{{ old('name') }}" placeholder="Your Name" required>
    @error('name')
        <div class="error-message">{{ $message }}</div>
    @enderror

    <input type="email" name="email" value="{{ old('email') }}" placeholder="Your Email" required>
    @error('email')
        <div class="error-message">{{ $message }}</div>
    @enderror

    <!-- Google reCAPTCHA widget -->
    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
    @error('g-recaptcha-response')
        <div class="error-message">{{ $message }}</div>
    @enderror

    <button type="submit">Submit</button>
</form>
