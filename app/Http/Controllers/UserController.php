<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        // Mengambil semua user untuk ditampilkan di tabel manajemen
        $users = User::orderBy('name', 'asc')->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|string|unique:users', 
            'role'     => 'required|in:admin,pegawai',
            'password' => ['required', Password::defaults()],
        ]);

        User::create([
            'name'     => $request->name,
            'nip'      => $request->nip,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'User ' . $request->name . ' berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|string|unique:users,nip,'.$id,
            'role'     => 'required|in:admin,pegawai',
            'password' => 'nullable|min:6', // Password opsional saat update
        ]);

        $user->name = $request->name;
        $user->nip = $request->nip;
        $user->role = $request->role;

        // Hanya update password jika kolom diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        return back()->with('success', 'Informasi user ' . $user->name . ' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        // Proteksi agar admin tidak menghapus dirinya sendiri
        if ($id == Auth::id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun yang sedang digunakan.');
        }

        $user = User::findOrFail($id);
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    // Fungsi ganti password mandiri (untuk pegawai di dashboard)
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password Anda berhasil diperbarui.');
    }
}