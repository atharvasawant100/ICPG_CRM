<?php

namespace Tests\Feature\Content;

use App\Console\Commands\CheckMissedContentSchedules;
use App\Models\Brand;
use App\Models\ContentApproval;
use App\Models\ContentMedia;
use App\Models\ContentPlatformSchedule;
use App\Models\ContentPost;
use App\Models\Customer;
use App\Models\SocialAccount;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ContentApprovalDecisionNotification;
use App\Notifications\ContentAssignedNotification;
use App\Notifications\ContentPublishMissedNotification;
use App\Notifications\ContentReviewSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Rbac\RbacTestCase;

class SocialMediaContentCalendarTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    private function marketingManager(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'department' => 'marketing',
            'designation' => 'Marketing Manager',
        ], $attributes));

        $user->assignRole('marketing_manager');
        return $user;
    }

    private function marketingEmployee(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'department' => 'marketing',
            'designation' => 'Social Media Executive',
        ], $attributes));

        $user->assignRole('marketing');
        return $user;
    }

    private function createCustomer(?int $userId = null): Customer
    {
        return Customer::create([
            'created_by' => $userId ?? 1,
            'name' => 'Acme Global Client',
            'email' => 'client' . uniqid() . '@acmeglobal.com',
            'company_name' => 'Acme Corporation',
            'status' => 'active',
        ]);
    }

    /**
     * Test Brand and Social Account Management (Requirement 3).
     */
    public function test_can_manage_brands_and_social_accounts(): void
    {
        $manager = $this->marketingManager();
        $client = $this->createCustomer($manager->id);

        // 1. Create Brand
        $response = $this->actingAs($manager)->post(route('brands.store'), [
            'client_id' => $client->id,
            'name' => 'Acme Athletic',
            'industry' => 'Sports & Apparel',
            'primary_color' => '#ef4444',
            'description' => 'Athletic footwear and apparel division.',
            'website' => 'https://acmeathletic.com',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'customer_id' => $client->id,
            'name' => 'Acme Athletic',
            'industry' => 'Sports & Apparel',
        ]);

        $brand = Brand::where('name', 'Acme Athletic')->first();
        $this->assertNotNull($brand);

        // 2. Connect Social Account
        $accountResponse = $this->actingAs($manager)->post(route('social-accounts.store'), [
            'brand_id' => $brand->id,
            'platform' => 'instagram',
            'account_name' => 'Acme Athletic Official IG',
            'username' => 'acme_athletic',
            'platform_account_id' => 'ig_12345678',
            'connection_status' => 'connected',
        ]);

        $accountResponse->assertRedirect();
        $this->assertDatabaseHas('social_accounts', [
            'brand_id' => $brand->id,
            'customer_id' => $client->id,
            'platform' => 'instagram',
            'username' => 'acme_athletic',
        ]);

        $socialAccount = SocialAccount::where('username', 'acme_athletic')->first();

        // 3. Update Social Account
        $updateResponse = $this->actingAs($manager)->put(route('social-accounts.update', $socialAccount), [
            'account_name' => 'Acme Athletic Pro IG',
            'username' => 'acme_athletic_pro',
            'connection_status' => 'connected',
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('social_accounts', [
            'id' => $socialAccount->id,
            'account_name' => 'Acme Athletic Pro IG',
        ]);
    }

    /**
     * Test Content Post creation with multi-platform schedules, media files, and notification.
     */
    public function test_can_create_content_post_with_media_and_multi_platform_schedules(): void
    {
        Notification::fake();
        Storage::fake('public');

        $manager = $this->marketingManager();
        $employee = $this->marketingEmployee();
        $client = $this->createCustomer($manager->id);

        $brand = Brand::create([
            'client_id' => $client->id,
            'name' => 'Acme Energy',
            'is_active' => true,
        ]);

        $file1 = UploadedFile::fake()->image('banner.jpg', 800, 600);
        $file2 = UploadedFile::fake()->create('video_clip.mp4', 5000, 'video/mp4');

        $postData = [
            'brand_id' => $brand->id,
            'title' => 'Summer Fitness Energy Campaign',
            'post_type' => 'carousel',
            'status' => 'in_creation',
            'caption' => 'Fuel your workouts this summer with Acme Energy! #Fitness #Energy',
            'hashtags' => '#AcmeEnergy #FuelYourDay',
            'platforms' => ['instagram', 'facebook', 'linkedin'],
            'scheduled_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'scheduled_time' => '17:30',
            'timezone' => 'Asia/Kolkata',
            'assigned_to' => $employee->id,
            'reviewer_id' => $manager->id,
            'require_approval' => '1',
            'notes' => 'Ensure logo is placed on top right of all carousel slides.',
            'media' => [$file1, $file2],
        ];

        $response = $this->actingAs($manager)->post(route('content.posts.store'), $postData);
        $response->assertRedirect();

        $post = ContentPost::where('title', 'Summer Fitness Energy Campaign')->first();
        $this->assertNotNull($post);
        $this->assertEquals($brand->id, $post->brand_id);
        $this->assertEquals($client->id, $post->client_id);
        $this->assertEquals('carousel', $post->post_type);
        $this->assertEquals('in_creation', $post->status);
        $this->assertEquals($employee->id, $post->assigned_to);

        // Check platform schedules
        $this->assertCount(3, $post->platformSchedules);
        $this->assertTrue($post->platformSchedules->pluck('platform')->contains('instagram'));
        $this->assertTrue($post->platformSchedules->pluck('platform')->contains('facebook'));
        $this->assertTrue($post->platformSchedules->pluck('platform')->contains('linkedin'));

        // Check media attachments
        $this->assertCount(2, $post->media);
        $firstMedia = $post->media->first();
        Storage::disk('public')->assertExists($firstMedia->file_path);

        // Check assignment notification sent
        Notification::assertSentTo($employee, ContentAssignedNotification::class);
    }

    /**
     * Test Review and Approval Lifecycle (Requirements 6 & 8).
     */
    public function test_content_review_and_approval_workflow(): void
    {
        Notification::fake();

        $manager = $this->marketingManager();
        $employee = $this->marketingEmployee();
        $client = $this->createCustomer($manager->id);

        $brand = Brand::create(['client_id' => $client->id, 'name' => 'Brand Alpha', 'is_active' => true]);

        $post = ContentPost::create([
            'client_id' => $client->id,
            'brand_id' => $brand->id,
            'created_by' => $employee->id,
            'assigned_to' => $employee->id,
            'reviewer_id' => $manager->id,
            'title' => 'New Season Reveal',
            'post_type' => 'single_image',
            'caption' => 'The wait is over! Reveal tomorrow.',
            'status' => 'in_creation',
            'scheduled_date' => Carbon::tomorrow()->format('Y-m-d'),
            'scheduled_time' => '10:00:00',
            'require_approval' => true,
        ]);

        // 1. Employee submits for review
        $submitResponse = $this->actingAs($employee)->post(route('content.posts.submit-review', $post));
        $submitResponse->assertRedirect();

        $post->refresh();
        $this->assertEquals('pending_review', $post->status);
        Notification::assertSentTo($manager, ContentReviewSubmittedNotification::class);

        // 2. Manager requests changes
        $rejectResponse = $this->actingAs($manager)->post(route('content.posts.decide-review', $post), [
            'decision' => 'changes_requested',
            'comments' => 'Please add brand hashtag and brighten the image colors.',
        ]);
        $rejectResponse->assertRedirect();

        $post->refresh();
        $this->assertEquals('changes_requested', $post->status);
        $this->assertDatabaseHas('content_approvals', [
            'content_post_id' => $post->id,
            'reviewer_id' => $manager->id,
            'action' => 'changes_requested',
            'comment' => 'Please add brand hashtag and brighten the image colors.',
        ]);
        Notification::assertSentTo($employee, ContentApprovalDecisionNotification::class);

        // 3. Employee resubmits
        $this->actingAs($employee)->post(route('content.posts.submit-review', $post));
        $post->refresh();
        $this->assertEquals('pending_review', $post->status);

        // 4. Manager approves post
        $approveResponse = $this->actingAs($manager)->post(route('content.posts.decide-review', $post), [
            'decision' => 'approved',
            'comments' => 'Looks stunning! Approved for scheduling.',
        ]);
        $approveResponse->assertRedirect();

        $post->refresh();
        $this->assertEquals('approved', $post->status);
        $this->assertDatabaseHas('content_approvals', [
            'content_post_id' => $post->id,
            'reviewer_id' => $manager->id,
            'action' => 'approved',
        ]);
    }

    /**
     * Test Calendar Feed JSON & Drag-and-drop Reschedule (Requirement 4).
     */
    public function test_calendar_feed_and_reschedule(): void
    {
        $manager = $this->marketingManager();
        $client = $this->createCustomer($manager->id);
        $brand = Brand::create(['client_id' => $client->id, 'name' => 'Brand Beta', 'primary_color' => '#10b981', 'is_active' => true]);

        $post = ContentPost::create([
            'client_id' => $client->id,
            'brand_id' => $brand->id,
            'created_by' => $manager->id,
            'assigned_to' => $manager->id,
            'title' => 'Weekly Motivation Post',
            'post_type' => 'text_post',
            'status' => 'scheduled',
            'scheduled_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'scheduled_time' => '12:00:00',
        ]);

        ContentPlatformSchedule::create([
            'content_post_id' => $post->id,
            'platform' => 'linkedin',
            'scheduled_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'status' => 'scheduled',
        ]);

        // 1. Fetch JSON Feed
        $feedResponse = $this->actingAs($manager)->getJson(route('content.calendar.feed', [
            'start' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end' => Carbon::now()->endOfMonth()->format('Y-m-d'),
        ]));

        $feedResponse->assertOk();
        $feedData = $feedResponse->json();
        $this->assertNotEmpty($feedData);
        $this->assertEquals($post->id, $feedData[0]['id']);
        $this->assertEquals('Weekly Motivation Post', $feedData[0]['title']);

        // 1b. Verify Calendar Page & Dashboard Views render with HTTP 200
        $this->actingAs($manager)->get(route('content.calendar'))->assertOk();
        $this->actingAs($manager)->get(route('content.dashboard'))->assertOk();
        $this->actingAs($manager)->get(route('content.posts.index'))->assertOk();
        $this->actingAs($manager)->get(route('content.posts.create'))->assertOk();
        $this->actingAs($manager)->get(route('content.posts.show', $post))->assertOk();
        $this->actingAs($manager)->get(route('content.posts.edit', $post))->assertOk();
        $this->actingAs($manager)->get(route('brands.index'))->assertOk();
        $this->actingAs($manager)->get(route('brands.create'))->assertOk();
        $this->actingAs($manager)->get(route('brands.show', $brand))->assertOk();
        $this->actingAs($manager)->get(route('brands.edit', $brand))->assertOk();

        // 2. Reschedule via PATCH API
        $newTargetDate = Carbon::now()->addDays(5)->format('Y-m-d');
        $rescheduleResponse = $this->actingAs($manager)->patchJson(route('content.posts.reschedule', $post), [
            'scheduled_date' => $newTargetDate,
            'scheduled_time' => '15:00',
        ]);

        $rescheduleResponse->assertOk();
        $this->assertTrue($rescheduleResponse->json('success'));

        $post->refresh();
        $this->assertEquals($newTargetDate, $post->scheduled_at->format('Y-m-d'));

        // Verify platform schedule synchronized
        $this->assertEquals($newTargetDate, $post->platformSchedules->first()->scheduled_at->format('Y-m-d'));
    }

    /**
     * Test Manual Publishing and Failed Post Tracking (Requirement 10).
     */
    public function test_manual_publishing_and_failure_recording(): void
    {
        $manager = $this->marketingManager();
        $client = $this->createCustomer($manager->id);
        $brand = Brand::create(['client_id' => $client->id, 'name' => 'Brand Gamma', 'is_active' => true]);

        $post = ContentPost::create([
            'client_id' => $client->id,
            'brand_id' => $brand->id,
            'created_by' => $manager->id,
            'title' => 'Product Launch Video',
            'post_type' => 'video',
            'status' => 'scheduled',
            'scheduled_date' => Carbon::today()->format('Y-m-d'),
            'scheduled_time' => '09:00:00',
        ]);

        // 1. Queue list renders scheduled post
        $queueResponse = $this->actingAs($manager)->get(route('content.queue'));
        $queueResponse->assertOk();
        $queueResponse->assertSee('Product Launch Video');

        // 2. Mark as Published
        $publishResponse = $this->actingAs($manager)->post(route('content.posts.publish', $post), [
            'published_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'live_url' => 'https://instagram.com/p/Cxyz12345',
            'notes' => 'Successfully posted to Instagram Reels. High initial engagement.',
        ]);

        $publishResponse->assertRedirect();
        $post->refresh();
        $this->assertEquals('published', $post->status);
        $this->assertEquals('https://instagram.com/p/Cxyz12345', $post->live_url);
        $this->assertNotNull($post->published_at);

        // 3. Mark another post as failed
        $post2 = ContentPost::create([
            'client_id' => $client->id,
            'brand_id' => $brand->id,
            'created_by' => $manager->id,
            'title' => 'Cancelled Story Promo',
            'post_type' => 'story',
            'status' => 'scheduled',
            'scheduled_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $failResponse = $this->actingAs($manager)->post(route('content.posts.fail', $post2), [
            'failure_reason' => 'Client requested pause on promotional stories due to supply chain delay.',
        ]);

        $failResponse->assertRedirect();
        $post2->refresh();
        $this->assertEquals('failed', $post2->status);
        $this->assertStringContainsString('Client requested pause', $post2->failure_reason);
    }

    /**
     * Test Missed Schedules Monitoring Command (Requirement 11).
     */
    public function test_missed_content_schedules_artisan_command(): void
    {
        Notification::fake();

        $manager = $this->marketingManager();
        $employee = $this->marketingEmployee();
        $client = $this->createCustomer($manager->id);
        $brand = Brand::create(['client_id' => $client->id, 'name' => 'Brand Delta', 'is_active' => true]);

        // Create overdue scheduled post (scheduled 2 hours ago)
        $pastDate = Carbon::today()->format('Y-m-d');
        $pastTime = Carbon::now()->subHours(2)->format('H:i:s');

        $overduePost = ContentPost::create([
            'client_id' => $client->id,
            'brand_id' => $brand->id,
            'created_by' => $manager->id,
            'assigned_to' => $employee->id,
            'title' => 'Overdue Flash Sale Post',
            'post_type' => 'single_image',
            'status' => 'scheduled',
            'scheduled_date' => $pastDate,
            'scheduled_time' => $pastTime,
        ]);

        // Run artisan command
        $this->artisan('content:check-missed-schedules')
            ->expectsOutputToContain('Flagged 1 overdue scheduled post(s)')
            ->assertExitCode(0);

        // Verify notification sent to assignee
        Notification::assertSentTo($employee, ContentPublishMissedNotification::class);
    }

    /**
     * Test Task Management Integration (Requirement 7):
     * Links content post directly to a real task in the central Task Pool.
     */
    public function test_task_management_integration_creates_real_task(): void
    {
        Notification::fake();

        $manager = $this->marketingManager();
        $employee = $this->marketingEmployee();
        $client = $this->createCustomer($manager->id);
        $brand = Brand::create(['client_id' => $client->id, 'name' => 'Brand Task Test', 'is_active' => true]);

        $post = ContentPost::create([
            'client_id' => $client->id,
            'brand_id' => $brand->id,
            'created_by' => $manager->id,
            'title' => 'Holiday Gift Guide Carousel',
            'post_type' => 'carousel',
            'status' => 'planned',
            'scheduled_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ]);

        $taskData = [
            'content_task_type' => 'create_graphic',
            'title' => 'Design 5 carousel slides in Figma',
            'description' => 'Follow brand color palette and use holiday icons.',
            'assigned_to' => $employee->id,
            'due_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
            'priority' => 'high',
        ];

        $response = $this->actingAs($manager)->post(route('content.posts.tasks.store', $post), $taskData);
        $response->assertRedirect();

        // Verify task exists in the real tasks table
        $this->assertDatabaseHas('tasks', [
            'content_post_id' => $post->id,
            'content_task_type' => 'create_graphic',
            'title' => 'Design 5 carousel slides in Figma',
            'assigned_to' => $employee->id,
            'assigned_by' => $manager->id,
            'department' => 'marketing',
        ]);

        $createdTask = Task::where('content_post_id', $post->id)->first();
        $this->assertNotNull($createdTask);
        $this->assertEquals($post->id, $createdTask->contentPost->id);

        // Post status automatically advanced to 'in_creation'
        $post->refresh();
        $this->assertEquals('in_creation', $post->status);
    }

    public function test_marketing_department_restriction_and_employee_cannot_assign_to_manager(): void
    {
        $manager = $this->marketingManager();
        $employee = $this->marketingEmployee();
        $client = $this->createCustomer($manager->id);
        $brand = Brand::create(['client_id' => $client->id, 'name' => 'Brand Dept Test', 'is_active' => true]);

        // Create an external HR user
        $hrUser = User::factory()->create([
            'department' => 'hr',
            'designation' => 'hr executive',
            'is_active' => true,
        ]);

        // 1. Employee visits create post view -> view only contains marketing non-managers
        $createView = $this->actingAs($employee)->get(route('content.posts.create'));
        $createView->assertOk();
        $viewEmployees = $createView->viewData('employees');
        $this->assertTrue($viewEmployees->contains('id', $employee->id));
        $this->assertFalse($viewEmployees->contains('id', $manager->id), 'Employee should not see manager in assignable employees');
        $this->assertFalse($viewEmployees->contains('id', $hrUser->id), 'Employee should not see HR staff');

        // 2. Employee cannot submit post assigned to manager
        $invalidAssignManager = $this->actingAs($employee)->post(route('content.posts.store'), [
            'brand_id' => $brand->id,
            'title' => 'Test Post Assign To Manager',
            'post_type' => 'single_image',
            'assigned_to' => $manager->id,
            'platforms' => ['instagram'],
        ]);
        $invalidAssignManager->assertSessionHasErrors(['assigned_to']);

        // 3. Employee cannot submit post assigned to non-marketing user
        $invalidAssignHr = $this->actingAs($employee)->post(route('content.posts.store'), [
            'brand_id' => $brand->id,
            'title' => 'Test Post Assign To HR',
            'post_type' => 'single_image',
            'assigned_to' => $hrUser->id,
            'platforms' => ['instagram'],
        ]);
        $invalidAssignHr->assertSessionHasErrors(['assigned_to']);

        // 4. Employee can assign to himself / fellow marketing executive
        $validPost = $this->actingAs($employee)->post(route('content.posts.store'), [
            'brand_id' => $brand->id,
            'title' => 'Valid Employee Post',
            'post_type' => 'single_image',
            'assigned_to' => $employee->id,
            'platforms' => ['instagram'],
        ]);
        $validPost->assertRedirect();
        $this->assertDatabaseHas('content_posts', [
            'title' => 'Valid Employee Post',
            'assigned_to' => $employee->id,
        ]);

        // 5. Employee with assign permission cannot create linked task assigned to manager
        $employee->givePermissionTo('content.assign');
        $post = ContentPost::where('title', 'Valid Employee Post')->first();
        $invalidTask = $this->actingAs($employee)->post(route('content.posts.tasks.store', $post), [
            'content_task_type' => 'write_caption',
            'title' => 'Write caption for manager',
            'assigned_to' => $manager->id,
            'priority' => 'medium',
        ]);
        $invalidTask->assertSessionHasErrors(['assigned_to']);

        // 6. Manager CAN assign to marketing employee
        $validTask = $this->actingAs($manager)->post(route('content.posts.tasks.store', $post), [
            'content_task_type' => 'write_caption',
            'title' => 'Write caption task by manager',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
        ]);
        $validTask->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'title' => 'Write caption task by manager',
            'assigned_to' => $employee->id,
        ]);
    }
}
