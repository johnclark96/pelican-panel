<?php

namespace App\Http\Requests\Api\Application\Users;

use App\Services\Acl\Api\AdminAcl as Acl;
use App\Http\Requests\Api\Application\ApplicationApiRequest;
use App\Models\User;

class GetUsersRequest extends ApplicationApiRequest
{
    protected ?string $resource = User::RESOURCE_NAME;

    protected int $permission = Acl::READ;
}
