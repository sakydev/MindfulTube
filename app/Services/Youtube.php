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

        $channelIds = [];
        $videos = [];
        foreach ($results as $video) {
            $current = [
                'id' => $video->id->videoId,
                'title' => html_entity_decode($video->snippet->title),
                'description' => $video->snippet->description,
                'channelId' => $video->snippet->channelId,
                'channelTitle' => $video->snippet->channelTitle,
                'publishedAt' => current(explode('T', $video->snippet->publishedAt)),
                'thumbnail' => $video->snippet->thumbnails->medium->url,
                'url' => sprintf('https://www.youtube.com/watch?v=%s', $video->id->videoId),
                'channelUrl' => sprintf('https://www.youtube.com/channel/%s', $video->snippet->channelId),
            ];

            $channelIds[] = $video->snippet->channelId;
            $videos[$video->id->videoId] = $current;
        }

        if ($withDetails) {
            $videoDetails = $this->getVideos(array_keys($videos));
            foreach ($videos as $video) {
                $videos[$video['id']]['videoDetails'] = $videoDetails[$video['id']];
            }

            $channelDetails = $this->getChannels($channelIds);
            foreach ($videos as $video) {
                $videos[$video['id']]['channelDetails'] = $channelDetails[$video['channelId']];
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
                'commentCount' => (int) $video->statistics->commentCount,
                'likeCount' => (int) $video->statistics->likeCount,
                'dislikeCount' => (int) $video->statistics->dislikeCount,
                'viewCount' => (int) $video->statistics->viewCount,
                'viewCountFormatted' => $this->formatViews($video->statistics->viewCount),
            ];

            $videos[$video->id] = $current;
        }

        return $videos;
    }

    public function getChannels(array $channelIds): ?array
    {
        $response = $this->youtubeApi->channels->listChannels('statistics', [
            'id' => implode(',', $channelIds),
        ]);

        $channels = [];
        foreach ($response as $channel) {
            $current = [
                'subscriberCount' => (int) $channel->statistics->subscriberCount,
                'subscriberCountFormatted' => $this->formatViews($channel->statistics->subscriberCount),
                'videoCount' => (int) $channel->statistics->videoCount,
                'viewCount' => (int) $channel->statistics->viewCount,
            ];

            $channels[$channel->id] = $current;
        }

        return $channels;
    }

    public function recommend(array $input): ?array
    {
        $parameters = [
            'q' => str_replace(',', '|', $input['terms']),
            'order' => $input['order'], // viewCount, rating, videoCount
            'safeSearch' => $input['safeSearch'],
            'videoDefinition' => $input['videoDefinition'], // any, standard, high
            'videoDuration' => $input['videoDuration'], // any, long, medium, short
            'maxResults' => ($input['maxResults'] * 2),
            'relevanceLanguage' => $input['relevanceLanguage'],
            'channelId' => $input['channelId'],
        ];

        if (!empty($input['publishedAfter'])) {
            $dateFilter = Carbon::now()->subDays($input['publishedAfter'])->toDateString();
            $parameters['publishedAfter'] = $dateFilter . 'T00:00:00Z';
        }

        $searchResults = $this->searchVideos($parameters, true);
        $rankedResults = $this->rankVideos($searchResults);
        $totalResults = count($rankedResults);

        // return only top half of videos
        if ($totalResults > $input['maxResults']) {
            $rankedResults = array_slice($rankedResults, 0, ($totalResults / 2));
        }

        return $rankedResults;
    }

    public function rankVideos(array $videos): array
    {
        $ranked = [];
        foreach ($videos as $currentVideo) {
            $videoDetails = $currentVideo['videoDetails'];
            $channelDetails = $currentVideo['channelDetails'];

            $ratios = [
                'viewsToComment' => $this->getPercentage(
                    $videoDetails['commentCount'],
                    $videoDetails['viewCount'],
                ),
                'viewsToLikes' => $this->getPercentage(
                    $videoDetails['likeCount'],
                    $videoDetails['viewCount'],
                ),
                'channelViewsContribution' => $this->getPercentage(
                    $videoDetails['viewCount'],
                    $channelDetails['viewCount'],
                ),
                'liked' => $this->getPercentage(
                    $videoDetails['likeCount'],
                    ($videoDetails['likeCount'] + $videoDetails['dislikeCount']),
                ),
            ];

            // only get subscriber ratio with a minimum following of given value
            if ($channelDetails['subscriberCount'] > env('YOUTUBE_MIN_SUBSCRIBERS')) {
                $ratios['subscribersWatched'] = $this->getPercentage(
                    $videoDetails['viewCount'],
                    $channelDetails['subscriberCount'],
                );
            }

            $scoreSincePublished = $this->getPercentage(
                array_sum($ratios),
                $this->daysSincePublished($currentVideo['publishedAt']),
            );
            $currentVideo['totalScore'] = $scoreSincePublished;
            $currentVideo['likedRatio'] = $ratios['liked'];
            $ranked[$scoreSincePublished] = $currentVideo;
        }

        krsort($ranked);

        return $ranked;
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

    private function getPercentage(int $first, $second): int
    {
        if ($first < 1 || $second < 1) { return 0; }
        $total = ($first / $second);

        return $total * 100;
    }

    private function daysSincePublished(string $publishedAt): int
    {
        $published = Carbon::parse($publishedAt);
        $now = Carbon::now();

        return $published->diffInDays($now);
    }
}
