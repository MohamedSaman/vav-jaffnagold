<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\SmsTopup;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsSettingsAndBillingTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_default_sms_settings_and_initial_topup_are_configured(): void
    {
        SmsTopup::truncate();
        SmsLog::truncate();

        SmsTopup::create([
            'amount' => 1000.00,
            'notes' => 'Default initial balance top-up',
        ]);
        Setting::updateOrCreate(['key' => 'sms_rate'], ['value' => '0.85']);
        Setting::updateOrCreate(['key' => 'sms_low_balance_threshold'], ['value' => '50.00']);
        Setting::updateOrCreate(['key' => 'sms_topup_contact'], ['value' => '0759037101']);

        $this->assertEquals(0.85, SmsService::getPerSmsRate());
        $this->assertEquals('0759037101', SmsService::getTopupContact());
        $this->assertEquals(1000.00, SmsService::getTotalTopupAmount());
        $this->assertEquals(1000.00, SmsService::getBalance());
        $this->assertFalse(SmsService::isBalanceLow());
        $this->assertFalse(SmsService::isBalanceFinished());
    }

    public function test_sending_sms_creates_sms_log_with_correct_charge_and_reduces_balance(): void
    {
        SmsTopup::truncate();
        SmsLog::truncate();

        SmsTopup::create(['amount' => 1000.00]);
        Setting::updateOrCreate(['key' => 'sms_rate'], ['value' => '0.85']);

        config(['sms.enabled' => false]);

        $result = SmsService::sendSms('0771234567', 'Test SMS Message from Jaffna Gold');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('sms_logs', [
            'recipient' => '+94771234567',
            'cost_per_sms' => 0.85,
            'total_cost' => 0.85,
            'sms_count' => 1,
            'is_double_charge' => false,
            'status' => 'mocked',
        ]);

        $this->assertEquals(1, SmsService::getTotalSmsSentCount());
        $this->assertEquals(0.85, SmsService::getTotalSmsCost());
        $this->assertEquals(999.15, SmsService::getBalance());
    }

    public function test_force_double_charge_applies_double_rate(): void
    {
        SmsTopup::truncate();
        SmsLog::truncate();

        SmsTopup::create(['amount' => 1000.00]);
        Setting::updateOrCreate(['key' => 'sms_rate'], ['value' => '0.85']);

        config(['sms.enabled' => false]);

        $result = SmsService::sendSms('0771234567', 'Test SMS with double charge override', true);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('sms_logs', [
            'recipient' => '+94771234567',
            'cost_per_sms' => 1.70,
            'total_cost' => 1.70,
            'sms_count' => 1,
            'is_double_charge' => true,
        ]);

        $this->assertEquals(1.70, SmsService::getTotalSmsCost());
        $this->assertEquals(998.30, SmsService::getBalance());
    }

    public function test_balance_is_low_when_under_threshold(): void
    {
        SmsTopup::truncate();
        SmsLog::truncate();

        // Add only 30 Rs topup with threshold 50
        SmsTopup::create(['amount' => 30.00]);
        Setting::updateOrCreate(['key' => 'sms_low_balance_threshold'], ['value' => '50.00']);

        $this->assertTrue(SmsService::isBalanceLow());
        $this->assertFalse(SmsService::isBalanceFinished());
    }

    public function test_admin_settings_page_displays_locked_provider_policy_section(): void
    {
        SmsTopup::truncate();
        SmsLog::truncate();

        SmsTopup::create(['amount' => 1000.00]);
        Setting::updateOrCreate(['key' => 'sms_rate'], ['value' => '0.85']);
        Setting::updateOrCreate(['key' => 'sms_topup_contact'], ['value' => '0759037101']);

        $response = $this->actingAs($this->admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('SMS Service Provider');
        $response->assertSee('Locked by Provider');
        $response->assertSee('0.85');
        $response->assertSee('0759037101');
        $response->assertSee('Request Top-Up via SMS');
    }

    public function test_user_cannot_modify_sms_settings_or_perform_self_topup(): void
    {
        SmsTopup::truncate();
        SmsLog::truncate();

        SmsTopup::create(['amount' => 1000.00]);
        Setting::updateOrCreate(['key' => 'sms_rate'], ['value' => '0.85']);

        $this->actingAs($this->admin);

        $component = new Settings;
        $component->loadSmsSettings();

        $this->assertEquals(0.85, $component->sms_rate);
        $this->assertEquals('0759037101', $component->sms_topup_contact);

        // Attempt to change rate
        $component->sms_rate = 0.99;
        $component->saveSmsSettings();

        // Database rate must remain intact at 0.85
        $this->assertEquals('0.85', Setting::where('key', 'sms_rate')->value('value'));

        // Attempt user self-topup
        $initialTopupCount = SmsTopup::count();
        $component->saveTopup();

        // Topup count must not change
        $this->assertEquals($initialTopupCount, SmsTopup::count());
    }

    public function test_service_provider_can_credit_balance_via_artisan_command(): void
    {
        SmsTopup::truncate();
        SmsLog::truncate();

        SmsTopup::create(['amount' => 1000.00]);

        $exitCode = \Illuminate\Support\Facades\Artisan::call('sms:topup', [
            'amount' => 500,
            '--notes' => 'Top-up credited by 0759037101 bank deposit',
            '--provider' => '0759037101',
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('sms_topups', [
            'amount' => 500.00,
            'notes' => 'Top-up credited by 0759037101 bank deposit',
            'user_id' => null,
        ]);

        $this->assertEquals(1500.00, SmsService::getTotalTopupAmount());
        $this->assertEquals(1500.00, SmsService::getBalance());
    }

    public function test_request_sms_topup_dispatches_free_sms_to_provider_without_whatsapp(): void
    {
        SmsTopup::truncate();
        SmsLog::truncate();

        SmsTopup::create(['amount' => 1000.00]);
        config(['sms.enabled' => false]);

        $this->actingAs($this->admin);

        $component = new Settings;
        $component->requestSmsTopup();

        // Must create an SMS log to provider contact with 0 total_cost
        $this->assertDatabaseHas('sms_logs', [
            'recipient' => '+94759037101',
            'total_cost' => 0.00,
            'status' => 'mocked',
        ]);

        // Balance should remain unchanged (free request)
        $this->assertEquals(1000.00, SmsService::getBalance());

        // View must not contain wa.me for SMS topup
        $response = $this->actingAs($this->admin)->get(route('admin.settings'));
        $response->assertDontSee('https://wa.me/94759037101');
    }
}
