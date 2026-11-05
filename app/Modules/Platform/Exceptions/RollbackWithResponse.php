<?php

namespace App\Modules\Platform\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal Carries a 5xx response out of a transaction so its writes roll back.
 */
final class RollbackWithResponse extends RuntimeException
{
    public function __construct(public readonly Response $response)
    {
        parent::__construct('Rolled back after a server error.');
    }
}
