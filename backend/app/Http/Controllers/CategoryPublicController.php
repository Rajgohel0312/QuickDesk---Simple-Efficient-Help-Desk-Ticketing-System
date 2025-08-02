<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryPublicController extends Controller
{
    public function index()
    {
        return Category::where('is_active', true)->get();
    }
}
