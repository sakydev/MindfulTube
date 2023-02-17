@extends('layout')

@section('content')
    <div class="row justify-content-md-center mt-3">
        <div class="col-md-auto">
            <h3>MindFeed</h3>
        </div>
    </div>
    <div class="row">
        @foreach($videos as $video)
            @include('components.item', ['video' => $video])
        @endforeach
    </div>
@endsection()
