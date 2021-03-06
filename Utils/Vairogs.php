<?php declare(strict_types = 1);

namespace Vairogs\Component\Utils;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class Vairogs extends Bundle
{
    /**
     * @var string
     */
    public const VAIROGS = 'vairogs';
    /**
     * @var string
     */
    public const RAVEN = 'raven';
}
