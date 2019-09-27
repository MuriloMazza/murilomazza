<?php

/**
 * @file
 * Class for representing an Asset.
 */

class Asset
{
    private $id;
    private $name;

    /**
     * Asset constructor.
     *
     * @param $id
     * @param $name
     */
    public function __construct($id, $name) {
      $this->id = $id;
      $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
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
}
