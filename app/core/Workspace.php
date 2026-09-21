<?php

class Workspace
{
    protected Team $teamModel;
    protected Channel $channelModel;

    public function __construct()
    {
        $this->teamModel = new Team();
        $this->channelModel = new Channel();
    }

    public function getAlien(int $teamId)
    {
        return $this->teamModel->findById($teamId);
    }

    public function getPods(int $teamId): array
    {
        return $this->channelModel->getByAlien($teamId);
    }
}