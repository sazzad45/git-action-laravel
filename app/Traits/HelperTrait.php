<?php

namespace App\Traits;

use App\Constant\LevelConst;
use App\Domain\Distribution\Models\DistributionChannel;
use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserProfile;

trait HelperTrait
{
    private function getAgentParent(User $agent)
    {
        return DistributionChannel::where('agent_id', $agent->id)
            ->whereNotNull('sales_rep_id')
            ->whereStatus(1)
            ->latest()
            ->first();
    }

    private function isMainAgent(int  $srId): bool
    {
        $mainAgent = User::with('level')->find($srId);

        if ($mainAgent && $mainAgent->level && in_array($mainAgent->level->level_id, [LevelConst::MAIN_AGENT_KURD, LevelConst::MAIN_AGENT_IRAQ]))
            return true;

        return false;
    }

    private function areSenderAndReceiverHasSameParent(User $sender, User $receiver): bool
    {
        $senderParentId = $this->getAgentParent($sender)->sales_rep_id;
        $receiverParentId = $this->getAgentParent($receiver)->sales_rep_id;
        if (($senderParentId == $receiverParentId) && $this->isMainAgent($senderParentId)) {
            return true;
        }
        return false;
    }

    private function getCityId(int $userId)
    {
        return UserProfile::where('user_id', $userId)
            ->whereNotNull('city_id')
            ->first()
            ->city_id ?? null;
    }

    private function isSenderReceiverFromSameCity(User $sender, User $receiver): bool
    {
        $senderCityId = $this->getCityId($sender->id);
        $receiverCityId = $this->getCityId($receiver->id);
        if ($senderCityId != null &&  $receiverCityId != null && $senderCityId == $receiverCityId) {
            return true;
        }

        return false;
    }
}
