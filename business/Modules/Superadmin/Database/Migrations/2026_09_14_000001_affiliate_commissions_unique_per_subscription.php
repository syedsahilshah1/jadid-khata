P<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Affiliate commission can now be recurring (every paid subscription of a
     * referred business earns) or non-recurring (only the first one does),
     * chosen in Superadmin > Affiliate Settings. Recurring is the default.
     *
     * This is a new migration, rather than an edit to the earlier affiliate
     * ones, because those have already run on existing installs and editing
     * them would change nothing there.
     *
     * The setting itself needs no row: AffiliateUtil falls back to recurring
     * when affiliate_commission_recurring is absent, and the row is created the
     * first time the superadmin saves the settings form.
     *
     * Stored terms & conditions are deliberately not touched here: existing
     * installs keep whatever terms they have. Only the starter terms seeded on
     * a fresh install describe the commission type.
     *
     * @return void
     */
    public function up()
    {
        $this->uniquePerSubscription();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->uniquePerBusiness();

        //Written by the settings form, not by up(), but it belongs to this feature.
        DB::table('system')->where('key', 'affiliate_commission_recurring')->delete();
    }

    /**
     * One commission per subscription, instead of one per business.
     *
     * A recurring business earns once per subscription, so referred_business_id
     * can no longer be unique. subscription_id becomes unique instead: the same
     * subscription can be approved, unapproved and approved again, which fires
     * the commission hook twice, and must still pay only once. The code checks
     * this too, but the index is what makes it certain.
     *
     * @return void
     */
    protected function uniquePerSubscription()
    {
        /*
         * The plain index goes on first, in its own statement. MySQL backs the
         * referred_business_id foreign key with the unique index, and refuses to
         * drop it ("needed in a foreign key constraint") until another index on
         * that column exists.
         */
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->index('referred_business_id');
        });

        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropUnique(['referred_business_id']);
            $table->unique('subscription_id');
        });
    }

    /**
     * Back to one commission per business.
     *
     * Fails if any business already holds more than one commission. That is
     * the correct outcome: those rows cannot fit the old rule, and silently
     * deleting earned commission would be worse.
     *
     * @return void
     */
    protected function uniquePerBusiness()
    {
        //Unique first, so the foreign key always has an index to lean on.
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropUnique(['subscription_id']);
            $table->unique('referred_business_id');
        });

        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropIndex(['referred_business_id']);
        });
    }
};
