<?php

namespace App\Models;

enum Permission: string
{
    case VIEW_EVENTS = 'view-events';
    case VIEW_EVENT = 'view-event';
    case CREATE_EVENT = 'create-event';
    case EDIT_EVENT = 'edit-event';
    case DELETE_EVENT = 'delete-event';

    case VIEW_MEMBERS = 'view-members';
    case VIEW_MEMBER = 'view-member';
    case INVITE_MEMBER = 'invite-member';
    case EDIT_MEMBER = 'edit-member';
    case DELETE_MEMBER = 'delete-member';

    case VIEW_ROLES = 'view-roles';
    case CREATE_ROLE = 'create-role';
    case EDIT_ROLE = 'edit-role';
}
