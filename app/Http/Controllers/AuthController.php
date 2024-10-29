<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Handle user login and issue a token.
     */
    public function login(Request $request)
    {
        // Validate login request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and the password is correct
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create a new token for the user
        $token = $user->createToken('mobile-app')->plainTextToken;

        // Return token in the response
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Handle user logout and revoke the current token.
     */
    public function logout(Request $request)
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }


    /**
     * Handle user registration and issue a token.
     */
    public function register(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'branch'     => 'required|string|max:255',
            'mobile_number' => 'required|string|max:15',
            'password'   => 'required|string|min:8|confirmed', // 'password_confirmation' should be sent in request
        ]);
        //|regex:/^.+@saveto\.com$/i
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'branch'     => $request->branch,
            'mobile_number' => $request->mobile_number,
            'password'   => Hash::make($request->password),
        ]);

        // Generate token for the user
        $token = $user->createToken('mobile-app')->plainTextToken;

        // Return response with token
        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function forgetPassword(Request $request)
    {

        try {
            // Validate the request email
            $request->validate([
                'email' => 'required|email|exists:users'
                //|regex:/^.+@saveto\.com$/i
            ]);
            // dd('vald');
            // Generate reset token
            $token = Str::random(60);
            // Store the token in password_resets table with the user's email
            PasswordReset::insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            Mail::to($request->email)->send(new ResetPasswordMail($token));

            // Send response back to client
            return response()->json([
                'message' => 'Password reset token sent to your email.',
                'email' => $request->email,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        }
    }

    public function resetPassword(Request $request)
    {
        // Validate the reset token and new password
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed', // 'password_confirmation' must be sent in the request
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Retrieve the reset token from the database
        $passwordReset = PasswordReset::where('email', $request->email)->where('token', $request->token)->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid token or email.'], 400);
        }

        // Reset the user's password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the password reset token after successful reset
       PasswordReset::where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully. You can now log in with your new password.']);
    }
}