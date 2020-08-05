<?php

namespace srag\Plugins\UserTakeOver;

use Closure;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;

/**
 * Class Access
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Access
{
    /**
     * @var int
     */
    protected $current_user_id;
    /**
     * @var int
     */
    protected $original_user_id;
    /**
     * @var BasicAccessCheckClosures
     */
    protected $basics;

    /**
     * Access constructor.
     * @param int $current_user_id
     */
    public function __construct(int $current_user_id, int $original_user_id)
    {
        $this->current_user_id  = $current_user_id;
        $this->original_user_id = $original_user_id;
        $this->basics           = BasicAccessCheckClosures::getInstance();
    }

    public function isTakeOverRunning(?Closure $additional = null) : Closure
    {
        return $this->getClosureWithOptinalClosure(function () : bool {
            $handler = new Handler();
            return $handler->isImpersonated();
        }, $additional);

    }

    public function isUserTakeOverAvailableForUser(?Closure $additional = null) : Closure
    {
        $closure = function () use ($additional) : bool {
            return ($this->hasUserAccessToUserSearch($additional)() || $this->isUserAssignedToAGroup($additional) || $this->isTakeOverRunning($additional));
        };

        return $this->getClosureWithOptinalClosure(
            $this->getClosureWithOptinalClosure($closure, $additional),
            $this->basics->isUserLoggedIn());
    }

    public function hasUserAccessToUserSearch(?Closure $additional = null) : Closure
    {
        return $this->getClosureWithOptinalClosure(function () use ($additional) : bool {
            return ($this->basics->hasAdministrationAccess($additional)() && !$this->isTakeOverRunning($additional)());
        }, $additional);
    }

    public function isUserAllowedToImpersonate(?Closure $additional = null) : Closure
    {
        $closure = function () use ($additional) : bool {
            return ($this->hasUserAccessToUserSearch($additional)() || $this->isUserAssignedToAGroup($additional));
        };
        return $this->getClosureWithOptinalClosure($this->getClosureWithOptinalClosure($this->basics->isUserLoggedIn(), $closure), $additional);
    }

    public function isUserAssignedToSameGroup(int $user_id_to_impersonate, ?Closure $additional = null) : Closure
    {
        return $this->getClosureWithOptinalClosure(function () use ($user_id_to_impersonate) : bool {
            static $is_in_group;
            if (!isset($is_in_group)) {
                $groups      = \usrtoMember::where(['user_id' => $this->original_user_id])->getArray(null, 'group_id');
                $members     = \usrtoMember::where(['group_id' => $groups])->getArray(null, 'user_id');
                $is_in_group = in_array($user_id_to_impersonate, $members);
            }

            return $is_in_group;
        }, $additional);
    }

    public function isUserAssignedToAGroup(?Closure $additional = null) : Closure
    {
        return $this->getClosureWithOptinalClosure(function () : bool {
            static $is_in_group;
            if (!isset($is_in_group)) {
                $is_in_group = \usrtoMember::where(['user_id' => $this->current_user_id])->hasSets();
            }

            return $is_in_group;
        }, $additional);
    }


    //
    // Internal
    //

    private function checkClosureForBoolReturnValue(Closure $c) : bool
    {
        try {
            $r = new \ReflectionFunction($c);
        } catch (\Throwable $e) {
            return false;
        }

        return $r->hasReturnType() && $r->getReturnType()->isBuiltin();
    }

    private function getClosureWithOptinalClosure(Closure $closure, ?Closure $additional = null) : Closure
    {
        if ($additional instanceof Closure && $this->checkClosureForBoolReturnValue($additional)) {
            return static function () use ($closure, $additional) : bool {
                return $additional() && $closure();
            };
        }

        return $closure;
    }

}
