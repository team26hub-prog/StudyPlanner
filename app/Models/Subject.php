<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Subject
{
    public function allForOwner(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT s.id, s.user_id, s.name, s.description, s.color, s.created_at,
                COUNT(t.id) AS task_count,
                SUM(t.status = "completed") AS completed_count
             FROM subjects s LEFT JOIN tasks t ON t.subject_id = s.id
             WHERE s.user_id = :user_id GROUP BY s.id ORDER BY s.name'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function allForUser(array $user): array
    {
        $sql = 'SELECT s.id, s.user_id, s.name, s.description, s.color, s.created_at, COUNT(t.id) AS task_count, SUM(t.status = "completed") AS completed_count FROM subjects s LEFT JOIN tasks t ON t.subject_id = s.id';
        $parameters = [];
        if ($user['role'] !== 'admin') {
            $sql .= ' WHERE s.user_id = :user_id';
            $parameters['user_id'] = $user['id'];
        }
        $sql .= ' GROUP BY s.id ORDER BY s.name';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findForUser(int $id, array $user): ?array
    {
        $sql = 'SELECT s.id, s.user_id, s.name, s.description, s.color, s.created_at, COUNT(t.id) AS task_count, SUM(t.status = "completed") AS completed_count FROM subjects s LEFT JOIN tasks t ON t.subject_id = s.id WHERE s.id = :id';
        $parameters = ['id' => $id];
        if ($user['role'] !== 'admin') {
            $sql .= ' AND s.user_id = :user_id';
            $parameters['user_id'] = $user['id'];
        }
        $sql .= ' GROUP BY s.id';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);
        $subject = $statement->fetch();

        return $subject ?: null;
    }

    public function create(array $attributes): void
    {
        Database::connection()->prepare(
            'INSERT INTO subjects (user_id, name, description, color) VALUES (:user_id, :name, :description, :color)'
        )->execute($attributes);
    }

    public function nameExistsForUser(int $userId, string $name, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM subjects WHERE user_id = :user_id AND name = :name';
        $parameters = ['user_id' => $userId, 'name' => $name];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $parameters['except_id'] = $exceptId;
        }
        $statement = Database::connection()->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    public function updateForUser(int $id, array $user, array $attributes): void
    {
        $sql = 'UPDATE subjects SET name = :name, description = :description, color = :color WHERE id = :id';
        $attributes['id'] = $id;
        if ($user['role'] !== 'admin') {
            $sql .= ' AND user_id = :user_id';
            $attributes['user_id'] = $user['id'];
        }
        Database::connection()->prepare($sql)->execute($attributes);
    }

    public function deleteForUser(int $id, array $user): void
    {
        $sql = 'DELETE FROM subjects WHERE id = :id';
        $parameters = ['id' => $id];
        if ($user['role'] !== 'admin') {
            $sql .= ' AND user_id = :user_id';
            $parameters['user_id'] = $user['id'];
        }
        Database::connection()->prepare($sql)->execute($parameters);
    }
}