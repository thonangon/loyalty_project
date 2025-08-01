<?php

namespace App;

enum Roles: string
{
    case ADMIN = 'super_admin';
    case USER = 'user';
}
