<?php namespace App\Docker;

use Illuminate\Contracts\Support\Jsonable;

/**
 * Class Stream
 */
class Stream implements \JsonSerializable, Jsonable
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param string $data
     */
    function __construct($data)
    {
        $this->data = $this->parseStreamData($data);
    }

    /**
     * @param bool $std
     * @param bool $stderr
     * @return string
     */
    public function getOutput($std = true, $stderr = true)
    {
        $result = '';
        foreach ($this->data as $out) {
            $type = key($out);

            if (($type == 'std' && $std) || ($type == 'err' && $stderr)) {
                $result .= current($out);
            }
        }

        return $result;
    }

    function __toString()
    {
        return $this->getOutput();
    }

    /**
     * @param string $data
     * @return array
     */
    private function parseStreamData($data)
    {
        $plain = [];
        while(strlen($data) > 0) {
            $header = unpack('c1type/c3ignore/N1size', substr($data, 0, 8));

            $next = $header['type'] == 1 ? 'std' : 'err';
            $size = $header['size'];

            if ($size > 0) {
                $plain[] = [
                    $next => substr($data, 8, $size)
                ];
            }

            $data = substr($data, $size + 8);
        }

        return $plain;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
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
        return ['log' => $this->getOutput()];
    }
}
