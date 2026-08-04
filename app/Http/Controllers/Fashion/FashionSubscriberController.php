<?php

namespace App\Http\Controllers\Fashion;

use App\Domain\Fashion\Models\FashionLaunchSubscriber;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class FashionSubscriberController extends Controller
{
    #[OA\Post(
        path: '/fashion/subscribe',
        summary: 'Register for the Demera Fashion launch notification',
        description: 'Requires at least one of email or whatsapp_number. Rate limited to 5 requests/minute per IP.',
        tags: ['Fashion'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', nullable: true, example: 'Dinda'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                    new OA\Property(property: 'whatsapp_number', type: 'string', nullable: true, example: '081234567890'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Subscribed successfully'),
            new OA\Response(response: 422, description: 'Validation error (missing contact method or duplicate)'),
        ],
    )]
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('fashion_launch_subscribers', 'email')],
            'whatsapp_number' => ['nullable', 'string', 'max:20', Rule::unique('fashion_launch_subscribers', 'whatsapp_number')],
        ]);

        if (empty($validated['email']) && empty($validated['whatsapp_number'])) {
            throw ValidationException::withMessages([
                'email' => 'Isi email atau nomor WhatsApp agar kami bisa menghubungi Anda.',
            ]);
        }

        $subscriber = FashionLaunchSubscriber::create([
            ...$validated,
            'source' => 'coming_soon_page',
            'subscribed_at' => now(),
        ]);

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['message' => 'Berhasil mendaftar.', 'data' => $subscriber], 201);
        }

        return back()->with('success', 'Terima kasih! Kami akan mengabari Anda saat Demera Fashion meluncur.');
    }
}
