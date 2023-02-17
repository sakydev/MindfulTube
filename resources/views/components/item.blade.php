<div class="col-md-6 mt-3">
    <div class="card col-md-auto mb-3">
        <div class="row g-0">
            <div class="col-md-4">
                <div class="video-thumbnail">
                    <a href="{{ $video['url'] }}" target="_blank" title="{{ $video['title'] }}">
                        <img src="{{ $video['thumbnail'] }}" class="img-fluid rounded-start" alt="...">
                    </a>
                    <div class="bottom-right">{{ $video['details']['duration'] }}</div>
                    <div class="bottom-left">{{ $video['publishedAt'] }}</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="{{ $video['url'] }}" target="_blank" title="{{ $video['title'] }}">
                            {{ \Illuminate\Support\Str::limit($video['title'], 75) }}
                        </a>
                    </h5>
                    {{--<p class="card-text">{{ $video['description'] }}</p>--}}
                    <p class="card-text">
                        <small class="text-muted">
                            <span>
                                <img src="{{ asset("assets/svgs/address-card.svg") }}" width="16">
                                <a href="{{ $video['channelUrl'] }}" target="_blank">{{ $video['channelTitle'] }} (973K)</a>,
                            </span>
                            <img src="{{ asset("assets/svgs/eye.svg") }}" width="16"> {{ $video['details']['viewCountFormatted'] }} (73%),
                            <img src="{{ asset("assets/svgs/heart.svg") }}" width="16"> 93%,
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
