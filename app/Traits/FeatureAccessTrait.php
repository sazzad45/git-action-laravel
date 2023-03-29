<?php

namespace App\Traits;

use App\Constant\LevelConst;
use App\Domain\UserRelation\Models\User;

trait FeatureAccessTrait
{
    use HelperTrait;

    private function ableToDoCustomerKyc(User $user)
    {
        if (!empty($user->level) && in_array($user->level->level_id, $this->getKycPermittedLevelIds())) {
            return true;
        }

        return false;
    }

    private function getKycPermittedLevelIds()
    {
        return [
            LevelConst::AGENT_SHOWROOM
        ];
    }

    private function isParentMainAgent(User $agent)
    {
        $agentParentId = $this->getAgentParent($agent)->sales_rep_id;
        if($this->isMainAgent($agentParentId)){
            return true;
        }

        return false;
    }
}
