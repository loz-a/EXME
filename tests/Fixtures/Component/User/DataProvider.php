<?php

declare(strict_types=1);

namespace EXME\Tests\Fixtures\Component\User;

final class DataProvider
{
    public function getData(): array
    {
        return [
            [
                'firstname' => 'Alex',
                'lastname' =>  'Brown',
                'age' => 25,
                'sex' => 'male',
            ],
            [
                'firstname' => 'Diana',
                'lastname' =>  'Borjia',
                'age' => 22,
                'sex' => 'female',
            ]
        ];
    }
}