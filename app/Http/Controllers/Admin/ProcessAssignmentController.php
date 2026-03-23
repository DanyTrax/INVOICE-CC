<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\User;
use App\Services\ProcessAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcessAssignmentController extends Controller
{
    public function show(Process $process, ProcessAccessService $access): JsonResponse
    {
        $user = auth()->user();
        if (! $user->hasRole('super_admin') && ! $user->hasRole('admin')) {
            abort(403);
        }

        $process->load(['assignedUsers', 'client']);

        $assignable = $access->assignableUsers();
        $assignments = $process->assignedUsers->mapWithKeys(function (User $u) use ($access) {
            $flags = $access->permissionFlagsForAssignableUser($u);

            return [
                $u->id => [
                    'can_feed_timeline' => (bool) $u->pivot->can_feed_timeline,
                    'can_manage_documents' => (bool) $u->pivot->can_manage_documents,
                    'can_offer_timeline' => $flags['can_offer_timeline'],
                    'can_offer_documents' => $flags['can_offer_documents'],
                ],
            ];
        });

        $usersPayload = $assignable->map(function (User $u) use ($access) {
            $flags = $access->permissionFlagsForAssignableUser($u);

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'can_offer_timeline' => $flags['can_offer_timeline'],
                'can_offer_documents' => $flags['can_offer_documents'],
            ];
        })->values();

        return response()->json([
            'process_id' => $process->id,
            'product_reference' => $process->product_reference,
            'client_name' => $process->client->name ?? '—',
            'user_ids' => $process->assignedUsers->pluck('id')->all(),
            'assignments' => $assignments,
            'users' => $usersPayload,
        ]);
    }

    public function update(Request $request, Process $process, ProcessAccessService $access): JsonResponse
    {
        $user = auth()->user();
        if (! $user->hasRole('super_admin') && ! $user->hasRole('admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'assignments' => 'array',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.can_feed_timeline' => 'boolean',
            'assignments.*.can_manage_documents' => 'boolean',
        ]);

        $assignableIds = $access->assignableUsers()->pluck('id')->all();

        $sync = [];
        foreach ($validated['assignments'] ?? [] as $row) {
            $uid = (int) $row['user_id'];
            if (! in_array($uid, $assignableIds, true)) {
                continue;
            }
            $target = User::with('roles')->find($uid);
            if (! $target) {
                continue;
            }
            $flags = $access->permissionFlagsForAssignableUser($target);

            $canFeed = ! empty($row['can_feed_timeline']) && $flags['can_offer_timeline'];
            $canDocs = ! empty($row['can_manage_documents']) && $flags['can_offer_documents'];

            $sync[$uid] = [
                'can_feed_timeline' => $canFeed,
                'can_manage_documents' => $canDocs,
            ];
        }

        $process->assignedUsers()->sync($sync);

        return response()->json([
            'success' => true,
            'message' => 'Asignación actualizada.',
        ]);
    }
}
