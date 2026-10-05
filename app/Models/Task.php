<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Task
{
    public function allForOwner(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT t.id, t.title, t.description, t.subject_id, t.due_date, t.priority, t.status,
                t.created_at, s.name AS subject_name, s.color AS subject_color
             FROM tasks t LEFT JOIN subjects s ON s.id = t.subject_id
             WHERE t.user_id = :user_id
             ORDER BY t.status = "completed", t.due_date IS NULL, t.due_date, t.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function allForUser(array $user, array $filters = []): array
    {
        $sql = 'SELECT t.id, t.title, t.description, t.subject_id, t.due_date, t.priority, t.status, t.created_at, s.name AS subject_name, s.color AS subject_color, u.name AS owner_name FROM tasks t LEFT JOIN subjects s ON s.id = t.subject_id LEFT JOIN users u ON u.id = t.user_id';
        $conditions = [];
        $parameters = [];
        if ($user['role'] !== 'admin') {
            $conditions[] = 't.user_id = :user_id';
            $parameters['user_id'] = $user['id'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = 't.status = :status';
            $parameters['status'] = $filters['status'];
        }
        if (!empty($filters['subject_id'])) {
            $conditions[] = 't.subject_id = :subject_id';
            $parameters['subject_id'] = (int) $filters['subject_id'];
        }
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY t.status = "completed", t.due_date IS NULL, t.due_date, t.created_at DESC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findForUser(int $id, array $user): ?array
    {
        $sql = 'SELECT t.*, s.name AS subject_name FROM tasks t LEFT JOIN subjects s ON s.id = t.subject_id WHERE t.id = :id';
        $parameters = ['id' => $id];
        if ($user['role'] !== 'admin') {
            $sql .= ' AND t.user_id = :user_id';
            $parameters['user_id'] = $user['id'];
        }
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);
        $task = $statement->fetch();

        return $task ?: null;
    }

    public function create(array $attributes): void
    {
        Database::connection()->prepare(
            'INSERT INTO tasks (user_id, subject_id, title, description, due_date, priority, status) VALUES (:user_id, :subject_id, :title, :description, :due_date, :priority, :status)'
        )->execute($attributes);
    }

    public function updateForUser(int $id, array $user, array $attributes): void
    {
        $sql = 'UPDATE tasks SET subject_id = :subject_id, title = :title, description = :description, due_date = :due_date, priority = :priority, status = :status WHERE id = :id';
        $attributes['id'] = $id;
        if ($user['role'] !== 'admin') {
            $sql .= ' AND user_id = :user_id';
            $attributes['user_id'] = $user['id'];
        }
        Database::connection()->prepare($sql)->execute($attributes);
    }

    public function updateStatusForUser(int $id, array $user, string $status): void
    {
        $sql = 'UPDATE tasks SET status = :status WHERE id = :id';
        $parameters = ['status' => $status, 'id' => $id];
        if ($user['role'] !== 'admin') {
            $sql .= ' AND user_id = :user_id';
            $parameters['user_id'] = $user['id'];
        }
        Database::connection()->prepare($sql)->execute($parameters);
    }

    public function deleteForUser(int $id, array $user): void
    {
        $sql = 'DELETE FROM tasks WHERE id = :id';
        $parameters = ['id' => $id];
        if ($user['role'] !== 'admin') {
            $sql .= ' AND user_id = :user_id';
            $parameters['user_id'] = $user['id'];
        }
        Database::connection()->prepare($sql)->execute($parameters);
    }

    public function countsForUser(array $user): array
    {
        $sql = 'SELECT COUNT(*) AS total, SUM(status = "completed") AS completed, SUM(status <> "completed") AS pending FROM tasks';
        $parameters = [];
        if ($user['role'] !== 'admin') {
            $sql .= ' WHERE user_id = :user_id';
            $parameters['user_id'] = $user['id'];
        }
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);
        $counts = $statement->fetch();

        return array_map('intval', $counts ?: ['total' => 0, 'completed' => 0, 'pending' => 0]);
    }
}