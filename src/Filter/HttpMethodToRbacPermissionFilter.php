<?php

declare(strict_types=1);

namespace UserManager\Filter;

use Fig\Http\Message\RequestMethodInterface as Http;
use Laminas\Filter;

use function str_ends_with;

final class HttpMethodToRbacPermissionFilter implements Filter\FilterInterface
{
    final public const CREATE_PERMISSION  = 'create';
    final public const DELETE_PERMISSION  = 'delete';
    final public const READ_PERMISSION    = 'read';
    final public const UPDATE_PERMISSION  = 'update';

    /** @inheritDoc */
    public function filter($value, ?string $method = '')
    {
        $mappedPermission = match ($method) {
            Http::METHOD_GET                     => self::READ_PERMISSION,
            Http::METHOD_PUT, Http::METHOD_PATCH => self::UPDATE_PERMISSION,
            Http::METHOD_POST                    => self::CREATE_PERMISSION,
            Http::METHOD_DELETE                  => self::DELETE_PERMISSION,
            default                              => $value
        };

        if (! str_ends_with($value, $mappedPermission)) {
            $value .= '.' . $mappedPermission;
        }
        return $value;
    }
}
