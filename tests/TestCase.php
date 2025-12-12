<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * The parameters that should be used when running "migrate:fresh".
     * 
     * @return array
     */
    protected function migrateFreshUsing()
    {
        return array_merge(parent::migrateFreshUsing(), [
            '--force' => true,
            '--no-interaction' => true,
        ]);
    }
}
