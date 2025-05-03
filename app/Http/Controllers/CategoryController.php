<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    //
    public function index()
    {
        $categories = Category::query()
            ->whereHas('products')
            ->select(['id', 'name', 'image'])
            ->orderBy('name')
            ->withCount('products')
            ->paginate(12);

        return Inertia::render('shop/categories/index', [
            'categories' => $categories,
        ]);
    }
}
