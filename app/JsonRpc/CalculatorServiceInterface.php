<?php
declare(strict_types=1);

namespace App\JsonRpc;

interface CalculatorServiceInterface
{
    public function calculate(int $v1, int $v2): int;
}