<?php

namespace Fortnite;

class Response
{
    const ICON = 'i20640';

    /**
     * Response constructor.
     */
    public function __construct()
    {
        header("Content-Type: application/json");
    }

    /**
     * @param null $text
     * @return string
     */
    public function returnError($text = null)
    {
        return $this->asJson([
            'frames' => [
                [
                    'index' => 0,
                    'text'  => $text ?: 'Please check app configuration',
                    'icon'  => self::ICON
                ]
            ]
        ]);
    }

    /**
     * @param array $data
     * @return string
     */
    public function asJson($data = [])
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
