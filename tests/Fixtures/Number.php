<?php

namespace DMT\Test\Laravel\Import\Reader\Fixtures;

class Number
{
    public int $number;
    public string $text;

    public function __construct(int $number, string $text)
    {
        $this->number = $number;
        $this->text = $text;
    }
}
