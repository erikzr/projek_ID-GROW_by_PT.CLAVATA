<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        // Ambil dari session yang sudah di-set saat login
        $userData = session('user_data');
        
        if (!$userData) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $user = User::find($userData['id']);

        if (!$user) {
            return redirect('/login')->with('error', 'User not found');
        }

        return view('profile', compact('user'));
    }

    public function update(Request $request)
    {
        try {
            // Ambil dari session
            $userData = session('user_data');
            
            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $user = User::find($userData['id']);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'current_password' => 'nullable|required_with:new_password',
                'new_password' => 'nullable|min:8',
                'confirm_password' => 'nullable|required_with:new_password|same:new_password'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update nama dan email
            $user->name = $request->input('name');
            $user->email = $request->input('email');

            // Update password jika diisi
            if ($request->filled('new_password')) {
                if (!Hash::check($request->input('current_password'), $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password saat ini tidak sesuai'
                    ], 400);
                }

                $user->password = Hash::make($request->input('new_password'));
            }

            $user->save();

            // ✅ Update session user_data
            session(['user_data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diupdate',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'updated_at' => $user->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
