<?php

namespace App\Listeners;

use App\Events\MemberAdded;
use App\Jobs\SuggestFamilyLinksJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateFamilySuggestionsForNewMember implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MemberAdded $event): void
    {
        try {
            Log::info('MemberAdded event received', [
                'new_member' => $event->newMember->name,
                'added_by' => $event->addedBy->name,
                'relationship' => $event->relationship->relationshipType->display_name_fr ?? 'Unknown'
            ]);

            // Dispatch job to generate suggestions for the new member
            SuggestFamilyLinksJob::dispatch($event->newMember, $event->addedBy, $event->relationship)
                ->onQueue('suggestions')
                ->delay(now()->addSeconds(5)); // Small delay to ensure transaction is committed

            // Also generate suggestions for the person who added them
            SuggestFamilyLinksJob::dispatch($event->addedBy, $event->newMember, $event->relationship)
                ->onQueue('suggestions')
                ->delay(now()->addSeconds(10));

            Log::info('Family suggestion jobs dispatched', [
                'new_member_id' => $event->newMember->id,
                'added_by_id' => $event->addedBy->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error handling MemberAdded event', [
                'error' => $e->getMessage(),
                'new_member_id' => $event->newMember->id,
                'added_by_id' => $event->addedBy->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(MemberAdded $event, \Throwable $exception): void
    {
        Log::error('GenerateFamilySuggestionsForNewMember listener failed', [
            'new_member_id' => $event->newMember->id,
            'added_by_id' => $event->addedBy->id,
            'error' => $exception->getMessage()
        ]);
    }
}
