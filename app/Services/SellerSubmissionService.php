<?php

namespace App\Services;

use App\Enums\LiveSubmissionStatus;
use App\Enums\UserRole;
use App\Exceptions\DuplicateSubmissionException;
use App\Models\LiveSubmission;
use App\Models\Seller;
use App\Models\User;
use App\Notifications\NewLiveSubmissionNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SellerSubmissionService
{
    /**
     * Crée (ou réutilise) le vendeur puis enregistre sa demande de LIVE en statut PENDING.
     *
     * @param  array{profile_image?: ?UploadedFile, logo_image?: ?UploadedFile}  $files
     *
     * @throws DuplicateSubmissionException
     */
    public function create(array $data, array $files = []): LiveSubmission
    {
        $submission = DB::transaction(function () use ($data) {
            $seller = $this->resolveSeller($data['seller']);

            $this->ensureNoActiveDuplicate($seller, $data['live']['account_name']);

            $submission = LiveSubmission::create([
                'reference' => 'TMP-'.Str::random(20), // remplacée juste après par MKT-AAAA-NNNN
                'tracking_token' => Str::random(40),
                'seller_id' => $seller->id,
                'country_id' => $seller->country_id,
                'city_id' => $seller->city_id,
                'category_id' => $data['category_id'],
                'subcategory_id' => $data['subcategory_id'] ?? null,
                'schedule_id' => $data['schedule_id'] ?? null,
                'platform' => 'tiktok',
                'account_name' => $data['live']['account_name'],
                'live_url' => $data['live']['url'],
                'product_description' => $data['product_description'],
                'consents' => $data['consents'],
                'status' => LiveSubmissionStatus::Pending,
                'submitted_at' => now(),
                'ip_address' => request()?->ip(),
            ]);

            $submission->update(['reference' => sprintf('MKT-%d-%04d', now()->year, $submission->id)]);

            return $submission;
        });

        $this->storeImages($submission->seller, $files);
        $this->notifyModerators($submission->fresh(['seller', 'category', 'country', 'city']));

        return $submission;
    }

    /** Dédoublonnage par (téléphone, pays) : un même vendeur ne crée pas deux fiches. */
    private function resolveSeller(array $input): Seller
    {
        $existing = Seller::query()
            ->where('phone', $input['phone'])
            ->where('country_id', $input['country_id'])
            ->first();

        if ($existing) {
            $existing->update(collect($input)->only(['full_name', 'business_name', 'city_id', 'description', 'consents'])->all());

            return $existing;
        }

        return Seller::create($input);
    }

    /** Empêche la soumission répétée d'une demande active pour le même compte TikTok. */
    private function ensureNoActiveDuplicate(Seller $seller, string $accountName): void
    {
        $existing = $seller->submissions()
            ->whereNotIn('status', LiveSubmissionStatus::closed())
            ->whereRaw('LOWER(account_name) = ?', [Str::lower($accountName)])
            ->first();

        if ($existing) {
            throw new DuplicateSubmissionException($existing->reference);
        }
    }

    private function storeImages(Seller $seller, array $files): void
    {
        $directory = 'market/sellers/'.$seller->id;
        $store = fn (UploadedFile $file) => $file->storeAs($directory, Str::random(40).'.'.$file->extension(), 'public');

        $update = [];
        if (! empty($files['profile_image'])) {
            $update['profile_image'] = $store($files['profile_image']);
        }
        if (! empty($files['logo_image'])) {
            $update['logo_image'] = $store($files['logo_image']);
        }

        if ($update) {
            $seller->update($update);
        }
    }

    private function notifyModerators(LiveSubmission $submission): void
    {
        try {
            $moderators = User::query()
                ->where('is_active', true)
                ->whereIn('role', [UserRole::Admin->value, UserRole::Moderator->value])
                ->get();

            Notification::send($moderators, new NewLiveSubmissionNotification($submission));

            if ($extra = setting('market_notification_email')) {
                Notification::route('mail', $extra)->notify(new NewLiveSubmissionNotification($submission, mailOnly: true));
            }
        } catch (\Throwable $e) {
            // Un incident d'envoi d'e-mail ne doit jamais faire perdre la demande.
            Log::error('Notification de nouvelle demande Market non envoyée', ['submission' => $submission->reference, 'error' => $e->getMessage()]);
        }
    }
}
