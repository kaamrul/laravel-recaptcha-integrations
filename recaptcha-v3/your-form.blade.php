<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>

<form method="POST" action="{{ route('your-route-name') }}" id="contactForm">
    @csrf

    <input type="text" name="name" value="{{ old('name') }}" placeholder="Your Name" required>
    @error('name')
        <div class="error-message">{{ $message }}</div>
    @enderror

    <input type="email" name="email" value="{{ old('email') }}" placeholder="Your Email" required>
    @error('email')
        <div class="error-message">{{ $message }}</div>
    @enderror

    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

    <button type="submit">Submit</button>
</form>

<script>
    grecaptcha.ready(function() {
        grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {
            action: 'submit'
        }).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
        });
    });
</script>
