<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Establishes the affiliate settings in the `system` table, where the rest
     * of this application's superadmin settings live.
     *
     * Only the terms are seeded. The other four settings need no row:
     * AffiliateUtil falls back to its own defaults when a key is
     * absent, and System::addProperty() creates the row the first time the
     * superadmin saves the settings form. Writing rows that only restate the
     * defaults would just be noise in a shared table.
     *
     * The terms are different: there is no sensible document for the code to
     * invent at read time, and an empty value makes the application form tell
     * the applicant that no terms have been configured. So a starter document
     * is written, meant to be replaced from Superadmin > Affiliate Settings.
     *
     * @return void
     */
    public function up()
    {
        //Key as a literal: a migration must keep working if the service changes.
        $key = 'affiliate_terms';

        //Never clobber terms already written through the settings screen.
        if (DB::table('system')->where('key', $key)->exists()) {
            return;
        }

        DB::table('system')->insert(['key' => $key, 'value' => $this->defaultTerms()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('system')->whereIn('key', [
            'affiliate_enabled',
            'affiliate_commission_percentage',
            'affiliate_cookie_days',
            'affiliate_min_payout',
            'affiliate_terms',
        ])->delete();
    }

    /**
     * A starter terms & conditions document.
     *
     * @return string
     */
    protected function defaultTerms()
    {
        return <<<'HTML'
<h2>Affiliate Program Terms &amp; Conditions</h2>

<p><em>This is a starter document. Please review it and replace it with your own terms from Superadmin &gt; Affiliate Settings.</em></p>

<h3>1. Eligibility</h3>
<ul>
    <li>Participation is subject to approval, and an application may be approved, rejected or suspended at any time.</li>
    <li>Affiliates must provide accurate information when applying.</li>
</ul>

<h3>2. Referral tracking</h3>
<ul>
    <li>Referrals are tracked with a cookie set when your referral link is used.</li>
    <li>A referral is credited only if the business registers before that cookie expires.</li>
    <li>Attribution cannot be guaranteed where cookies are blocked or cleared.</li>
</ul>

<h3>3. Qualifying purchase</h3>
<ul>
    <li>Commission is earned when a referred business buys a paid subscription and that subscription is approved.</li>
    <li>Free plans, trials and subscriptions with a payable amount of zero do not qualify.</li>
    <li>Commission is calculated on the amount actually paid, after any discount or coupon.</li>
</ul>

<h3>4. Recurring or first purchase</h3>
<ul>
    <li>Whether commission is earned on every qualifying purchase of a referred business, or on its first qualifying purchase only, is shown as the commission type on the application page.</li>
    <li>Each qualifying purchase earns commission at most once.</li>
</ul>

<h3>5. Commission rate</h3>
<ul>
    <li>The rate in effect at the time of the qualifying purchase is recorded against that commission.</li>
    <li>Later changes to the rate do not alter commission already earned.</li>
</ul>

<h3>6. Fraud and abuse</h3>
<ul>
    <li>Self-referrals, fake or duplicate accounts, spam and misleading promotion are not permitted.</li>
    <li>Commission arising from such activity may be withheld or reversed.</li>
</ul>

<h3>7. Payment</h3>
<ul>
    <li>Payouts are made according to the published schedule and may be subject to a minimum amount.</li>
    <li>Affiliates are responsible for providing accurate payment details and for any taxes due.</li>
</ul>

<h3>8. Changes and termination</h3>
<ul>
    <li>Commission rates, eligibility and program rules may change, and changes apply from the date they are made.</li>
    <li>An affiliate account may be suspended or closed for breach of these terms.</li>
</ul>

<h3>9. Acceptance</h3>
<p>By applying to the affiliate program you confirm that you have read, understood and agree to these terms.</p>
HTML;
    }
};
