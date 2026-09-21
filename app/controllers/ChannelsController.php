<?php

class ChannelsController extends Controller
{
    private Channel $channelModel;
    private Team $teamModel;

    public function __construct()
    {
        Auth::requireLogin();

        $this->channelModel = $this->model('Channel');
        $this->teamModel = $this->model('Team');
    }


    public function store($teamId = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('teams');
        }

        $teamId = (int) $teamId;
        $userId = (int) $_SESSION['user_id'];

        if ($teamId <= 0) {
            Flash::error('Alien not found.');
            $this->redirect('teams');
        }

        if (!$this->teamModel->canManageAlien($teamId, $userId)) {
            Flash::error(
                'You do not have permission to create Pods in this Alien.'
            );

            $this->redirect('teams/show/' . $teamId);
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = trim($_POST['type'] ?? 'public');

        if ($name === '') {
            Flash::error('Please enter a Pod name.');
            $this->redirect('teams/show/' . $teamId);
        }

        if (mb_strlen($name) < 2) {
            Flash::error(
                'Pod name must contain at least 2 characters.'
            );

            $this->redirect('teams/show/' . $teamId);
        }

        if (mb_strlen($name) > 120) {
            Flash::error(
                'Pod name cannot exceed 120 characters.'
            );

            $this->redirect('teams/show/' . $teamId);
        }

        if (mb_strlen($description) > 1000) {
            Flash::error(
                'Pod description cannot exceed 1000 characters.'
            );

            $this->redirect('teams/show/' . $teamId);
        }

        if (!in_array($type, ['public', 'private'], true)) {
            $type = 'public';
        }

        $slug = $this->generateSlug($teamId, $name);

        $podId = $this->channelModel->createPod([
            'team_id' => $teamId,
            'created_by' => $userId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description ?: null,
            'type' => $type
        ]);

        if (!$podId) {
            Flash::error(
                'The Pod could not be created. Please try again.'
            );

            $this->redirect('teams/show/' . $teamId);
        }

        Flash::success(
            'Pod created successfully. Your crew can now enter it.'
        );

        $this->redirect('teams/show/' . $teamId);
    }


    private function generateSlug(
        int $teamId,
        string $name
    ): string {
        $slug = strtolower($name);

        $slug = preg_replace(
            '/[^a-z0-9]+/i',
            '-',
            $slug
        );

        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'pod';
        }

        $originalSlug = $slug;
        $counter = 1;

        while (
            $this->channelModel->slugExistsForAlien(
                $teamId,
                $slug
            )
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function show($id = null): void
        {
            $channelId = (int) $id;
            $userId = (int) $_SESSION['user_id'];

            if ($channelId <= 0) {
                Flash::error('Pod not found.');
                $this->redirect('teams');
            }

            $pod = $this->channelModel->findPodForUser(
                $channelId,
                $userId
            );

            if (!$pod) {
                Flash::error(
                    'You do not have access to this Pod.'
                );

                $this->redirect('teams');
            }

            $alien = $this->teamModel->findByIdForUser(
                (int) $pod['team_id'],
                $userId
            );

            if (!$alien) {
                Flash::error('Alien not found.');
                $this->redirect('teams');
            }

            $pods = $this->teamModel->getAlienPods(
                (int) $pod['team_id']
            );

            $crew = $this->teamModel->getAlienCrew(
                (int) $pod['team_id']
            );

            $messageModel = $this->model('Message');

            $transmissions = $messageModel->getPodTransmissions(
                $channelId
            );

            $this->view('channels/show', [
                'title' => $pod['name'],
                'alien' => $alien,
                'pod' => $pod,
                'pods' => $pods,
                'crew' => $crew,
                'transmissions' => $transmissions
            ]);
        }

        public function delete($id = null): void
            {
                /*
                |--------------------------------------------------------------------------
                | POST Only
                |--------------------------------------------------------------------------
                */

                if (
                    $_SERVER['REQUEST_METHOD'] !==
                    'POST'
                ) {
                    $this->redirect('teams');
                }


                /*
                |--------------------------------------------------------------------------
                | Current User / Pod
                |--------------------------------------------------------------------------
                */

                $podId =
                    (int) $id;

                $userId =
                    (int) (
                        $_SESSION['user_id']
                        ?? 0
                    );


                if (
                    $podId <= 0 ||
                    $userId <= 0
                ) {

                    Flash::error(
                        'Pod not found.'
                    );

                    $this->redirect('teams');
                }


                /*
                |--------------------------------------------------------------------------
                | Find Pod And Confirm Access
                |--------------------------------------------------------------------------
                */

                $pod =
                    $this->channelModel
                        ->findPodForUser(
                            $podId,
                            $userId
                        );


                if (!$pod) {

                    Flash::error(
                        'Pod not found or you do not have access.'
                    );

                    $this->redirect('teams');
                }


                $teamId =
                    (int) (
                        $pod['team_id']
                        ?? 0
                    );


                if ($teamId <= 0) {

                    Flash::error(
                        'Alien not found.'
                    );

                    $this->redirect('teams');
                }


                /*
                |--------------------------------------------------------------------------
                | Permission
                |--------------------------------------------------------------------------
                */

                if (
                    !$this->teamModel
                        ->canManageAlien(
                            $teamId,
                            $userId
                        )
                ) {

                    Flash::error(
                        'You do not have permission to delete this Pod.'
                    );

                    $this->redirect(
                        'channels/show/' .
                        $podId
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Every Alien Must Keep At Least One Pod
                |--------------------------------------------------------------------------
                */

                $podCount =
                    $this->channelModel
                        ->countActivePods(
                            $teamId
                        );


                if ($podCount <= 1) {

                    Flash::error(
                        'This Pod cannot be deleted because every Alien must have at least one Pod.'
                    );

                    $this->redirect(
                        'channels/show/' .
                        $podId
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Soft Delete Pod
                |--------------------------------------------------------------------------
                */

                $deleted =
                    $this->channelModel
                        ->softDeletePod(
                            $podId
                        );


                if (!$deleted) {

                    Flash::error(
                        'The Pod could not be deleted.'
                    );

                    $this->redirect(
                        'channels/show/' .
                        $podId
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Success
                |--------------------------------------------------------------------------
                */

                Flash::success(
                    'Pod deleted successfully.'
                );


                $this->redirect(
                    'teams/show/' .
                    $teamId
                );
            }

            
}