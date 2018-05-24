<?php

namespace Fortnite;

class Response
{
    const ICON = 'i20640';

    const SHOWS = [
        'wins',
        'winrate',
        'kd',
    ];

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
     * @param Validator $validator
     * @param $data
     * @return string
     */
    public function returnData(Validator $validator, $data)
    {
        $frames = [
            'frames' => [
                [
                    'index' => 0,
                    'text'  => $data['name'],
                    'icon'  => self::ICON
                ]
            ]
        ];

        $frameNumber = 1;

        foreach (self::SHOWS as $showOption) {
            if ($validator->getParameters()['show' . ucwords($showOption)] === 'true') {
                $frames['frames'][] = [
                    'index' => $frameNumber,
                    'text'  => $data[$showOption],
                    'icon'  => self::ICON
                ];

                $frameNumber++;
            }
        }

        return $this->asJson($frames);
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
