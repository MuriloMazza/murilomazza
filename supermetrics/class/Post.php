<?php

/**
 * @file
 * Class for representing Post.
 */

namespace SocialMedia;

use \Datetime;

class Post
{
    const TYPE_STATUS = 'status';

    private $id;
    private $fromId;
    private $message;
    private $type;
    private $createdTime;
 
    /**
     * Post constructor.
     *
     * @param $id
     * @param $fromId
     * @param $message
     * @param $type
     * @param $createdTime
     */
    public function __construct(string $id, string $fromId, string $message, string $type=this::TYPE_STATUS, string $createdTime) {
        $this->id = $id;
        $this->fromId = $fromId;
        $this->message = $message;
        $this->type = $type;
        $this->createdTime = new DateTime($createdTime);
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
    public function setId(string $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFromId(): string {
        return $this->fromId;
    }

    /**
     * @param string $fromId
     */
    public function setFromId(string $fromId): void {
        $this->fromId = $fromId;
    }

    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void {
        $this->type = $type;
    }

    /**
     * @return DateTime
     */
    public function getCreatedTime(): DateTime {
        return $this->createdTime;
    }

    /**
     * @param string $createdTime
     */
    public function setCreatedTime(string $createdTime): void {
        $this->createdTime = new DateTime($createdTime);
    }
    
    /**
     * Count character length of post message.
     *
     * @return int
     *   Returns the character length.
     */
    public function countMessageCharacters() {
        return strlen($this->message);
    }
}
