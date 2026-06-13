@extends('layouts.profile')
@section('title', 'Lịch sử chat - SFood')
@section('profile_content')

<div class="space-y-6">

    {{-- Page header --}}
    <div class="card p-6 pb-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <h2 class="font-bold text-lg text-earth-800">💬 Lịch sử chat</h2>

            {{-- Search box --}}
            <div class="relative w-full sm:w-72">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-earth-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    id="chat-search-input"
                    placeholder="Tìm trong lịch sử chat..."
                    class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-cream-300 bg-cream-50 focus:outline-none focus:border-leaf-500 text-earth-800"
                >
                <span id="search-count" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-earth-400 hidden"></span>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 border-b border-cream-200 -mx-6 px-6">
            <a href="{{ route('profile.chat-history', ['tab' => 'ai']) }}"
               class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px
                      {{ $tab === 'ai' ? 'border-leaf-600 text-leaf-700' : 'border-transparent text-earth-500 hover:text-earth-700' }}">
                🤖 AI Chat
            </a>
            <a href="{{ route('profile.chat-history', ['tab' => 'cskh']) }}"
               class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px
                      {{ $tab === 'cskh' ? 'border-leaf-600 text-leaf-700' : 'border-transparent text-earth-500 hover:text-earth-700' }}">
                👨‍💻 Chăm sóc khách hàng
            </a>
        </div>
    </div>

    {{-- No history --}}
    @if (empty($conversations))
        <div class="card p-10 text-center">
            <div class="text-5xl mb-4">{{ $tab === 'ai' ? '🤖' : '👨‍💻' }}</div>
            <p class="text-earth-600 font-medium mb-1">Chưa có lịch sử chat nào</p>
            <p class="text-sm text-earth-400">
                {{ $tab === 'ai' ? 'Hãy thử hỏi AI Assistant để tìm sản phẩm nhé!' : 'Chat với nhân viên hỗ trợ để được tư vấn trực tiếp.' }}
            </p>
        </div>
    @else

    {{-- Conversation list --}}
    <div id="conversation-list" class="space-y-4">
        @foreach ($conversations as $convIndex => $messages)
            @php
                $firstMsg  = $messages[0];
                $lastMsg   = $messages[count($messages) - 1];
                $convDate  = $firstMsg->created_at;
                $msgCount  = count($messages);

                // Snippet: first user message plain text
                $snippet = '';
                foreach ($messages as $m) {
                    $role = $tab === 'ai' ? $m->role : $m->sender;
                    if ($role === 'user') {
                        $snippet = $tab === 'ai'
                            ? strip_tags($m->content)
                            : strip_tags($m->message);
                        break;
                    }
                }
                $snippet = \Illuminate\Support\Str::limit($snippet, 80);
            @endphp

            <div class="card overflow-hidden conversation-item" data-index="{{ $convIndex }}">

                {{-- Conversation header (collapsible trigger) --}}
                <button
                    type="button"
                    class="w-full flex items-start justify-between gap-4 p-5 text-left hover:bg-cream-50 transition conv-toggle"
                    data-target="conv-body-{{ $convIndex }}"
                    aria-expanded="false"
                >
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <span class="text-2xl mt-0.5 shrink-0">{{ $tab === 'ai' ? '🤖' : '👨‍💻' }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-semibold text-leaf-700 bg-leaf-50 px-2 py-0.5 rounded-full border border-leaf-200">
                                    {{ $convDate->format('d/m/Y') }}
                                </span>
                                <span class="text-xs text-earth-400">{{ $convDate->format('H:i') }} — {{ $lastMsg->created_at->format('H:i') }}</span>
                                <span class="text-xs text-earth-400">{{ $msgCount }} tin nhắn</span>
                            </div>
                            <p class="text-sm text-earth-700 mt-1 truncate search-snippet">{{ $snippet ?: 'Cuộc trò chuyện' }}</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-earth-400 shrink-0 mt-1 transition-transform duration-200 conv-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Conversation messages body (hidden by default) --}}
                <div id="conv-body-{{ $convIndex }}" class="hidden border-t border-cream-100">
                    <div class="p-4 bg-cream-50 dark:bg-gray-900 flex flex-col gap-3 max-h-[500px] overflow-y-auto">

                        @foreach ($messages as $msg)
                            @php
                                $role    = $tab === 'ai' ? $msg->role : $msg->sender;
                                $isUser  = $role === 'user';
                                $content = $tab === 'ai'
                                    ? ($msg->html_content ?: e($msg->content))
                                    : e($msg->message);
                                $time    = $msg->created_at->format('H:i');
                            @endphp

                            <div class="flex gap-2 {{ $isUser ? 'justify-end' : 'justify-start' }} message-row">
                                <div class="max-w-[80%] flex flex-col {{ $isUser ? 'items-end' : 'items-start' }}">
                                    <div class="text-[10px] text-earth-400 mb-1 {{ $isUser ? 'text-right' : 'text-left' }}">
                                        {{ $isUser ? 'Bạn' : ($tab === 'ai' ? 'AI' : 'CSKH') }} · {{ $time }}
                                    </div>
                                    <div class="
                                        text-sm p-3 rounded-2xl shadow-sm
                                        {{ $isUser
                                            ? 'bg-leaf-600 text-white rounded-tr-sm'
                                            : 'bg-white dark:bg-gray-800 text-earth-800 dark:text-gray-200 border border-cream-100 dark:border-gray-700 rounded-tl-sm markdown-body' }}
                                        message-content
                                    ">
                                        {!! $content !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                    {{-- Footer actions --}}
                    <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-cream-100 flex justify-end gap-2">
                        <form method="POST" action="{{ route('profile.chat-history.delete') }}"
                              onsubmit="return confirm('Xóa cuộc trò chuyện này?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <input type="hidden" name="start_at" value="{{ $firstMsg->created_at->toISOString() }}">
                            <input type="hidden" name="end_at" value="{{ $lastMsg->created_at->toISOString() }}">
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                                🗑️ Xóa cuộc trò chuyện
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    {{-- Delete all button --}}
    <div class="flex justify-end">
        <form method="POST" action="{{ route('profile.chat-history.delete-all') }}"
              onsubmit="return confirm('Xóa toàn bộ lịch sử {{ $tab === 'ai' ? 'AI Chat' : 'CSKH' }}?')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="tab" value="{{ $tab }}">
            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium px-4 py-2 rounded-xl border border-red-200 hover:bg-red-50 transition">
                🗑️ Xóa tất cả lịch sử {{ $tab === 'ai' ? 'AI Chat' : 'CSKH' }}
            </button>
        </form>
    </div>

    @endif {{-- end conversations --}}

</div>

{{-- No results found (search) --}}
<div id="search-empty" class="hidden card p-8 text-center mt-4">
    <p class="text-earth-500 text-sm">Không tìm thấy tin nhắn nào khớp với từ khóa.</p>
</div>

@push('scripts')
<script>
(function () {
    // ---- Collapsible conversations ----
    document.querySelectorAll('.conv-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;
            const body     = document.getElementById(targetId);
            const chevron  = btn.querySelector('.conv-chevron');
            const isOpen   = !body.classList.contains('hidden');

            if (isOpen) {
                body.classList.add('hidden');
                chevron.style.transform = '';
                btn.setAttribute('aria-expanded', 'false');
            } else {
                body.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
                btn.setAttribute('aria-expanded', 'true');
                // Scroll message body to bottom
                setTimeout(() => { body.querySelector('.flex.flex-col').scrollTop = 9999; }, 50);
            }
        });
    });

    // ---- Client-side search ----
    const searchInput  = document.getElementById('chat-search-input');
    const searchCount  = document.getElementById('search-count');
    const convList     = document.getElementById('conversation-list');
    const emptyNotice  = document.getElementById('search-empty');

    if (!searchInput || !convList) return;

    let debounce;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(doSearch, 200);
    });

    function doSearch() {
        const q = searchInput.value.trim().toLowerCase();

        if (!q) {
            // Reset all
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('hidden');
                item.querySelectorAll('.message-row').forEach(row => row.classList.remove('hidden'));
                clearHighlights(item);
            });
            searchCount.classList.add('hidden');
            if (emptyNotice) emptyNotice.classList.add('hidden');
            return;
        }

        let totalMatches = 0;
        let visibleConvs = 0;

        document.querySelectorAll('.conversation-item').forEach(item => {
            let convHasMatch = false;

            // Search in snippet (header)
            const snippet = item.querySelector('.search-snippet');
            if (snippet && snippet.textContent.toLowerCase().includes(q)) {
                convHasMatch = true;
            }

            // Search in message rows
            item.querySelectorAll('.message-row').forEach(row => {
                const msgEl   = row.querySelector('.message-content');
                const msgText = msgEl ? (msgEl.textContent || msgEl.innerText || '').toLowerCase() : '';
                const match   = msgText.includes(q);

                row.classList.toggle('hidden', !match);
                if (match) {
                    convHasMatch = true;
                    totalMatches++;
                    highlightText(msgEl, q);
                } else {
                    clearHighlights(row);
                }
            });

            item.classList.toggle('hidden', !convHasMatch);

            if (convHasMatch) {
                visibleConvs++;
                // Auto-open conversation if it has matches
                const body    = item.querySelector('[id^="conv-body-"]');
                const chevron = item.querySelector('.conv-chevron');
                if (body && body.classList.contains('hidden')) {
                    body.classList.remove('hidden');
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                }
            }
        });

        // Update count badge
        if (totalMatches > 0) {
            searchCount.textContent = totalMatches + ' kết quả';
            searchCount.classList.remove('hidden');
        } else {
            searchCount.classList.add('hidden');
        }

        if (emptyNotice) {
            emptyNotice.classList.toggle('hidden', visibleConvs > 0);
        }
    }

    function highlightText(el, q) {
        if (!el) return;
        clearHighlights(el);
        const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
        const nodes  = [];
        let node;
        while ((node = walker.nextNode())) nodes.push(node);

        nodes.forEach(textNode => {
            const idx = textNode.textContent.toLowerCase().indexOf(q);
            if (idx === -1) return;
            const before  = document.createTextNode(textNode.textContent.slice(0, idx));
            const mark    = document.createElement('mark');
            mark.className = 'bg-yellow-200 rounded px-0.5 highlight-mark';
            mark.textContent = textNode.textContent.slice(idx, idx + q.length);
            const after   = document.createTextNode(textNode.textContent.slice(idx + q.length));
            const parent  = textNode.parentNode;
            parent.replaceChild(after, textNode);
            parent.insertBefore(mark, after);
            parent.insertBefore(before, mark);
        });
    }

    function clearHighlights(el) {
        el.querySelectorAll('.highlight-mark').forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize();
        });
    }
})();
</script>
@endpush

@endsection
