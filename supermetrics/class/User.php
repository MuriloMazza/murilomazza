<?php

/**
 * @file
 * Class for representing User.
 */

namespace SocialMedia;
 
use \Datetime;
 
class User
{
    private $id;
    private $name;
    private $posts = array(); //the posts of user in social media.
    private $cachedPostsByMonth = array(); //the posts grouped by month.
    
    /**
     * User constructor.
     *
     * @param $id
     * @param $name
     */
    public function __construct(string $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }
    
    /**
     * Add a post.
     *
     * @param Post $post
     */
    public function addPost(Post $post) {
        $this->posts[] = $post;
    }

    /**
     * Get array of Posts.
     *
     * @return array
     */
    public function getPosts(): array {
        return $this->posts;
    }
    
    /**
     * Get array of Posts grouped by month.
     *
     * @return array
     */
    public function getPostsByMonth(): array {
        $postsByMonth = array();
        foreach ($this->posts as $post) {
            $dateTime = $post->getCreatedTime();
            $yearMonth = $dateTime->format('Y-m');
            $postsByMonth[$yearMonth][] = $post;
        }
        $this->cachedPostsByMonth = $postsByMonth;
        return $postsByMonth;
    }
    
    /**
     * Get the avverage character length from posts grouped by month.
     *
     * @return array
     */
    public function getPostsAverageCharacterLengthByMonth() {
        $postsByMonth  = empty($this->cachedPostsByMonth) ? $this->getPostsByMonth() : $this->cachedPostsByMonth;
        foreach ($postsByMonth as $yearMonth => $posts) {
            if (count($posts) == 0) {
                continue;
            }
            $characterLengthSum = 0;
            foreach ($posts as $post) {
                $characterLengthSum += $post->countMessageCharacters();
            }
            $postsByMonth[$yearMonth] = round($characterLengthSum / count($posts), 2);
        }
        return $postsByMonth;
    }
    
    /**
     * Get the longest posts grouped by month.
     *
     * @return array
     */
    public function getLongestPostsByMonth() {
        $postsByMonth  = empty($this->cachedPostsByMonth) ? $this->getPostsByMonth() : $this->cachedPostsByMonth;
        foreach ($postsByMonth as $yearMonth => $posts) {
            $longestCharacterCount = 0;
            foreach ($posts as $post) {
                $countMessageCharacter = $post->countMessageCharacters();
                if ($countMessageCharacter > $longestCharacterCount) {
                    $longestCharacterCount = $countMessageCharacter;
                    $postsByMonth[$yearMonth] = array('id' => $post->getId(), 'message' => $post->getMessage());
                }
            }
        }
        return $postsByMonth;
    }
    
    /**
     * Get the total of posts per week.
     *
     * @return array
     */
    public function getTotalPostsPerWeek() {
        $result = array();
        foreach ($this->posts as $post) {
            $dateTime = $post->getCreatedTime();
            $weekLabel = $dateTime->format('Y-m') . ' Week #' . $dateTime->format('W');
            if (array_key_exists($weekLabel, $result)) {
                $result[$weekLabel]++;
            } else {
                $result[$weekLabel] = 1;
            }
        }
        return $result;
    }
    
    /**
     * Get the average number of posts of user per month.
     *
     * @return array
     */
    public function getAverageNumberOfPostsPerMonth() {
        $postsByMonth  = empty($this->cachedPostsByMonth) ? $this->getPostsByMonth() : $this->cachedPostsByMonth;
        if (count($postsByMonth) == 0) {
            return null;
        }
        $postsCount = 0;
        foreach ($postsByMonth as $yearMonth => $posts) {
            $postsCount += count($posts);
        }
        return round($postsCount / count($postsByMonth), 2);
    }
}
