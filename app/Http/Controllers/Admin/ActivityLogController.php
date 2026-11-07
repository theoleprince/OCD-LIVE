<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.logs.index', [
            'logs' => AdminActivityLog::with('user')->latest()->paginate(40),
        ]);
    }
}
