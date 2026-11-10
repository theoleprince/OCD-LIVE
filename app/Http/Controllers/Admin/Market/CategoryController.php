<?php

namespace App\Http\Controllers\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.market.categories.index', [
            'categories' => Category::withTrashed()->topLevel()->ordered()->with('children')->get(),
            'topLevel' => Category::active()->topLevel()->ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $category = Category::create([
            ...$data,
            'slug' => Str::slug($data['name']),
            'is_active' => true,
            'sort_order' => (int) Category::max('sort_order') + 1,
        ]);

        AdminActivityLog::record('market.category.created', $category);

        return back()->with('status', 'Catégorie « '.$category->name.' » créée.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);
        AdminActivityLog::record($category->is_active ? 'market.category.activated' : 'market.category.deactivated', $category);

        return back()->with('status', 'Catégorie '.($category->is_active ? 'activée' : 'désactivée').'.');
    }
}
