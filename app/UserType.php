<?php

namespace App;

enum UserType: string
{
    case SYSTEM = 'system';
    case MEMBER = 'member';
}
