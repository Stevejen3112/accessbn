<div class="notification-list-view">
    <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between">
        <h3 class="text-sm font-bold text-white">{{ __('Notifications') }}</h3>
        <span class="notification-count text-xs text-text-secondary">{{ $unread_notification_count }}</span>
    </div>
    <div class="notification-items max-h-80 overflow-y-auto"></div>
    <p class="notification-error hidden p-3 text-xs text-accent-error" role="alert">{{ __('Unable to load notifications. Please try again.') }}</p>
    <button type="button" class="notification-more w-full p-3 text-sm text-accent-primary">{{ __('Load more') }}</button>
</div>
<div class="notification-detail-view hidden">
    <button type="button" class="notification-back-btn p-3 text-sm text-accent-primary">{{ __('Back') }}</button>
    <div class="p-4 max-h-80 overflow-y-auto">
        <h3 class="notification-title text-sm font-bold text-white"></h3>
        <p class="notification-date text-xs text-text-secondary my-3"></p>
        <p class="notification-body text-sm text-text-secondary whitespace-pre-line"></p>
    </div>
</div>
