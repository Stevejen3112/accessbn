<script>
    $(function() {
        const listUrl = @json(route('user.notifications.index'));
        const detailUrl = @json(route('user.notifications.show', ['id' => 0]));
        let unreadCount = {{ (int) $unread_notification_count }};
        const readIds = new Set();

        function loadPage(dropdown) {
            if (dropdown.data('loading') || dropdown.data('exhausted')) return;
            dropdown.data('loading', true);
            const more = dropdown.find('.notification-more').prop('disabled', true);
            dropdown.find('.notification-error').addClass('hidden');
            $.getJSON(listUrl, {cursor: dropdown.data('cursor') || ''})
                .done(function(response) {
                    response.items.forEach(function(item) {
                        const button = $('<button>', {type: 'button', class: 'notification-item-trigger block w-full text-left p-3 border-b border-white/5 hover:bg-white/5'}).attr('data-id', item.id);
                        $('<p>', {class: 'text-sm font-bold text-white'}).text(item.title).appendTo(button);
                        $('<p>', {class: 'text-xs text-text-secondary'}).text(item.created_at).appendTo(button);
                        dropdown.find('.notification-items').append(button);
                    });
                    dropdown.data('loaded', true).data('cursor', response.next_cursor).data('exhausted', !response.next_cursor);
                    more.toggleClass('hidden', !response.next_cursor);
                })
                .fail(function() { dropdown.find('.notification-error').removeClass('hidden'); })
                .always(function() { dropdown.data('loading', false); more.prop('disabled', false); });
        }

        $('.notification-btn').on('click', function(event) {
            event.stopPropagation();
            const dropdown = $(this).siblings('.notification-dropdown');
            const wasOpen = !dropdown.hasClass('hidden');
            $('.notification-dropdown').addClass('hidden');
            dropdown.find('.notification-detail-view').addClass('hidden');
            dropdown.find('.notification-list-view').removeClass('hidden');
            if (!wasOpen) {
                dropdown.removeClass('hidden');
                if (!dropdown.data('loaded')) loadPage(dropdown);
            }
        });
        $('.notification-dropdown').on('click', function(event) { event.stopPropagation(); });
        $('.notification-more').on('click', function() { loadPage($(this).closest('.notification-dropdown')); });
        $('.notification-dropdown').on('click', '.notification-item-trigger', function() {
            const button = $(this);
            const id = Number(button.attr('data-id'));
            const dropdown = button.closest('.notification-dropdown');
            if (dropdown.data('detailLoading')) return;
            dropdown.data('detailLoading', true);
            dropdown.find('.notification-error').addClass('hidden');
            $.getJSON(detailUrl.replace(/\/0$/, '/' + id))
                .done(function(item) {
                    dropdown.find('.notification-title').text(item.title);
                    dropdown.find('.notification-body').text(item.body);
                    dropdown.find('.notification-date').text(item.created_at);
                    dropdown.find('.notification-list-view').addClass('hidden');
                    dropdown.find('.notification-detail-view').removeClass('hidden');
                    if (!readIds.has(id)) {
                        $.post(@json(route('user.notification-mark-as-read')), {_token: @json(csrf_token()), notification_id: id})
                            .done(function(response) {
                                if (!response.success || readIds.has(id)) return;
                                readIds.add(id);
                                unreadCount = Math.max(0, unreadCount - 1);
                                $('.notification-count').text(unreadCount);
                                $('.notification-btn span.bg-red-500').text(unreadCount).toggleClass('hidden', unreadCount === 0);
                                $('.notification-item-trigger[data-id="' + id + '"] p.font-bold').removeClass('text-white').addClass('text-text-secondary');
                            })
                            .fail(function() { dropdown.find('.notification-body').append($('<p>', {role: 'alert'}).text(@json(__('Unable to mark as read. Please try again.')))); });
                    }
                })
                .fail(function() { dropdown.find('.notification-error').removeClass('hidden'); })
                .always(function() { dropdown.data('detailLoading', false); });
        });
        $('.notification-back-btn').on('click', function() {
            const dropdown = $(this).closest('.notification-dropdown');
            dropdown.find('.notification-detail-view').addClass('hidden');
            dropdown.find('.notification-list-view').removeClass('hidden');
        });
        $(document).on('click', function() { $('.notification-dropdown').addClass('hidden'); });
    });
</script>
