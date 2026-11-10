<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Rbac\RbacTestCase;

class UserAvatarTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
        Storage::fake('public');
    }

    public function test_employee_can_upload_own_avatar_via_profile(): void
    {
        $employee = User::factory()->create([
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
        ]);

        $file = UploadedFile::fake()->image('profile.jpg', 200, 200);

        $response = $this->actingAs($employee)->post('/profile/avatar', [
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $employee->refresh();
        $this->assertNotNull($employee->avatar);
        Storage::disk('public')->assertExists($employee->avatar);
        $this->assertNotNull($employee->avatar_url);
    }

    public function test_employee_can_remove_own_avatar(): void
    {
        $file = UploadedFile::fake()->image('me.png', 100, 100);
        $path = $file->store('avatars', 'public');

        $employee = User::factory()->create([
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
            'avatar' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($employee)->delete('/profile/avatar');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $employee->refresh();
        $this->assertNull($employee->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_hr_can_upload_employee_avatar(): void
    {
        $hrUser = User::factory()->create([
            'department' => 'hr',
            'designation' => 'hr executive',
            'role' => 'hr',
        ]);

        $employee = User::factory()->create([
            'department' => 'marketing',
            'designation' => 'content writer',
            'role' => 'marketing',
        ]);

        $file = UploadedFile::fake()->image('emp_avatar.png', 300, 300);

        $response = $this->actingAs($hrUser)->post("/users/{$employee->id}/avatar", [
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $employee->refresh();
        $this->assertNotNull($employee->avatar);
        Storage::disk('public')->assertExists($employee->avatar);
    }

    public function test_hr_can_remove_employee_avatar(): void
    {
        $hrUser = User::factory()->create([
            'department' => 'hr',
            'designation' => 'hr manager',
            'role' => 'hr',
        ]);

        $file = UploadedFile::fake()->image('old_photo.jpg', 150, 150);
        $path = $file->store('avatars', 'public');

        $employee = User::factory()->create([
            'department' => 'sales',
            'designation' => 'sales executive',
            'role' => 'sales',
            'avatar' => $path,
        ]);

        $response = $this->actingAs($hrUser)->delete("/users/{$employee->id}/avatar");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $employee->refresh();
        $this->assertNull($employee->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_upload_employee_avatar(): void
    {
        $admin = $this->admin();

        $employee = User::factory()->create([
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
        ]);

        $file = UploadedFile::fake()->image('admin_upload.webp', 200, 200);

        $response = $this->actingAs($admin)->post("/users/{$employee->id}/avatar", [
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $employee->refresh();
        $this->assertNotNull($employee->avatar);
        Storage::disk('public')->assertExists($employee->avatar);
    }

    public function test_unauthorized_employee_cannot_manage_other_employee_avatar(): void
    {
        $dev1 = User::factory()->create([
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
        ]);

        $dev2 = User::factory()->create([
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
        ]);

        $file = UploadedFile::fake()->image('hacker.jpg', 100, 100);

        $response = $this->actingAs($dev1)->post("/users/{$dev2->id}/avatar", [
            'avatar' => $file,
        ]);

        $response->assertStatus(403);
        $this->assertNull($dev2->fresh()->avatar);

        // Also test unauthorized delete
        $dev2->update(['avatar' => 'avatars/fake.jpg']);
        $deleteResponse = $this->actingAs($dev1)->delete("/users/{$dev2->id}/avatar");
        $deleteResponse->assertStatus(403);
    }

    public function test_uploading_new_avatar_deletes_previous_avatar_file(): void
    {
        $employee = User::factory()->create([
            'department' => 'development',
            'designation' => 'software developer',
            'role' => 'development',
        ]);

        $firstFile = UploadedFile::fake()->image('first.jpg', 100, 100);
        $this->actingAs($employee)->post('/profile/avatar', ['avatar' => $firstFile]);

        $employee->refresh();
        $firstPath = $employee->avatar;
        Storage::disk('public')->assertExists($firstPath);

        // Upload second avatar
        $secondFile = UploadedFile::fake()->image('second.png', 100, 100);
        $this->actingAs($employee)->post('/profile/avatar', ['avatar' => $secondFile]);

        $employee->refresh();
        $secondPath = $employee->avatar;

        $this->assertNotEquals($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }

    public function test_avatar_validation_rejects_non_image_files(): void
    {
        $employee = User::factory()->create();

        $textDoc = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($employee)->post('/profile/avatar', [
            'avatar' => $textDoc,
        ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($employee->fresh()->avatar);
    }

    public function test_avatar_fallback_route_serves_image(): void
    {
        $file = UploadedFile::fake()->image('my_pic.jpg', 50, 50);
        $path = $file->store('avatars', 'public');
        $filename = basename($path);

        $response = $this->get("/storage/avatars/{$filename}");
        $response->assertStatus(200);
        $this->assertTrue(str_starts_with($response->headers->get('content-type'), 'image/'));
    }
}
