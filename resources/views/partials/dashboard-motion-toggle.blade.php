<div class="dashboard-motion-control">
    <button type="button" class="dashboard-motion-toggle" data-dashboard-motion-toggle
        data-pause-label="{{ __('messages.pause_dashboard_motion') }}"
        data-resume-label="{{ __('messages.resume_dashboard_motion') }}"
        aria-label="{{ __('messages.pause_dashboard_motion') }}" hidden>
        <svg class="motion-pause-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6v12M15 6v12" /></svg>
        <svg class="motion-play-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 10 7-10 7z" /></svg>
        <span data-motion-label>{{ __('messages.pause_dashboard_motion') }}</span>
    </button>
    <small data-motion-note hidden>{{ __('messages.reduced_dashboard_motion') }}</small>
</div>
