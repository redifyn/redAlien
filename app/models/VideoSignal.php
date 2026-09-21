<?php

class VideoSignal extends Model
{
    public function createSignal(array $data): int|false
    {
        $sql = "INSERT INTO video_signals
                (
                    channel_id,
                    sender_id,
                    receiver_id,
                    signal_type,
                    signal_data,
                    is_processed,
                    created_at
                )
                VALUES
                (
                    :channel_id,
                    :sender_id,
                    :receiver_id,
                    :signal_type,
                    :signal_data,
                    0,
                    NOW()
                )";

        $this->query($sql, [
            ':channel_id' =>
                $data['channel_id'],

            ':sender_id' =>
                $data['sender_id'],

            ':receiver_id' =>
                $data['receiver_id'] ?? null,

            ':signal_type' =>
                $data['signal_type'],

            ':signal_data' =>
                $data['signal_data']
        ]);

        $id = $this->lastInsertId();

        return $id > 0
            ? $id
            : false;
    }


    public function getPendingSignals(
        int $channelId,
        int $userId
    ): array {
        $sql = "SELECT *
                FROM video_signals
                WHERE channel_id = :channel_id
                  AND is_processed = 0
                  AND sender_id != :user_id
                  AND (
                        receiver_id IS NULL
                        OR receiver_id = :receiver_id
                  )
                ORDER BY id ASC";

        return $this->fetchAll($sql, [
            ':channel_id' =>
                $channelId,

            ':user_id' =>
                $userId,

            ':receiver_id' =>
                $userId
        ]);
    }


    public function markProcessed(
        int $signalId
    ): bool {
        $sql = "UPDATE video_signals
                SET is_processed = 1
                WHERE id = :id";

        $statement =
            $this->query($sql, [
                ':id' => $signalId
            ]);

        return $statement !== false;
    }


    public function clearChannelSignals(
        int $channelId
    ): bool {
        $sql = "DELETE FROM video_signals
                WHERE channel_id = :channel_id";

        $statement =
            $this->query($sql, [
                ':channel_id' =>
                    $channelId
            ]);

        return $statement !== false;
    }
}