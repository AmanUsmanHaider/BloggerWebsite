<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResources;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CreateUsers extends Controller
{
    public function create(Request $request)
    {

        $validation = $request->validate([
            'name' => 'required',
            'email' => 'required ',
            'password' => 'required',
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');
        $userInfo = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,


        ]);
        if ($userInfo) {
            if ($role == 'admin' or $role == 'blogger') {
                $userInfo->assignRole($role);
            }
            // if ($role == 'admin') {
            //     try {
            //         $userInfo->assignRole('admin');
            //         return response()->json([
            //             'Message' => "User added successfully",
            //             'User Info' => $userInfo
            //         ]);
            //     } catch (\Exception $e) {
            //         return response()->json([
            //             'Error' => $e->getMessage(),
            //         ], 500);
            //     }
            // } elseif ($role == 'blogger') {
            //     try {
            //         $userInfo->assignRole('blogger');
            //         return response()->json([
            //             'Message' => "User added successfully",
            //             'User Info' => $userInfo
            //         ]);
            //     } catch (\Exception $e) {
            //         return response()->json([
            //             'Error' => $e->getMessage(),
            //         ], 500);
            //     }
            // }
        }
    }
}
