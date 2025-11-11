<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;     // atau App\Models\User
use App\Gudang;   // <-- TAMBAHKAN INI (atau App\Models\Gudang)
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user.
     */
    public function index()
    {
        // 'with('gudang')' akan mengambil data gudang terkait
        $users = User::with('gudang')->get(); 
        return view('users.index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        $gudangs = Gudang::all(); // Ambil semua gudang untuk dropdown
        return view('users.create', compact('gudangs'));
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input baru
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'alamat' => ['nullable', 'string'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'], // Pastikan gudang_id ada di tabel gudangs
        ]);

        // Buat user baru
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'gudang_id' => $request->gudang_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit(User $user)
    {
        $gudangs = Gudang::all(); // Ambil data gudang untuk dropdown
        return view('users.edit', compact('user', 'gudangs'));
    }

    /**
     * Mengupdate data user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'alamat' => ['nullable', 'string'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'gudang_id' => ['nullable', 'exists:gudangs,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], // Password opsional saat update
        ]);

        // Ambil semua data input
        $data = $request->only('name', 'email', 'role', 'alamat', 'no_telp', 'gudang_id');

        // Jika user mengisi password baru, hash dan timpa password lama
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }


    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}