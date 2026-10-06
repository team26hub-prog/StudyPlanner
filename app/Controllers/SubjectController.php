<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\Subject;
use App\Models\Task;

final class SubjectController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole('user');
        $this->render('subjects/index', ['subjects' => (new Subject())->allForUser($user)]);
    }

    public function createForm(): void
    {
        $this->requireRole('user');
        $this->render('subjects/form', [
            'subject' => null,
            'formAction' => url('/subjects'),
            'formTitle' => 'Add a subject',
        ]);
    }

    public function store(): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $attributes = $this->validatedAttributes((int) $user['id']);
        if ($attributes === null) {
            redirect('/subjects/create');
        }
        $attributes['user_id'] = $user['id'];
        (new Subject())->create($attributes);
        (new ActivityLog())->record($user, 'subject.created', "Created subject: {$attributes['name']}.");
        flash('notice', 'Subject added.');
        redirect('/subjects');
    }

    public function show(string $id): void
    {
        $user = $this->requireRole('user');
        $subject = (new Subject())->findForUser((int) $id, $user);
        if ($subject === null) {
            $this->notFound();
        }
        $this->render('subjects/show', [
            'subject' => $subject,
            'tasks' => (new Task())->allForUser($user, ['subject_id' => $id]),
        ]);
    }

    public function editForm(string $id): void
    {
        $user = $this->requireRole('user');
        $subject = (new Subject())->findForUser((int) $id, $user);
        if ($subject === null) {
            $this->notFound();
        }
        $this->render('subjects/form', [
            'subject' => $subject,
            'formAction' => url('/subjects/' . $id . '/update'),
            'formTitle' => 'Edit subject',
        ]);
    }

    public function update(string $id): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $subjects = new Subject();
        $existing = $subjects->findForUser((int) $id, $user);
        if ($existing === null) {
            $this->notFound();
        }
        $attributes = $this->validatedAttributes((int) $existing['user_id'], (int) $id);
        if ($attributes === null) {
            redirect('/subjects/' . $id . '/edit');
        }
        $subjects->updateForUser((int) $id, $user, $attributes);
        (new ActivityLog())->record($user, 'subject.updated', "Updated subject: {$attributes['name']}.");
        flash('notice', 'Subject details updated.');
        redirect('/subjects/' . $id);
    }

    public function delete(string $id): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $subjects = new Subject();
        $subject = $subjects->findForUser((int) $id, $user);
        if ($subject === null) {
            $this->notFound();
        }
        $subjects->deleteForUser((int) $id, $user);
        (new ActivityLog())->record($user, 'subject.deleted', "Deleted subject: {$subject['name']}.");
        flash('notice', 'Subject deleted. Its tasks and exams are kept without a subject.');
        redirect('/subjects');
    }

    private function validatedAttributes(int $ownerId, ?int $exceptId = null): ?array
    {
        $name = sanitize_text_value(input_string($_POST, 'name'));
        $description = sanitize_text_value(input_string($_POST, 'description'));
        $color = input_string($_POST, 'color');
        if (text_length($name) < 2 || text_length($name) > 100) {
            flash('error', 'Enter a subject name between 2 and 100 characters.');
            return null;
        }
        if (text_length($description) > 1000) {
            flash('error', 'Keep the subject description to 1,000 characters or fewer.');
            return null;
        }
        if ((new Subject())->nameExistsForUser($ownerId, $name, $exceptId)) {
            flash('error', 'You already have a subject with that name.');
            return null;
        }
        if (!in_array($color, ['#26745c', '#d47458', '#5277a5', '#997044', '#8065a3'], true)) {
            flash('error', 'Choose one of the available subject colors.');
            return null;
        }

        return ['name' => $name, 'description' => $description, 'color' => $color];
    }
}
