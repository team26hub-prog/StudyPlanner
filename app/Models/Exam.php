<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Exam
{
    public function allForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT e.id, e.user_id, e.subject_id, e.title, e.exam_at, e.status, e.created_at,
                s.name AS subject_name, s.color AS subject_color
             FROM exams e LEFT JOIN subjects s ON s.id = e.subject_id
             WHERE e.user_id = :user_id
             ORDER BY e.exam_at, e.id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, user_id, subject_id, title, exam_at, status
             FROM exams WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['id' => $id, 'user_id' => $userId]);
        $exam = $statement->fetch();

        return $exam ?: null;
    }

    public function create(array $attributes): void
    {
        Database::connection()->prepare(
            'INSERT INTO exams (user_id, subject_id, title, exam_at, status)
             VALUES (:user_id, :subject_id, :title, :exam_at, :status)'
        )->execute($attributes);
    }

    public function updateForUser(int $id, int $userId, array $attributes): void
    {
        $attributes['id'] = $id;
        $attributes['user_id'] = $userId;
        Database::connection()->prepare(
            'UPDATE exams SET subject_id = :subject_id, title = :title, exam_at = :exam_at, status = :status
             WHERE id = :id AND user_id = :user_id'
        )->execute($attributes);
    }

    public function updateStatusForUser(int $id, int $userId, string $status): void
    {
        Database::connection()->prepare(
            'UPDATE exams SET status = :status WHERE id = :id AND user_id = :user_id'
        )->execute(['status' => $status, 'id' => $id, 'user_id' => $userId]);
    }

    public function deleteForUser(int $id, int $userId): void
    {
        Database::connection()->prepare(
            'DELETE FROM exams WHERE id = :id AND user_id = :user_id'
        )->execute(['id' => $id, 'user_id' => $userId]);
    }
}