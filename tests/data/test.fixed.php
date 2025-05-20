<?php declare(strict_types = 1);

class Dummy
{

    public function test($a, $b): void // @phpstan-ignore missingType.parameter, missingType.parameter
    {
        return null; // @phpstan-ignore return.void
    }

}
