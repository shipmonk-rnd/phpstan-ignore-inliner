<?php declare(strict_types = 1);

class Dummy
{

    public function test($a, $b): void // @phpstan-ignore missingType.parameter
    {
        return null;
    }

}
