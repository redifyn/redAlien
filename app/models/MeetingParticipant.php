<?php

class MeetingParticipant extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Join Meeting
    |--------------------------------------------------------------------------
    */

    public function joinMeeting(array $data): bool
    {
        $sql = "SELECT id
                FROM meeting_participants
                WHERE meeting_id = :meeting_id
                  AND user_id = :user_id
                LIMIT 1";

        $existing =
            $this->fetch(
                $sql,
                [
                    ':meeting_id' =>
                        $data['meeting_id'],

                    ':user_id' =>
                        $data['user_id']
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Existing participant
        |--------------------------------------------------------------------------
        */

        if ($existing) {

            $sql = "UPDATE meeting_participants
                    SET
                        role = :role,
                        mic_on = 1,
                        camera_on = 1,
                        screen_sharing = 0,
                        joined_at = NOW(),
                        left_at = NULL
                    WHERE id = :id";

            $statement =
                $this->query(
                    $sql,
                    [
                        ':role' =>
                            $data['role'] ?? 'member',

                        ':id' =>
                            $existing['id']
                    ]
                );

            return $statement !== false;
        }


        /*
        |--------------------------------------------------------------------------
        | New participant
        |--------------------------------------------------------------------------
        */

        $sql = "INSERT INTO meeting_participants
                (
                    meeting_id,
                    user_id,
                    role,
                    mic_on,
                    camera_on,
                    screen_sharing,
                    joined_at,
                    left_at,
                    created_at
                )
                VALUES
                (
                    :meeting_id,
                    :user_id,
                    :role,
                    1,
                    1,
                    0,
                    NOW(),
                    NULL,
                    NOW()
                )";

        $statement =
            $this->query(
                $sql,
                [
                    ':meeting_id' =>
                        $data['meeting_id'],

                    ':user_id' =>
                        $data['user_id'],

                    ':role' =>
                        $data['role'] ?? 'member'
                ]
            );

        return $statement !== false;
    }


    /*
    |--------------------------------------------------------------------------
    | Leave Meeting
    |--------------------------------------------------------------------------
    */

    public function leaveMeeting(
        int $meetingId,
        int $userId
    ): bool {
        $sql = "UPDATE meeting_participants
                SET
                    left_at = NOW(),
                    screen_sharing = 0
                WHERE meeting_id = :meeting_id
                  AND user_id = :user_id
                  AND left_at IS NULL";

        $statement =
            $this->query(
                $sql,
                [
                    ':meeting_id' =>
                        $meetingId,

                    ':user_id' =>
                        $userId
                ]
            );

        return $statement !== false;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Microphone
    |--------------------------------------------------------------------------
    */

    public function updateMicrophone(
        int $meetingId,
        int $userId,
        bool $enabled
    ): bool {
        $sql = "UPDATE meeting_participants
                SET mic_on = :mic_on
                WHERE meeting_id = :meeting_id
                  AND user_id = :user_id
                  AND left_at IS NULL";

        $statement =
            $this->query(
                $sql,
                [
                    ':mic_on' =>
                        $enabled ? 1 : 0,

                    ':meeting_id' =>
                        $meetingId,

                    ':user_id' =>
                        $userId
                ]
            );

        return $statement !== false;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Camera
    |--------------------------------------------------------------------------
    */

    public function updateCamera(
        int $meetingId,
        int $userId,
        bool $enabled
    ): bool {
        $sql = "UPDATE meeting_participants
                SET camera_on = :camera_on
                WHERE meeting_id = :meeting_id
                  AND user_id = :user_id
                  AND left_at IS NULL";

        $statement =
            $this->query(
                $sql,
                [
                    ':camera_on' =>
                        $enabled ? 1 : 0,

                    ':meeting_id' =>
                        $meetingId,

                    ':user_id' =>
                        $userId
                ]
            );

        return $statement !== false;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Screen Sharing
    |--------------------------------------------------------------------------
    */

    public function updateScreenSharing(
        int $meetingId,
        int $userId,
        bool $sharing
    ): bool {
        $sql = "UPDATE meeting_participants
                SET screen_sharing = :screen_sharing
                WHERE meeting_id = :meeting_id
                  AND user_id = :user_id
                  AND left_at IS NULL";

        $statement =
            $this->query(
                $sql,
                [
                    ':screen_sharing' =>
                        $sharing ? 1 : 0,

                    ':meeting_id' =>
                        $meetingId,

                    ':user_id' =>
                        $userId
                ]
            );

        return $statement !== false;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Active Participants
    |--------------------------------------------------------------------------
    */

    public function getActiveParticipants(
        int $meetingId
    ): array {
        $sql = "SELECT
                    mp.id,
                    mp.meeting_id,
                    mp.user_id,
                    mp.role,
                    mp.mic_on,
                    mp.camera_on,
                    mp.screen_sharing,
                    mp.joined_at,

                    u.full_name,
                    u.username,
                    u.avatar,
                    u.status

                FROM meeting_participants mp

                INNER JOIN users u
                    ON u.id = mp.user_id

                WHERE mp.meeting_id = :meeting_id
                  AND mp.left_at IS NULL

                ORDER BY
                    CASE mp.role
                        WHEN 'owner' THEN 1
                        WHEN 'admin' THEN 2
                        WHEN 'moderator' THEN 3
                        ELSE 4
                    END,
                    mp.joined_at ASC";

        return $this->fetchAll(
            $sql,
            [
                ':meeting_id' =>
                    $meetingId
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Count Active Participants
    |--------------------------------------------------------------------------
    */

    public function countActiveParticipants(
        int $meetingId
    ): int {
        $sql = "SELECT COUNT(*) AS total
                FROM meeting_participants
                WHERE meeting_id = :meeting_id
                  AND left_at IS NULL";

        $row =
            $this->fetch(
                $sql,
                [
                    ':meeting_id' =>
                        $meetingId
                ]
            );

        return (int) (
            $row['total']
            ?? 0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Find Active Participant
    |--------------------------------------------------------------------------
    */

    public function findActiveParticipant(
        int $meetingId,
        int $userId
    ) {
        $sql = "SELECT *
                FROM meeting_participants
                WHERE meeting_id = :meeting_id
                  AND user_id = :user_id
                  AND left_at IS NULL
                LIMIT 1";

        return $this->fetch(
            $sql,
            [
                ':meeting_id' =>
                    $meetingId,

                ':user_id' =>
                    $userId
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Participants When Meeting Ends
    |--------------------------------------------------------------------------
    */

    public function endAllParticipants(
        int $meetingId
    ): bool {
        $sql = "UPDATE meeting_participants
                SET
                    left_at = NOW(),
                    screen_sharing = 0
                WHERE meeting_id = :meeting_id
                  AND left_at IS NULL";

        $statement =
            $this->query(
                $sql,
                [
                    ':meeting_id' =>
                        $meetingId
                ]
            );

        return $statement !== false;
    }
}