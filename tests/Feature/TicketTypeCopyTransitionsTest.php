<?php

/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 */

namespace Tests\Feature;

use Modules\Ticketsystem\Actions\CopyTicketTypeTransitionsAction;
use Modules\Ticketsystem\Entities\TicketType;
use Tests\TestCase;

class TicketTypeCopyTransitionsTest extends TestCase
{
    public function test_copy_action_replaces_target_workflow_with_source(): void
    {
        $source = TicketType::create(['name' => 'Copy Source '.uniqid('', true)]);
        $target = TicketType::create(['name' => 'Copy Target '.uniqid('', true)]);

        $this->assertGreaterThan(0, $source->ticketTypeTransitions()->count());

        $source->ticketTypeTransitions()->first()->forceDelete();

        $this->assertNotSame(
            $source->ticketTypeTransitions()->count(),
            $target->ticketTypeTransitions()->count()
        );

        app(CopyTicketTypeTransitionsAction::class)->execute($source->id, [$target->id]);

        $source->refresh()->load('ticketTypeTransitions');
        $target->refresh()->load('ticketTypeTransitions');

        $this->assertSame(
            $source->ticketTypeTransitions->count(),
            $target->ticketTypeTransitions->count()
        );

        $tupleKey = fn ($type) => $type->ticketTypeTransitions
            ->map(fn ($t) => "{$t->from_state_id}|{$t->to_state_id}|{$t->name}")
            ->sort()
            ->values()
            ->all();

        $this->assertSame($tupleKey($source), $tupleKey($target));
    }
}
