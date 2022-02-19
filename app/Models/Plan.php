<?php

namespace App\Models;

use Illuminate\Auth\Access\Response;

class Plan
{
    const CONSTRAINTS = [
        'basic' => [
            'max_events' => 3,
            'max_members' => 10,
        ],
        'pro' => [
            'max_events' => 10,
            'max_members' => 20,
        ],
        'premium' => [
            'max_events' => -1,
            'max_members' => -1,
        ]
    ];

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $params;

    /**
     * @param array $params
     */
    public function __construct(User $user, array $params)
    {
        $this->user = $user;
        $this->params = $params;
    }

    /**
     * @param string $identifier
     *
     * @return Plan
     */
    public static function create(User $user, string $identifier): Plan
    {
        if (!array_key_exists($identifier, self::CONSTRAINTS)) {
            throw new \Exception("Plan {$identifier} configuration not found");
        }
        return new Plan($user, self::CONSTRAINTS[$identifier]);
    }

    /**
     * @param string $ability
     *
     * @return bool
     */
    public function can(string $ability): ?Response
    {
        if (!$this->hasPermission($ability)) {
            return Response::deny('Your plan does not allow you to do this');
        }

        return match ($ability) {
            Permission::CREATE_EVENT->value => $this->canCreateEvent(),
            Permission::INVITE_MEMBER->value => $this->canInviteMember(),
            default => null,
        };
    }

    /**
     * Checks whether the plan allows for a generic ability.
     * Assumes it does if not present in params
     *
     * @param string $key
     *
     * @return bool
     */
    private function hasPermission(string $key): bool
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return true;
    }

    private function canCreateEvent(): ?Response
    {
        if ($this->params['max_events'] != -1) {
            if ($this->user->events()->count() >= $this->params['max_events']) {
                return Response::deny("You've reached your plan's events limit");
            }
        }
        return null;
    }

    private function canInviteMember(): ?Response
    {
        if ($this->params['max_members'] != -1) {
            if ($this->user->members()->count() >= $this->params['max_members']) {
                return Response::deny("You've reached your plan's members limit");
            }
        }
        return null;
    }
}
