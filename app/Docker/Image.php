<?php namespace App\Docker;

use JsonSerializable;

/**
 * Docker\Image
 */
class Image implements JsonSerializable
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $repository;

    /**
     * @var string
     */
    private $tag;

    /**
     * @param string $repository Name of the image
     * @param string $tag Tag (version) of the image, default "latest"
     */
    public function __construct($repository = null, $tag = 'latest')
    {
        $this->repository = $repository;
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string $repository
     *
     * @return Image
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return Image
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $tag
     *
     * @return Image
     */
    public function setTag($tag = 'latest')
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'repository' => $this->getRepository(),
            'tag' => $this->getTag(),
        ];
    }
}
