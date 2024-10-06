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
