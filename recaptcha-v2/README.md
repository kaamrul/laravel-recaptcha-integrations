
# Google reCAPTCHA v2 Integration in Laravel

This guide walks through integrating **Google reCAPTCHA v2** in a Laravel project to secure your forms against spam and abuse.

## Requirements

- Laravel 7.x, 8.x, or 9.x
- Google reCAPTCHA v2 Site Key and Secret Key

## Steps to Integrate Google reCAPTCHA v2

### 1. Get Google reCAPTCHA v2 Site Key and Secret Key

1. Go to the [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin).
2. Register a new site and choose **reCAPTCHA v2**.
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

Make sure to add the reCAPTCHA configuration in `config/services.php`:

```php
'recaptcha' => [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
],
```

### 4. Update the Form in Blade Template

In your Blade template, include the reCAPTCHA widget by adding the following HTML snippet:

```html
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
```

This will display the Google reCAPTCHA widget and ensure the `g-recaptcha-response` field is included in the form data when the form is submitted.

### 5. Validate reCAPTCHA in the Controller

In the controller, you need to validate the form input, including the reCAPTCHA response. Here's an example:

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
	    ]);

	    $recaptchaData = $response->json();

	    if (!$recaptchaData['success']) {
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
1. The reCAPTCHA widget loads correctly on the frontend.
2. The form cannot be submitted without completing the reCAPTCHA.
3. Any errors (like missing reCAPTCHA response) are correctly displayed.

## Troubleshooting

- If the reCAPTCHA widget is not showing, check if the **Site Key** is correctly added.
- If reCAPTCHA validation fails, ensure the **Secret Key** is correct and there are no restrictions set on your reCAPTCHA keys.

## References

- [Google reCAPTCHA v2 Documentation](https://developers.google.com/recaptcha/docs/display)
- [Laravel Validation Documentation](https://laravel.com/docs/validation)

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).