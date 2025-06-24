<?php declare(strict_types = 1);

class Dummy
{

    public function test($a, $b): void // @phpstan-ignore missingType.parameter, missingType.parameter (some comment)
    {
        return null; // @phpstan-ignore return.void (some comment)
    }

}
