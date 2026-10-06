<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\Exam;
use App\Models\Subject;

final class ExamController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole('user');
        $this->render('exams/index', [
            'exams' => (new Exam())->allForUser((int) $user['id']),
        ]);
    }

    public function createForm(): void
    {
        $user = $this->requireRole('user');
        $this->render('exams/form', [
            'exam' => null,
            'subjects' => (new Subject())->allForUser($user),
            'formAction' => url('/exams'),
            'formTitle' => 'Add an exam',
        ]);
    }

    public function store(): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $attributes = $this->validatedAttributes($user);
        if ($attributes === null) {
            redirect('/exams/create');
        }

        $attributes['user_id'] = (int) $user['id'];
        (new Exam())->create($attributes);
        (new ActivityLog())->record($user, 'exam.created', "Created exam: {$attributes['title']}.");
        flash('notice', 'Exam added to your schedule.');
        redirect('/exams');
    }

    public function editForm(string $id): void
    {
        $user = $this->requireRole('user');
        $exam = (new Exam())->findForUser((int) $id, (int) $user['id']);
        if ($exam === null) {
            $this->notFound();
        }

        $this->render('exams/form', [
            'exam' => $exam,
            'subjects' => (new Subject())->allForUser($user),
            'formAction' => url('/exams/' . $id . '/update'),
            'formTitle' => 'Edit exam',
        ]);
    }

    public function update(string $id): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $exams = new Exam();
        if ($exams->findForUser((int) $id, (int) $user['id']) === null) {
            $this->notFound();
        }

        $attributes = $this->validatedAttributes($user);
        if ($attributes === null) {
            redirect('/exams/' . $id . '/edit');
        }

        $exams->updateForUser((int) $id, (int) $user['id'], $attributes);
        (new ActivityLog())->record($user, 'exam.updated', "Updated exam: {$attributes['title']}.");
        flash('notice', 'Exam details updated.');
        redirect('/exams');
    }

    public function updateStatus(string $id): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $exams = new Exam();
        $exam = $exams->findForUser((int) $id, (int) $user['id']);
        if ($exam === null) {
            $this->notFound();
        }

        $status = input_string($_POST, 'status');
        if (!in_array($status, ['scheduled', 'completed', 'missed'], true)) {
            flash('error', 'Choose a valid exam status.');
            redirect('/exams');
        }

        $exams->updateStatusForUser((int) $id, (int) $user['id'], $status);
        (new ActivityLog())->record($user, 'exam.status_changed', "Changed exam status: {$exam['title']} to {$status}.");
        flash('notice', 'Exam status updated.');
        redirect('/exams');
    }

    public function delete(string $id): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $exams = new Exam();
        $exam = $exams->findForUser((int) $id, (int) $user['id']);
        if ($exam === null) {
            $this->notFound();
        }

        $exams->deleteForUser((int) $id, (int) $user['id']);
        (new ActivityLog())->record($user, 'exam.deleted', "Deleted exam: {$exam['title']}.");
        flash('notice', 'Exam deleted.');
        redirect('/exams');
    }

    private function validatedAttributes(array $user): ?array
    {
        $title = sanitize_text_value(input_string($_POST, 'title'));
        $subjectId = input_string($_POST, 'subject_id');
        $examAt = input_string($_POST, 'exam_at');
        $status = input_string($_POST, 'status');

        if (text_length($title) < 2 || text_length($title) > 120) {
            flash('error', 'Enter an exam name between 2 and 120 characters.');
            return null;
        }
        if (!ctype_digit($subjectId) || (int) $subjectId < 1 || (new Subject())->findForUser((int) $subjectId, $user) === null) {
            flash('error', 'Choose one of your subjects for this exam.');
            return null;
        }
        if (!preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}\z/D', $examAt)) {
            flash('error', 'Enter a valid exam date and time.');
            return null;
        }
        $parsedDate = \DateTime::createFromFormat('!Y-m-d\TH:i', $examAt);
        if ($parsedDate === false || $parsedDate->format('Y-m-d\TH:i') !== $examAt || (int) substr($examAt, 0, 4) < 1000) {
            flash('error', 'Enter a valid exam date and time.');
            return null;
        }
        if (!in_array($status, ['scheduled', 'completed', 'missed'], true)) {
            flash('error', 'Choose a valid exam status.');
            return null;
        }

        return [
            'subject_id' => (int) $subjectId,
            'title' => $title,
            'exam_at' => $parsedDate->format('Y-m-d H:i:s'),
            'status' => $status,
        ];
    }
}
