<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature : Laravel TestCase + RefreshDatabase pour tout test touchant la DB.
| Unit : PHPUnit\Framework\TestCase (tests unitaires purs sans bootstrap Laravel).
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
