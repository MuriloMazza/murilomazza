<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'settings.php';
require_once 'class/SupermetricsApi.php';
require_once 'class/Post.php';
require_once 'class/User.php';

use Api\SupermetricsApi;
use SocialMedia\Post;
use SocialMedia\User;

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : 'user_0';

/**
 * List of posts that we will get from API.
 */
$postsList = array();

/**
 * List of users that we will get from API.
 */
$usersList = array();

/**
 * Start Supermetrics API.
 */
$supermetricsApi = new SupermetricsApi();

/**
 * Register client to API.
 */
$result = $supermetricsApi->register($settings->client);

if (empty($result->data->sl_token)) {
    die('Unable to get data from API.');        
}

/**
 * Count of pages to call the API. 
 */
$iterationCount = $settings->assignment->postsCount / $settings->assignment->postsPerPage;

/*
 * Fetch posts from user.
 */
for ($page = 1; $page <= $iterationCount; $page++) {
    $data = array(
        'sl_token' => $result->data->sl_token,
        'page' => $page
    );
    //Get users posts from API.
    $postsResult = $supermetricsApi->posts($data);
    if (empty($postsResult->data)) {
        die('Unable to get posts data from API.');        
    }

    foreach ($postsResult->data->posts as $post) {
        if ($post->from_id == $userId) {
            //Instantiate user if it does not exist.
            if (!isset($user)) {
                $user = new User($post->from_id, $post->from_name);
            }
            //Add post of user.
            $userPost = new Post($post->id, $post->from_id, $post->message, $post->type, $post->created_time);
            $user->addPost($userPost);
        }
    }
}

echo 'Stats for user ' . $user->getName() . ' (' . $user->getId() . '):';

/**
 * Get the avverage character length from posts grouped by month.
 */
echo "<br /><br />- Average character length / post / month:<br /><br />";
echo json_encode($user->getPostsAverageCharacterLengthByMonth());
    
/**
 * Get the longest posts grouped by month.
 */
echo '<br /><br />- Longest post by character length / month:<br /><br />';
echo json_encode($user->getLongestPostsByMonth());

/**
 * Get the total of posts per week.
 */
echo '<br /><br />- Total posts split by week:<br /><br />';
echo json_encode($user->getTotalPostsPerWeek());

/**
 * Get the average number of posts of user per month.
 */
echo '<br /><br />- Average number of posts per user / month:<br /><br />';
echo json_encode($user->getAverageNumberOfPostsPerMonth());











