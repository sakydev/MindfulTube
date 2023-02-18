<form class="row g-3" action="{{ url('recommend') }}" method="GET">
    <div class="col-md-12">
        <label for="terms" class="form-label">Terms</label>
        <textarea type="text" name="terms" class="form-control" placeholder="productivity, journaling" id="terms">{{ request()->get('terms') ?? env('YOUTUBE_SEARCH_TERMS') }}</textarea>
    </div>
    <div class="col-md-2">
        <label for="publishedAfter" class="form-label">Published</label>
        <select name="publishedAfter" class="form-select" id="publishedAfter">
            <option value="">Any</option>
            <option value="7" @selected(request()->get('publishedAfter') == 7)>7 days ago</option>
            <option value="14" @selected(request()->get('publishedAfter') == 14)>14 days ago</option>
            <option value="28" @selected(request()->get('publishedAfter') == 28)>28 days ago</option>
            <option value="90" @selected(request()->get('publishedAfter') == 90)>90 days ago</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="videoDefinition" class="form-label">Quality</label>
        <select name="videoDefinition" class="form-select" id="videoDefinition">
            <option value="any">Any</option>
            <option value="high" @selected(request()->get('videoDefinition') == 'high')>High</option>
            <option value="standard" @selected(request()->get('videoDefinition') == 'standard')>Standard</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="videoDuration" class="form-label">Duration</label>
        <select name="videoDuration" class="form-select" id="videoDuration">
            <option value="any">Any</option>
            <option value="long" @selected(request()->get('videoDuration') == 'long')>Long</option>
            <option value="medium" @selected(request()->get('videoDuration') == 'medium')>Medium</option>
            <option value="short" @selected(request()->get('videoDuration') == 'short')>Short</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="safeSearch" class="form-label">Safe search</label>
        <select name="safeSearch" class="form-select" id="safeSearch">
            <option value="none">Any</option>
            <option value="moderate" @selected(request()->get('safeSearch') == 'moderate')>Moderate</option>
            <option value="strict" @selected(request()->get('safeSearch') == 'strict')>Strict</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="order" class="form-label">Order</label>
        <select name="order" class="form-select" id="order">
            <option value="">Any</option>
            <option value="date" @selected(request()->get('order') == 'date')>Date</option>
            <option value="viewCount" @selected(request()->get('order') == 'viewCount')>Most viewed</option>
            <option value="videoCount" @selected(request()->get('order') == 'videoCount')>Most uploads</option>
            <option value="rating" @selected(request()->get('order') == 'rating')>Most liked</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="maxResults" class="form-label">Results</label>
        <select name="maxResults" class="form-select" id="maxResults">
            <option value="10" @selected(request()->get('maxResults') == '10')>10</option>
            <option value="25" @selected(request()->get('maxResults') == '25')>25</option>
            <option value="50" @selected(request()->get('maxResults') == '50')>50</option>
            <option value="100" @selected(request()->get('maxResults') == '100')>100</option>
        </select>
    </div>
    <div class="collapse" id="advancedFilters">
        <div class="col-md-3">
            <label for="relevanceLanguage" class="form-label">Language</label>
            <select name="relevanceLanguage" class="form-select" id="relevanceLanguage">
                <option value="en" selected="selected">English</option>
                <option>Chinese</option>
                <option>Spanish</option>
                <option>Arabic</option>
                <option>Bengali</option>
                <option>Hindi</option>
                <option>Russian</option>
                <option>Portuguese</option>
                <option>Japanese</option>
                <option>German</option>
                <option>Javanese</option>
                <option>Korean</option>
                <option>French</option>
                <option>Turkish</option>
                <option>Vietnamese</option>
                <option>Telugu</option>
                <option>Marathi</option>
                <option>Tamil</option>
                <option>Italian</option>
                <option>Urdu</option>
                <option>Gujarati</option>
                <option>Polish</option>
                <option>Ukrainian</option>
                <option>Persian</option>
                <option>Malayalam</option>
                <option>Kannada</option>
                <option>Oriya</option>
                <option>Panjabi</option>
                <option>Sunda</option>
                <option>Panjabi</option>
                <option>Romanian</option>
                <option>Bhojpuri</option>
                <option>Azerbaijani</option>
                <option>Maithili</option>
                <option>Hausa</option>
                <option>Burmese</option>
                <option>Serbo</option>
                <option>Awadhi</option>
                <option>Thai</option>
                <option>Dutch</option>
                <option>Yoruba</option>
                <option>Sindhi</option>
            </select>
        </div>
        <div class="col-md-12">
            <label for="channelId" class="form-label">Limit search to provided channels</label>
            <textarea type="text" name="channelId" class="form-control" placeholder="Comma separated list of channel IDs" id="channelId">{{ request()->get('channelId') }}</textarea>
        </div>
    </div>
    <a class="" data-bs-toggle="collapse" href="#advancedFilters" role="button" aria-expanded="false" aria-controls="advancedFilters">
        Advanced filters
    </a>
    <div class="row col-12 justify-content-md-center text-center pt-3">
        <button type="submit" class="btn btn-light col-4 m-3">Reset filters</button>
        <button type="submit" class="btn btn-dark col-4 m-3">Feed My Brain</button>
    </div>
</form>
