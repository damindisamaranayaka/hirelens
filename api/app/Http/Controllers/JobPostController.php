<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobPostRequest;
use App\Http\Requests\UpdateJobPostRequest;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JobPostController extends Controller
{
    // Public: list open jobs with search, filters, pagination
    public function index(Request $request)
    {
        $jobs = JobPost::with('employer:id,name')
            ->where('status', 'open')
            ->when($request->query('q'), fn ($q, $term) =>
                $q->where(fn ($w) => $w->where('title', 'like', "%{$term}%")
                                       ->orWhere('company_name', 'like', "%{$term}%")))
            ->when($request->query('location'), fn ($q, $loc) =>
                $q->where('location', 'like', "%{$loc}%"))
            ->when($request->query('type'), fn ($q, $type) =>
                $q->where('type', $type))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return JobPostResource::collection($jobs);
    }

    // Public: single job
    public function show(JobPost $jobPost)
    {
        return new JobPostResource($jobPost->load('employer:id,name'));
    }

    // Employer: their own jobs (including closed)
    public function mine(Request $request)
    {
        $jobs = $request->user()->jobPosts()->latest()->paginate(10);

        return JobPostResource::collection($jobs);
    }

    // Employer only
    public function store(StoreJobPostRequest $request): JsonResponse
    {
        Gate::authorize('create', JobPost::class);

        // user_id is set from the token, never from the request body
        $jobPost = $request->user()->jobPosts()->create($request->validated());

        return (new JobPostResource($jobPost))->response()->setStatusCode(201);
    }

    // Owner only
    public function update(UpdateJobPostRequest $request, JobPost $jobPost)
    {
        Gate::authorize('update', $jobPost);

        $jobPost->update($request->validated());

        return new JobPostResource($jobPost);
    }

    // Owner only
    public function destroy(JobPost $jobPost): JsonResponse
    {
        Gate::authorize('delete', $jobPost);

        $jobPost->delete();

        return response()->json(['message' => 'Job deleted.']);
    }
}