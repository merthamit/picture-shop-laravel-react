<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthAdminRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Picture;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Render Admin Home Page

    public function index()
    {
        $completed = Order::where('orders.status', Order::STATUS_COMPLETED);

        $stats = [
            'hidden_reviews' => Review::where('approved', 0)->count(),
            'revenue' => (clone $completed)->join('pictures', 'pictures.id', '=', 'orders.picture_id')->sum('pictures.price'),
            'orders' => Order::count(),
            'users' => User::count(),
            'pictures' => Picture::where('status', 1)->count(),
            'categories' => Category::count(),
        ];

        // Revenue of the last 14 days for the bar chart
        $from = Carbon::today()->subDays(13);
        $daily = Order::query()
            ->join('pictures', 'pictures.id', '=', 'orders.picture_id')
            ->where('orders.created_at', '>=', $from)
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->selectRaw('DATE(orders.created_at) as day, SUM(pictures.price) as total, COUNT(*) as count')
            ->pluck('total', 'day');

        $chart = collect(range(0, 13))->map(function ($offset) use ($from, $daily) {
            $day = $from->copy()->addDays($offset);
            return ['label' => $day->translatedFormat('d M'), 'total' => (int) ($daily[$day->toDateString()] ?? 0)];
        });

        $recentOrders = Order::with(['user:id,name', 'picture'])->latest()->take(6)->get();
        $topPictures = Picture::withCount('orders')->with('user:id,name')
            ->orderByDesc('orders_count')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'chart', 'recentOrders', 'topPictures'));
    }

    public function login()
    {
        if (!auth()->guard('admin')->check()) {
            return view('login');
        }

        return redirect()->route('admin.index');
    }
    public function auth(AuthAdminRequest $request)
    {
        $remember_me = $request->has('remember_me');
        if (auth()->guard('admin')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ], $remember_me)) {
            $request->session()->regenerate();
            return redirect()->route('admin.index');
        }

        return redirect()->route('admin.login')->withInput($request->only('email'))
            ->with(['error' => __('admin.login.failed')]);
    }

    public function switchLocale(string $locale)
    {
        if (in_array($locale, config('app.supported_locales'), true)) {
            session(['admin_locale' => $locale]);
        }

        return back();
    }

    public function logout()
    {
        $locale = session('admin_locale');
        auth()->guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        session(['admin_locale' => $locale]);
        return redirect()->route('admin.login');
    }
}
