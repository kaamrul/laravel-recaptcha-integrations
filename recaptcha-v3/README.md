
# Google reCAPTCHA v3 Integration in Laravel

This guide walks through integrating **Google reCAPTCHA v3** in a Laravel project to enhance security by analyzing user interactions without requiring user input (like clicking "I'm not a robot").

## Requirements

- Laravel 7.x, 8.x, or 9.x
- Google reCAPTCHA v3 Site Key and Secret Key

## Steps to Integrate Google reCAPTCHA v3

### 1. Get Google reCAPTCHA v3 Site Key and Secret Key

1. Go to the [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin).
2. Register a new site and choose **reCAPTCHA v3**.
3. You will get two keys:
   - **Site Key** (for frontend)
   - **Secret Key** (for backend verification)

### 2. Add reCAPTCHA Keys to `.env`

Add the Site Key and Secret Key to your Laravel project's `.env` file:

```dotenv
RECAPTCHA_SITE_KEY=your-site-key
RECAPTCHA_SECRET_KEY=your-secret-key
```

### 3. Update `config/services.php`

Add the following configuration for reCAPTCHA v3 in `config/services.php`:

```php
'recaptcha' => [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
],
```

### 4. Include reCAPTCHA v3 Script in Blade Template

In your Blade template, include the reCAPTCHA v3 script and call it on form submission:

```html
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
        grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'submit'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
        });
    });
</script>
```

This will trigger the reCAPTCHA verification in the background when the user interacts with the form.

### 5. Validate reCAPTCHA v3 in the Controller

In your controller, validate the form input and verify the reCAPTCHA response:

```php

<?php

namespace App\Http\Controllers;

use App\Models\YourModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class YourController extends Controller
{
	public function store(Request $request)
	{
	    // Validate form fields and reCAPTCHA response
	    $validator = Validator::make($request->all(), [
		'name' => 'required|string|max:255',
		'email' => 'required|email',
		'g-recaptcha-response' => 'required', // reCAPTCHA field validation
	    ]);

	    if ($validator->fails()) {
		return back()->withErrors($validator)->withInput();
	    }

	    // Verify reCAPTCHA response
	    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
		'secret' => config('services.recaptcha.secret_key'),
		'response' => $request->input('g-recaptcha-response'),
		'remoteip' => $request->ip(),
	    ]);

	    $recaptchaData = $response->json();

	    if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
		return back()->withErrors(['g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.'])->withInput();
	    }

	    // If reCAPTCHA validation is successful, save the form data to the database
	    $data = new YourModel();
	    $data->name = $request->name;
	    $data->email = $request->email;
	    $data->save();
	    
	    // Redirect back with success notification
	    $notification = array(
		'message'    => 'Data saved successfully!',
		'alert-type' => 'success'
	    );
	    
	    return back()->with($notification);
	}
}
```

### Breakdown:
- **Score-based Validation:** reCAPTCHA v3 returns a score based on user interaction. You can adjust the threshold for accepting submissions. In the example, a score below `0.5` is rejected.
- **Verify User IP:** It is a good practice to send the user's IP address to Google's API for validation.

### 6. Error Handling in Blade

To display validation error messages in the form (including reCAPTCHA errors), you can use Laravel's `@error` directive in the Blade template:

```html
@error('g-recaptcha-response')
    <div class="error-message">{{ $message }}</div>
@enderror
```

### 7. Styling (Optional)

You can add custom CSS to style the error messages for better visibility:

```css
.error-message {
    color: red;
    font-size: 14px;
    margin-top: 5px;
}
```

## Testing the Integration

Once the integration is complete, test the form to ensure:
1. The reCAPTCHA is executed silently when interacting with the form.
2. The form submission is blocked if the reCAPTCHA score is too low.
3. Any errors (like low reCAPTCHA scores) are correctly displayed.

## Troubleshooting

- If the reCAPTCHA token is not generated, check if the **Site Key** is correctly added.
- If reCAPTCHA validation fails frequently, consider lowering the score threshold.
- Ensure the **Secret Key** is correct and has no restrictions set.

## References

- [Google reCAPTCHA v3 Documentation](https://developers.google.com/recaptcha/docs/v3)
- [Laravel Validation Documentation](https://laravel.com/docs/validation)

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).