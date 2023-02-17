<?php

namespace App\Http\Controllers;

use App\Services\Youtube;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(): View
    {
        return view('home');
    }

    public function recommend(Request $request): View
    {
        $input = $request->all();
        $youtube = new Youtube();

         $videos = $youtube->recommend($input);
        // $videos = [];

        return view('home', compact('videos'));
    }
}
