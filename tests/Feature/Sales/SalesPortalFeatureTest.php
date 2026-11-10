<?php

namespace Tests\Feature\Sales;

use App\Mail\LeadMeetingCancelledMail;
use App\Mail\LeadMeetingInviteMail;
use App\Mail\LeadMeetingRescheduledMail;
use App\Models\EmailLog;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadStatusLog;
use App\Models\User;
use App\Rbac\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SalesPortalFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::firstOrCreate(
            ['email' => RolePermissionSeeder::ADMIN_EMAILS[0]],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_sales_tracking_dashboard_accessible_to_sales_rep(): void
    {
        $salesRep = User::factory()->create(['role' => 'sales']);
        $salesRep->assignRbacRole('sales');

        $response = $this->actingAs($salesRep)->get(route('sales-tracking.index'));
        $response->assertStatus(200);
        $response->assertSee('Sales Tracking');
        $response->assertSee('Personal Performance View');
    }

    public function test_sales_rep_only_sees_leads_assigned_to_them(): void
    {
        $salesRepA = User::factory()->create(['role' => 'sales']);
        $salesRepA->assignRbacRole('sales');

        $salesRepB = User::factory()->create(['role' => 'sales']);
        $salesRepB->assignRbacRole('sales');

        $leadA = Lead::create([
            'name' => 'Lead For Rep A',
            'email' => 'leada@example.com',
            'status' => 'new',
            'created_by' => $salesRepA->id,
            'assigned_to' => $salesRepA->id,
        ]);

        $leadB = Lead::create([
            'name' => 'Lead For Rep B',
            'email' => 'leadb@example.com',
            'status' => 'new',
            'created_by' => $salesRepB->id,
            'assigned_to' => $salesRepB->id,
        ]);

        // Rep A can view index and see their lead, but not Rep B's lead
        $response = $this->actingAs($salesRepA)->get(route('leads.index'));
        $response->assertStatus(200);
        $response->assertSee('Lead For Rep A');
        $response->assertDontSee('Lead For Rep B');

        // Rep A can view show for leadA
        $this->actingAs($salesRepA)->get(route('leads.show', $leadA))->assertStatus(200);

        // Rep A cannot view show for leadB
        $this->actingAs($salesRepA)->get(route('leads.show', $leadB))->assertStatus(403);
    }

    public function test_lead_status_transition_records_audit_trail(): void
    {
        $salesRep = User::factory()->create(['role' => 'sales']);
        $salesRep->assignRbacRole('sales');

        $lead = Lead::create([
            'name' => 'Prospect John',
            'email' => 'john@example.com',
            'status' => 'new',
            'created_by' => $salesRep->id,
            'assigned_to' => $salesRep->id,
        ]);

        $response = $this->actingAs($salesRep)->patch(route('leads.status.update', $lead), [
            'status' => 'contacted',
            'notes' => 'Spoke over the phone today, client asked for pricing.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'contacted',
        ]);

        $this->assertDatabaseHas('lead_status_logs', [
            'lead_id' => $lead->id,
            'changed_by' => $salesRep->id,
            'old_status' => 'new',
            'new_status' => 'contacted',
            'notes' => 'Spoke over the phone today, client asked for pricing.',
        ]);
    }

    public function test_meeting_followup_generates_meeting_link_and_sends_email(): void
    {
        Mail::fake();

        $salesRep = User::factory()->create(['role' => 'sales']);
        $salesRep->assignRbacRole('sales');

        $lead = Lead::create([
            'name' => 'Meeting Lead',
            'email' => 'meetinglead@example.com',
            'status' => 'new',
            'created_by' => $salesRep->id,
            'assigned_to' => $salesRep->id,
        ]);

        $meetingDate = now()->addDays(2)->format('Y-m-d\TH:i');

        $response = $this->actingAs($salesRep)->post(route('leads.followups.store', $lead), [
            'followup_date' => $meetingDate,
            'followup_type' => 'meeting',
            'meeting_platform' => 'jitsi',
            'agenda' => 'Initial Demo & Consultation',
            'send_email_to_lead' => '1',
            'description' => 'Demo scheduled with client',
        ]);

        $response->assertSessionHasNoErrors();

        $followup = LeadFollowup::where('lead_id', $lead->id)->first();
        $this->assertNotNull($followup);
        $this->assertEquals('meeting', $followup->followup_type);
        $this->assertNotNull($followup->meeting_link);
        $this->assertStringContainsString('meet.jit.si', $followup->meeting_link);

        Mail::assertSent(LeadMeetingInviteMail::class, function ($mail) use ($lead) {
            return $mail->hasTo($lead->email);
        });

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => $lead->email,
            'type' => EmailLog::TYPE_MEETING_INVITE,
            'status' => 'sent',
        ]);

        // Test reschedule
        $newMeetingDate = now()->addDays(4)->format('Y-m-d\TH:i');
        $this->actingAs($salesRep)->post(route('followups.reschedule', $followup), [
            'followup_date' => $newMeetingDate,
            'reschedule_reason' => 'Client postponed',
        ])->assertSessionHasNoErrors();

        Mail::assertSent(LeadMeetingRescheduledMail::class, function ($mail) use ($lead) {
            return $mail->hasTo($lead->email);
        });

        // Test cancel
        $this->actingAs($salesRep)->post(route('followups.cancel', $followup), [
            'reason' => 'Client cancelled proposal',
        ])->assertSessionHasNoErrors();

        Mail::assertSent(LeadMeetingCancelledMail::class, function ($mail) use ($lead) {
            return $mail->hasTo($lead->email);
        });

        $this->assertDatabaseHas('lead_followups', [
            'id' => $followup->id,
            'status' => 'cancelled',
        ]);
    }
}
