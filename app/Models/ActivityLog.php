<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ActivityLog
{
    public function record(?array $actor, string $event, string $description): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO activity_logs (actor_user_id, actor_name, event, description)
             VALUES (:actor_user_id, :actor_name, :event, :description)'
        );
        $statement->execute([
            'actor_user_id' => isset($actor['id']) ? (int) $actor['id'] : null,
            'actor_name' => (string) ($actor['name'] ?? 'System'),
            'event' => $event,
            'description' => $description,
        ]);
    }

    public function latest(int $limit = 100): array
    {
        $limit = max(1, min($limit, 250));
        $statement = Database::connection()->query(
            'SELECT id, actor_name, event, description, created_at
             FROM activity_logs ORDER BY created_at DESC, id DESC LIMIT ' . $limit
        );

        return $statement->fetchAll();
    }
}