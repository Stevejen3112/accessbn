{{-- Signal Modal --}}
<div id="signalModal" class="hidden fixed inset-0 bg-secondary/90 backdrop-blur-sm z-[100] flex items-center justify-center p-4 transition-all duration-300">
    <div id="signalModal-content" class="bg-secondary-dark border border-white/10 w-full max-w-2xl rounded-2xl shadow-2xl scale-95 opacity-0 transition-all duration-300 relative overflow-hidden">
        <div class="p-6 relative z-10">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-black text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                    </div>
                    {{ __('Copy Trading Signals') }}
                </h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="copyAllSignals()" class="bg-accent-primary/10 text-accent-primary hover:bg-accent-primary hover:text-white px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2 border border-accent-primary/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                        {{ __('Copy All Languages') }}
                    </button>
                    <button type="button" onclick="closeModal('signalModal')"
                        class="text-text-secondary hover:text-white transition-colors cursor-pointer modal-close">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="signal-languages-container" class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto custom-scrollbar pr-2">
                {{-- Dynamically populated via JS --}}
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a id="modal-index-link" href="{{ route('admin.copy-trading.index') }}" class="hidden px-8 py-3 rounded-xl bg-accent-primary text-white font-bold hover:bg-accent-primary/90 transition-all cursor-pointer">
                    {{ __('Go to Copy Trades') }}
                </a>
                <button type="button" onclick="closeModal('signalModal')"
                    class="px-8 py-3 rounded-xl bg-white/5 text-white font-bold hover:bg-white/10 transition-all cursor-pointer">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentSignals = {};

    window.closeModal = function(id) {
        const $modal = $('#' + id);
        const $content = $('#' + id + '-content');
        $content.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $modal.addClass('hidden');
        }, 300);
    };

    window.copySignal = function(lang) {
        const textarea = document.getElementById('signal-' + lang);
        textarea.select();
        document.execCommand('copy');
        toastNotification(lang.toUpperCase() + ' {{ __('signal message copied to clipboard!') }}', 'success');
    };

    window.copyAllSignals = function() {
        let allText = '';
        for (const [lang, text] of Object.entries(currentSignals)) {
            allText += `--- ${lang.toUpperCase()} ---\n${text}\n\n`;
        }
        
        const tempTextArea = document.createElement('textarea');
        tempTextArea.value = allText;
        document.body.appendChild(tempTextArea);
        tempTextArea.select();
        document.execCommand('copy');
        document.body.removeChild(tempTextArea);
        
        toastNotification('{{ __('All signal messages copied to clipboard!') }}', 'success');
    };

    window.showSignalModal = function(messages, showLink = false) {
        currentSignals = messages;
        const container = document.getElementById('signal-languages-container');
        container.innerHTML = '';

        for (const [code, text] of Object.entries(messages)) {
            const html = `
                <div class="space-y-3 bg-white/5 p-4 rounded-xl border border-white/10">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-white uppercase tracking-widest">${code}</span>
                        <button type="button" onclick="copySignal('${code}')" class="text-[10px] text-accent-primary hover:text-white transition-colors flex items-center gap-1 font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            {{ __('Copy') }} ${code.toUpperCase()}
                        </button>
                    </div>
                    <textarea id="signal-${code}" readonly class="w-full bg-transparent border-none p-0 text-base text-text-secondary h-32 focus:outline-none custom-scrollbar leading-relaxed resize-none">${text}</textarea>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
        
        if (showLink) {
            $('#modal-index-link').removeClass('hidden');
        } else {
            $('#modal-index-link').addClass('hidden');
        }

        const $modal = $('#signalModal');
        const $content = $('#signalModal-content');
        $modal.removeClass('hidden');
        setTimeout(() => {
            $content.removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    };
</script>
