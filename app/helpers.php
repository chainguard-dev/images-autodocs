<?php

declare(strict_types=1);

function config_unfurl(string $envKey, string $defaultValue): array
{
    return explode(":", envconfig($envKey, $defaultValue));
}
