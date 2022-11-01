<?php

namespace DMT\Laravel\Import\Reader\Contracts;

interface ErrorHandler
{
    public function register(): void;
}