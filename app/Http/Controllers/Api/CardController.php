<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{
    public function getMemberCard(Request $request)
    {
        try {
            $request->validate([
                'member_id' => 'required|integer|exists:users,id',
            ]);

            $memberID = $request->input('member_id');
            $member = User::where('id', $memberID)
                ->where('user_type', UserType::MEMBER->value)->first();
            if (!$member) throw_msg('Member not found', 404);

            $perPage = $request->get('per_page', 15);
            $paginated = Card::where('user_id', $memberID)
                ->paginate($perPage);
            $paginated->map(function ($item) use ($member) {
                $item->is_default =  $item->card_number === $member->default_card;
            });
            $result = [
                'item' => [],
                'items' => $paginated->isEmpty() ? [] : CardResource::collection($paginated),
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
                'links' => [
                    'first' => $paginated->url(1),
                    'last' => $paginated->url($paginated->lastPage()),
                    'prev' => $paginated->previousPageUrl(),
                    'next' => $paginated->nextPageUrl(),
                ]
            ];
            return $this->sendResponse($result, 'Card retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
