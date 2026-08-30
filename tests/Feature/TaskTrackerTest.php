<?php

namespace Tests\Feature;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTrackerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test dashboard displays accurate task counts and metrics.
     */
    public function test_dashboard_displays_correct_metrics(): void
    {
        Task::create([
            'title' => 'Pending Task',
            'description' => 'Test',
            'assigned_to' => 'Emon',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        Task::create([
            'title' => 'In Progress Task',
            'description' => 'Test',
            'assigned_to' => 'Ahmed',
            'priority' => 'Medium',
            'status' => 'In Progress',
            'due_date' => Carbon::now()->addDays(2),
        ]);

        Task::create([
            'title' => 'Completed Task',
            'description' => 'Test',
            'assigned_to' => 'Sarah',
            'priority' => 'Low',
            'status' => 'Completed',
            'due_date' => Carbon::yesterday(),
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(config('office.app_name'));
        $response->assertSee('Pending Task');
        $response->assertSee('33.3%'); // 1 of 3 completed
    }

    /**
     * Test task validation requires title and assigned_to with custom error messages.
     */
    public function test_task_creation_validates_required_fields(): void
    {
        $response = $this->post(route('tasks.store'), [
            'title' => '',
            'assigned_to' => '',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => '2026-09-01',
        ]);

        $response->assertSessionHasErrors([
            'title' => 'Task title is required.',
            'assigned_to' => 'Please select or enter the person responsible for this task.',
        ]);
    }

    /**
     * Test successful task creation and redirection.
     */
    public function test_can_create_task_successfully(): void
    {
        $response = $this->post(route('tasks.store'), [
            'title' => 'Deploy New Feature',
            'description' => 'Deploy to production server',
            'assigned_to' => 'Emon Ahmed',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Deploy New Feature',
            'assigned_to' => 'Emon Ahmed',
            'priority' => 'High',
        ]);
    }

    /**
     * Test task search by title and assigned person.
     */
    public function test_can_search_tasks(): void
    {
        Task::create([
            'title' => 'Fix Payment Gateway',
            'assigned_to' => 'Rahim',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        Task::create([
            'title' => 'Design Logo',
            'assigned_to' => 'Karim',
            'priority' => 'Low',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        // Search by title
        $response1 = $this->get(route('tasks.index', ['search' => 'Payment']));
        $response1->assertSee('Fix Payment Gateway');
        $response1->assertDontSee('Design Logo');

        // Search by assigned person
        $response2 = $this->get(route('tasks.index', ['search' => 'Karim']));
        $response2->assertSee('Design Logo');
        $response2->assertDontSee('Fix Payment Gateway');
    }

    /**
     * Test filter by status and priority.
     */
    public function test_can_filter_tasks_by_status_and_priority(): void
    {
        Task::create([
            'title' => 'Urgent Bug',
            'assigned_to' => 'Emon',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        Task::create([
            'title' => 'Low Priority Done',
            'assigned_to' => 'Emon',
            'priority' => 'Low',
            'status' => 'Completed',
            'due_date' => Carbon::tomorrow(),
        ]);

        $response = $this->get(route('tasks.index', ['status' => 'Pending', 'priority' => 'High']));
        $response->assertSee('Urgent Bug');
        $response->assertDontSee('Low Priority Done');
    }

    /**
     * Test CSV export feature flag.
     */
    public function test_csv_export_respects_feature_flag(): void
    {
        Task::create([
            'title' => 'Exportable Task',
            'assigned_to' => 'Emon',
            'priority' => 'Medium',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        // Enabled
        config(['office.enable_task_export' => true]);
        $response = $this->get(route('tasks.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // Disabled
        config(['office.enable_task_export' => false]);
        $responseDisabled = $this->get(route('tasks.export'));
        $responseDisabled->assertStatus(403);
    }

    /**
     * Test quick status update (AJAX / Kanban).
     */
    public function test_can_update_task_status_via_quick_action(): void
    {
        $task = Task::create([
            'title' => 'Quick Status Task',
            'assigned_to' => 'Emon Ahmed',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        $response = $this->patchJson(route('tasks.update-status', $task), [
            'status' => 'Completed',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'Completed',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'Completed',
        ]);
    }

    /**
     * Test subtask creation and toggle.
     */
    public function test_can_add_and_toggle_subtasks(): void
    {
        $task = Task::create([
            'title' => 'Parent Task',
            'assigned_to' => 'Emon',
            'priority' => 'Medium',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        // Add subtask
        $response = $this->post(route('subtasks.store', $task), [
            'title' => 'First subtask item',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subtasks', [
            'task_id' => $task->id,
            'title' => 'First subtask item',
            'is_completed' => false,
        ]);

        $subtask = $task->subtasks()->first();

        // Toggle subtask
        $toggleResponse = $this->patchJson(route('subtasks.toggle', $subtask));
        $toggleResponse->assertStatus(200);
        $toggleResponse->assertJson(['is_completed' => true]);

        $this->assertDatabaseHas('subtasks', [
            'id' => $subtask->id,
            'is_completed' => true,
        ]);
    }

    /**
     * Test task discussion comments.
     */
    public function test_can_post_task_comment(): void
    {
        $task = Task::create([
            'title' => 'Task for discussion',
            'assigned_to' => 'Emon',
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => Carbon::tomorrow(),
        ]);

        $response = $this->post(route('tasks.comments.store', $task), [
            'user_name' => 'Emon Ahmed',
            'comment' => 'This is a test discussion update.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_name' => 'Emon Ahmed',
            'comment' => 'This is a test discussion update.',
        ]);
    }
}
