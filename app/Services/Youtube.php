<?php

namespace App\Services;

use Google_Client;
use Google_Service_YouTube;
use DateTime;
use DateInterval;
use Illuminate\Support\Carbon;

class Youtube
{
    private Google_Client $client;
    private Google_Service_YouTube $youtubeApi;
    private int $maxResults;

    public function __construct() {
        $this->client = new Google_Client();
        $this->client->setDeveloperKey(env('YOUTUBE_API_KEY'));

        $this->youtubeApi = new Google_Service_YouTube($this->client);
        $this->maxResults = env('YOUTUBE_MAX_RESULTS');
    }
    public function searchVideos(array $parameters, bool $withDetails = false): ?array
    {
        $parameters = array_merge(
            $parameters,
            [
                'maxResults' => $parameters['maxResults'] ?? $this->maxResults,
                'type' => 'video',
            ],
        );

        $results = $this->youtubeApi->search->listSearch('id,snippet', $parameters);

        $videos = [];
        foreach ($results as $video) {
            $current = [
                'id' => $video->id->videoId,
                'title' => $video->snippet->title,
                'description' => $video->snippet->description,
                'channelId' => $video->snippet->channelId,
                'channelTitle' => $video->snippet->channelTitle,
                'publishedAt' => current(explode('T', $video->snippet->publishedAt)),
                'thumbnail' => $video->snippet->thumbnails->medium->url,
                'url' => sprintf('https://www.youtube.com/watch?v=%s', $video->id->videoId),
                'channelUrl' => sprintf('https://www.youtube.com/channel/%s', $video->snippet->channelId),
            ];

            $videos[$video->id->videoId] = $current;
        }

        if ($withDetails) {
            $details = $this->getVideos(array_keys($videos));
            foreach ($videos as $video) {
                $videos[$video['id']]['details'] = $details[$video['id']];
            }
        }

        return $videos;
    }

    public function getVideos(array $videoIds): ?array
    {
        $response = $this->youtubeApi->videos->listVideos('contentDetails,statistics', [
                'id' => implode(',', $videoIds)
            ],
        );

        $videos = [];
        foreach ($response as $video) {
            $current = [
                'definition' => $video->contentDetails->definition,
                'duration' => $this->convertDuration($video->contentDetails->duration),
                'projection' => $video->contentDetails->projection,
                'commentCount' => $video->statistics->commentCount,
                'likeCount' => $video->statistics->likeCount,
                'dislikeCount' => $video->statistics->dislikeCount,
                'viewCount' => $video->statistics->viewCount,
                'viewCountFormatted' => $this->formatViews($video->statistics->viewCount),
            ];

            $videos[$video->id] = $current;
        }

        return $videos;
    }

    public function recommend(array $input): ?array
    {
        // formula
        // (views * min(viewsToSubsRation, 5)) / daysSincePublishedx
        $dateFilter = Carbon::now()->subDays($input['publishedAfter'])->toDateString();
        $parameters = [
            'q' => $input['terms'],
            'order' => $input['order'], // viewCount, rating, videoCount
            'publishedAfter' => $dateFilter . 'T00:00:00Z',
            'safeSearch' => $input['safeSearch'],
            'videoDefinition' => $input['videoDefinition'], // any, standard, high
            'videoDuration' => $input['videoDuration'], // any, long, medium, short
            'maxResults' => $input['maxResults'],
            'relevanceLanguage' => $input['relevanceLanguage'],
            'channelId' => $input['channelId'],
        ];


        $searchResults = $this->searchVideos($parameters, true);

        return $searchResults;
    }

    private function convertDuration(string $time){
        $start = new DateTime('@0');
        $start->add(new DateInterval($time));

        return $start->format('H:i:s');
    }

    private function formatViews(int $views): string
    {
        if($views > 1000) {
            $viewsArray = explode(',', number_format(round($views)));
            $formatParts = array('k', 'm', 'b', 't');
            $viewParts = count($viewsArray) - 1;

            $readableTime = $viewsArray[0] . ((int) $viewsArray[1][0] !== 0 ? '.' . $viewsArray[1][0] : '');
            $readableTime .= $formatParts[$viewParts - 1];

            return $readableTime;

        }

        return $views;
    }
}
