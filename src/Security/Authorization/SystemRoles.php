<?php

namespace App\Security\Authorization;

class SystemRoles
{
    const ADMIN_ROLE = 'ROLE_ROOT';

    /**
     * Should this method exists or not?
     */
    public function getAdminRole() : string
    {
        return self::ADMIN_ROLE;
    }

    public function getUserRoles(bool $flat = false) : array
    {
        return [
            'Common permissions' => [
                // 'Regular user' => 'ROLE_USER',
                'Manage users in own group' => 'ROLE_GROUP_MANAGER',
            ],
            'Administration' => [
            ],
            'Critical' => [
                'System administrator' => 'ROLE_ROOT',
            ]
        ];
    }

    public function getGroupRoles() : array
    {
        return [
            'Common permissions' => [
                'Finna' => [
                    'Access Finna organisations' => 'ROLE_FINNA',
                ],
                'PTV' => [
                    'Export documents to PTV' => 'ROLE_PTV',
                ]
            ]
        ];
    }
}
