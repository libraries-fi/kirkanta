<?php

namespace App\Module\UserManagement\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GroupManagerCount extends Constraint
{
    const GROUP_MANAGER_ROLE = 'ROLE_GROUP_MANAGER';

    public $message = 'This group has already reached its maximum number of administrators ({{ max }}).';
}
