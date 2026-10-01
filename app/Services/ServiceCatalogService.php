<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Collection;

class ServiceCatalogService
{
    /**
     * Retrieve all active categories with their active services, sorted.
     */
    public function getActiveCatalog(): Collection
    {
        return ServiceCategory::where('is_active', true)
            ->with(['activeServices'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Find an active service by its unique slug.
     */
    public function findBySlug(string $slug): ?Service
    {
        return Service::where('slug', $slug)
            ->where('is_active', true)
            ->with('category')
            ->first();
    }

    /**
     * Retrieve all services with their category and pending request counts.
     */
    public function getAllForAdmin(): Collection
    {
        return Service::with('category')
            ->withCount([
                'requests as pending_requests_count' => fn ($query) => $query->where('status', 'pending'),
                'requests as processing_requests_count' => fn ($query) => $query->where('status', 'processing'),
                'requests as completed_requests_count' => fn ($query) => $query->where('status', 'completed'),
                'requests as failed_requests_count' => fn ($query) => $query->where('status', 'failed'),
            ])
            ->orderBy('sort_order')
            ->get();
    }
}
