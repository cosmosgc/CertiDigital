<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-cyan-300">AI + MCP</p>
            <h2 class="text-2xl font-semibold text-white">Assistente CertiDigital</h2>
            <p class="text-sm text-slate-300">
                Converse com um LLM externo usando suas ferramentas MCP.
                Modelo: <span class="font-mono">{{ $model }}</span>
                @if (!$hasKey)
                    <span class="ml-2 rounded bg-amber-500/20 px-2 py-1 text-amber-200">Sem AI_API_KEY — configure no .env</span>
                @endif
                @if ($isAdmin)
                    <span class="ml-2 rounded bg-emerald-500/20 px-2 py-1 text-emerald-200">admin: 7 tools</span>
                @else
                    <span class="ml-2 rounded bg-sky-500/20 px-2 py-1 text-sky-200">leitura: 3 tools</span>
                @endif
            </p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6">
        <div class="rounded-3xl border border-white/10 bg-slate-950 p-4 shadow-xl">
            <div class="mb-3 flex flex-wrap gap-2 text-xs">
                @foreach (['Quanto vou ganhar em outubro?', 'Liste minhas turmas de Informática', 'Quais presenças da turma 10?', 'Verifique o certificado CERT-123'] as $q)
                    <button data-quick="{{ $q }}" class="quick rounded-full border border-cyan-300/30 bg-cyan-500/10 px-3 py-1.5 text-cyan-100 hover:bg-cyan-500/20">{{ $q }}</button>
                @endforeach
            </div>

            <div id="msgs" class="flex h-[46vh] flex-col gap-3 overflow-y-auto rounded-2xl bg-slate-900/60 p-4 text-sm"></div>

            <div id="tools" class="mt-2 flex flex-wrap gap-1.5 text-[11px] text-slate-400">
                @foreach ($tools as $t)
                    <span class="rounded-full bg-white/5 px-2 py-1 font-mono" title="{{ $t['description'] ?? '' }}">{{ $t['name'] }}</span>
                @endforeach
            </div>

            <form id="form" class="mt-3 flex gap-2">
                <input id="input" autocomplete="off" placeholder="Pergunte… ex: quanto vou ganhar em outubro?"
                    class="flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder:text-slate-500 focus:border-cyan-400/50 focus:outline-none" />
                <button class="rounded-2xl bg-cyan-500 px-5 py-3 font-semibold text-slate-950 hover:bg-cyan-400 disabled:opacity-50">Enviar</button>
            </form>
            <p class="mt-2 text-xs text-slate-500">As chamadas de ferramentas aparecem como badges. O LLM só vê dados reais via MCP.</p>
        </div>
    </div>

    <script>
        const msgs = document.getElementById('msgs');
        const form = document.getElementById('form');
        const input = document.getElementById('input');
        const history = [];
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        function bubble(role, text, tools = []) {
            const div = document.createElement('div');
            const mine = role === 'user';
            div.className = 'max-w-[85%] rounded-2xl px-4 py-3 whitespace-pre-wrap ' + (mine
                ? 'self-end bg-cyan-500 text-slate-950'
                : 'self-start bg-white/10 text-slate-100');
            div.textContent = text;
            msgs.appendChild(div);
            if (tools.length) {
                const w = document.createElement('div');
                w.className = 'self-start flex flex-wrap gap-1';
                tools.forEach(t => {
                    const s = document.createElement('span');
                    s.className = 'rounded-full bg-emerald-500/20 px-2 py-0.5 font-mono text-[11px] text-emerald-200';
                    s.textContent = '⚙ ' + t.tool;
                    w.appendChild(s);
                });
                msgs.appendChild(w);
            }
            msgs.scrollTop = msgs.scrollHeight;
        }

        async function send(text) {
            const q = (text ?? input.value).trim();
            if (!q) return;
            input.value = '';
            bubble('user', q);
            history.push({ role: 'user', content: q });
            bubble('assistant', '…');
            const typing = msgs.lastChild;
            try {
                const res = await fetch("{{ route('ai.chat') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ messages: history.slice(-20) }),
                });
                const data = await res.json();
                typing.remove();
                if (!res.ok) {
                    bubble('assistant', 'Erro: ' + (data.message || res.status) + (data.detail ? '\n' + data.detail.slice(0, 500) : ''));
                    return;
                }
                bubble('assistant', data.reply || '(sem resposta)', data.used_tools || []);
                history.push({ role: 'assistant', content: data.reply || '' });
            } catch (e) {
                typing.remove();
                bubble('assistant', 'Falha de rede: ' + e.message);
            }
        }

        form.addEventListener('submit', e => { e.preventDefault(); send(); });
        document.querySelectorAll('.quick').forEach(b => b.addEventListener('click', () => send(b.dataset.quick)));
        bubble('assistant', 'Olá! Posso consultar cursos, turmas, presenças, certificados e ganhos via MCP. O que deseja?');
    </script>
</x-app-layout>
