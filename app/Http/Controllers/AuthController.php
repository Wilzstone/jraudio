<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::check()) return redirect(Auth::user()->role === 'Owner' ? '/dashboard' : '/admin/transaksi');
        return view('auth.login');
    }
    public function prosesLogin(Request $request)
    {
        $input = $request->validate(['username' => 'required|string|max:100', 'password' => 'required|string']);
        $user = User::where('username', $input['username'])->first();
        if (!$user || !in_array($user->role, ['Owner','Karyawan'], true) || !Hash::check($input['password'], $user->password)) {
            return back()->with('error', 'Username atau password salah.');
        }
        Auth::login($user);
        $request->session()->regenerate();
        $karyawan = $user->karyawan;
        $request->session()->put([
            'role' => $user->role,
            'nama' => $karyawan?->nama_karyawan ?: $user->username,
            'username' => $user->username,
        ]);
        return redirect($user->role === 'Owner' ? '/dashboard' : '/admin/transaksi')
            ->with('welcome_login', 'Selamat datang, '.($karyawan?->nama_karyawan ?: $user->username).'!');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
