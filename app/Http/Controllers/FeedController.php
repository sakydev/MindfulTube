<?php

namespace App\Http\Controllers;

use App\Services\Youtube;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(): View
    {
        $youtube = new Youtube();

        $videos = $youtube->searchVideos('jordan peterson', true);

        return view('home', compact('videos'));
    }
}
